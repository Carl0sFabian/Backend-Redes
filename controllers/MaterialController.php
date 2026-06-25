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
        
        if(
            !empty($datosTexto['id_curso']) &&
            !empty($datosTexto['titulo']) &&
            !empty($datosTexto['tipo']) &&
            isset($datosArchivo['archivo']) && 
            $datosArchivo['archivo']['error'] === UPLOAD_ERR_OK
        ) {
            try {
                $driveService = new GoogleDriveService();

                
                $rutaTemporal = $datosArchivo['archivo']['tmp_name'];
                $nombreOriginal = $datosArchivo['archivo']['name'];
                $mimeType = $datosArchivo['archivo']['type'];

                
                $resultadoDrive = $driveService->uploadFile($rutaTemporal, $nombreOriginal, $mimeType);

                
                if ($resultadoDrive && isset($resultadoDrive['id_drive'])) {
                    $this->materialModel->id_curso = $datosTexto['id_curso'];
                    $this->materialModel->titulo = $datosTexto['titulo'];
                    $this->materialModel->tipo = $datosTexto['tipo']; 
                    
                    
                    $this->materialModel->url_archivo = $resultadoDrive['url_ver'];
                    $this->materialModel->fecha_publicacion = date('Y-m-d');

                    
                    if($this->materialModel->post()) {
                        
                        $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
                        if ($extension === 'txt' || $extension === 'pdf') {
                            try {
                                include_once __DIR__ . '/../services/TranslationService.php';
                                $translationService = new TranslationService();

                                $textoExtraido = $translationService->extractText($rutaTemporal, $extension);
                                if (!empty(trim($textoExtraido))) {
                                    $translatedResult = $translationService->translateToSpanish($textoExtraido);

                                    if ($translatedResult && $translatedResult['detected_language'] === 'en' && !empty($translatedResult['translated_text'])) {
                                        
                                        $tempFileName = pathinfo($nombreOriginal, PATHINFO_FILENAME) . '_es.' . $extension;
                                        $tempTraducido = sys_get_temp_dir() . '/' . $tempFileName;

                                        if ($extension === 'pdf') {
                                            $translationService->generateTranslatedPdf($translatedResult['translated_text'], $datosTexto['titulo'], $tempTraducido);
                                            $mimeTraducido = 'application/pdf';
                                        } else {
                                            file_put_contents($tempTraducido, $translatedResult['translated_text']);
                                            $mimeTraducido = 'text/plain';
                                        }

                                        
                                        $resultadoDriveTraducido = $driveService->uploadFile($tempTraducido, $tempFileName, $mimeTraducido);
                                        @unlink($tempTraducido);

                                        if ($resultadoDriveTraducido && isset($resultadoDriveTraducido['id_drive'])) {
                                            
                                            $this->materialModel->id_curso = $datosTexto['id_curso'];
                                            $this->materialModel->titulo = $datosTexto['titulo'] . " (Traducido al Español)";
                                            $this->materialModel->tipo = $datosTexto['tipo'];
                                            $this->materialModel->url_archivo = $resultadoDriveTraducido['url_ver'];
                                            $this->materialModel->fecha_publicacion = date('Y-m-d');
                                            $this->materialModel->post();
                                        }
                                    }
                                }
                            } catch (Exception $ex) {
                                
                                error_log("Fallo controlado en la traducción automática: " . $ex->getMessage());
                            }
                        }

                        http_response_code(201); 
                        return json_encode(array(
                            "message" => "Material creado y subido a Google Drive exitosamente.",
                            "records" => $this->materialModel
                        ));
                    } else {
                        http_response_code(503);
                        return json_encode(array("message" => "El archivo subió a Drive, pero no se pudo registrar en la base de datos."));
                    }
                } else {
                    http_response_code(500);
                    return json_encode(array("message" => "Error interno al procesar el archivo en Google Drive."));
                }

            } catch (Exception $e) {
                http_response_code(500);
                return json_encode(array("message" => "Excepción detectada: " . $e->getMessage()));
            }
        } else {
            http_response_code(400); 
            return json_encode(array("message" => "Datos incompletos. Se requiere id_curso, titulo, tipo y el archivo adjunto."));
        }
    }
}
?>