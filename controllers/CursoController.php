<?php
class CursoController {
    private $cursoModel;

    public function __construct($db) {
        $this->cursoModel = new Curso($db);
    }

    public function getCursos() {
        $stmt = $this->cursoModel->get();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($result) {
            http_response_code(200);
            return json_encode($result);
        } else {
            http_response_code(404);
            return json_encode(array("message" => "No se encontraron cursos."));
        }
    }

    public function getCursoById($id) {
        $this->cursoModel->id = $id;
        $stmt = $this->cursoModel->getById();
        $num = $stmt->rowCount();

        if($num > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            http_response_code(200);
            return json_encode($row);
        } else {
            http_response_code(404);
            return json_encode(array("message" => "Curso no encontrado."));
        }
    }

    public function createCurso($datos) {
        
        if(
            !empty($datos->nombre) &&
            !empty($datos->categoria) &&
            !empty($datos->id_docente)
        ) {
            $this->cursoModel->nombre = $datos->nombre;
            $this->cursoModel->categoria = $datos->categoria;
            
            $this->cursoModel->descripcion = isset($datos->descripcion) ? $datos->descripcion : null;
            $this->cursoModel->temas = isset($datos->temas) ? $datos->temas : null;
            $this->cursoModel->id_docente = $datos->id_docente;

            if($this->cursoModel->post()) {
                http_response_code(201);
                return json_encode(array("message" => "Curso creado exitosamente.", "records" => $this->cursoModel));
            } else {
                http_response_code(503);
                return json_encode(array("message" => "Servicio no disponible. No se pudo crear el curso."));
            }
        } else {
            http_response_code(400);
            return json_encode(array("message" => "Datos incompletos. Se requiere nombre, categoría e id_docente."));
        }
    }
}
?>