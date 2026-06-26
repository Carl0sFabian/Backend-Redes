<?php

include_once __DIR__ . '/../services/GoogleDriveService.php';

class MaterialController {
    private $materialModel;

    public function __construct($db) {
        $this->materialModel = new Material($db);
    }

    public function getMateriales() {
        $stmt = $this->materialModel->get();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($result) {
            http_response_code(200);
            return json_encode($result);
        } else {
            http_response_code(404);
            return json_encode(array("message" => "No se encontraron inscripciones."));
        }
    }

    public function getMaterialById($id) {
        $this->materialModel->id = $id;
        $stmt = $this->materialModel->getById();
        $num = $stmt->rowCount();

        if($num > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            http_response_code(200);
            return json_encode($row);
        } else {
            http_response_code(404);
            return json_encode(array("message" => "Material no encontrado."));
        }
    }

    public function deleteMaterial($datos) {
        if(!empty($datos->id)) {
            $this->materialModel->id = $datos->id;

            // 1. Obtener la información del material antes de eliminarlo para sacar la URL de Drive
            $stmt = $this->materialModel->getById();
            $num = $stmt->rowCount();
            if ($num > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $urlArchivo = $row['url_archivo'];

                // 2. Extraer ID del archivo de Google Drive
                $fileId = GoogleDriveService::extractFileId($urlArchivo);

                // 3. Eliminar de Google Drive
                if ($fileId) {
                    try {
                        $driveService = new GoogleDriveService();
                        $driveService->deleteFile($fileId);
                    } catch (Exception $ex) {
                        error_log("No se pudo borrar de Drive: " . $ex->getMessage());
                    }
                }
            }

            // 4. Eliminar de la base de datos
            if($this->materialModel->delete()) {
                http_response_code(200);
                return json_encode(array("message" => "Material eliminado exitosamente."));
            } else {
                http_response_code(503);
                return json_encode(array("message" => "No se pudo eliminar el material."));
            }
        } else {
            http_response_code(400);
            return json_encode(array("message" => "Debe proporcionar el ID del material a eliminar."));
        }
    }

public function createMaterial($datosTexto, $datosArchivo) {
        $missing = [];
        if (empty($datosTexto['id_curso'])) $missing[] = 'id_curso';
        if (empty($datosTexto['titulo'])) $missing[] = 'titulo';
        if (empty($datosTexto['tipo'])) $missing[] = 'tipo';

        if (!isset($datosArchivo['archivo'])) {
            $missing[] = 'archivo';
        } else {
            $uploadError = $datosArchivo['archivo']['error'];
            if ($uploadError !== UPLOAD_ERR_OK) {
                // ... (Mantiene tus validaciones de error de subida intactas)
                http_response_code(400);
                return json_encode(array("message" => "Error de subida de archivo."));
            }
        }

        if (!empty($missing)) {
            http_response_code(400);
            return json_encode(array("message" => "Datos incompletos o inválidos. Faltan los campos: " . implode(', ', $missing)));
        }

        try {
            $driveService = new GoogleDriveService();

            $rutaTemporal = $datosArchivo['archivo']['tmp_name'];
            $nombreOriginal = $datosArchivo['archivo']['name'];
            $mimeType = $datosArchivo['archivo']['type'];
            $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));

            $fileProcessed = false;

            // Procesar traducción profesional si es TXT o PDF
            if ($extension === 'txt' || $extension === 'pdf') {
                try {
                    include_once __DIR__ . '/../services/TranslationService.php';
                    $translationService = new TranslationService();

                    // 1. Extraer texto para detectar el idioma origen
                    $textoExtraido = $translationService->extractText($rutaTemporal, $extension);
                    $detectedLang = $translationService->detectLanguage($textoExtraido);

                    // 2. Traducir el documento con DeepL solo si el idioma origen es Inglés (EN)
                    if ($detectedLang === 'EN') {
                        $tempTraducido = $translationService->translateDocument($rutaTemporal, $nombreOriginal, $extension);

                        if ($tempTraducido && file_exists($tempTraducido)) {
                            $tempFileName = basename($tempTraducido);
                            
                            // Determinar MimeType correcto de la traducción
                            $mimeTraducido = ($extension === 'pdf') ? 'application/pdf' : 'text/plain';

                            // Subir el archivo ya traducido por DeepL a Google Drive
                            $resultadoDrive = $driveService->uploadFile($tempTraducido, $tempFileName, $mimeTraducido);
                            
                            // Limpiar el archivo temporal del servidor
                            @unlink($tempTraducido);

                            if ($resultadoDrive && isset($resultadoDrive['id_drive'])) {
                                $this->materialModel->id_curso = $datosTexto['id_curso'];
                                $this->materialModel->titulo = $datosTexto['titulo']; // Mantenemos el título original para la traducción española
                                $this->materialModel->tipo = $datosTexto['tipo'];
                                $this->materialModel->url_archivo = $resultadoDrive['url_ver'];
                                $this->materialModel->fecha_publicacion = date('Y-m-d');

                                if ($this->materialModel->post()) {
                                    $fileProcessed = true;
                                }
                            }
                        }
                    }
                } catch (Exception $ex) {
                    error_log("Fallo controlado en la traducción con DeepL: " . $ex->getMessage());
                }
            }

            // Fallback: Si no se pudo traducir o no es compatible, se sube el original
            if (!$fileProcessed) {
                $resultadoDrive = $driveService->uploadFile($rutaTemporal, $nombreOriginal, $mimeType);

                if ($resultadoDrive && isset($resultadoDrive['id_drive'])) {
                    $this->materialModel->id_curso = $datosTexto['id_curso'];
                    $this->materialModel->titulo = $datosTexto['titulo'];
                    $this->materialModel->tipo = $datosTexto['tipo'];
                    $this->materialModel->url_archivo = $resultadoDrive['url_ver'];
                    $this->materialModel->fecha_publicacion = date('Y-m-d');

                    if ($this->materialModel->post()) {
                        $fileProcessed = true;
                    }
                }
            }

            if ($fileProcessed) {
                http_response_code(201);
                return json_encode(array(
                    "message" => "Material procesado y subido a Google Drive exitosamente.",
                    "records" => $this->materialModel
                ));
            } else {
                http_response_code(500);
                return json_encode(array("message" => "Error al procesar el archivo en el sistema."));
            }

        } catch (Exception $e) {
            http_response_code(500);
            return json_encode(array("message" => "Excepción detectada: " . $e->getMessage()));
        }
    }
}
?>