<?php
class InscripcionController {
    private $inscripcionModel;

    public function __construct($db) {
        $this->inscripcionModel = new Inscripcion($db);
    }

    public function getInscripciones() {
        $stmt = $this->inscripcionModel->get();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($result) {
            http_response_code(200);
            return json_encode($result);
        } else {
            http_response_code(404);
            return json_encode(array("message" => "No se encontraron inscripciones."));
        }
    }

    public function getByStudentId($id_estudiante) {
        $this->inscripcionModel->id_estudiante = $id_estudiante;
        $stmt = $this->inscripcionModel->getByStudentId();
        $num = $stmt->rowCount();

        if($num > 0) {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            http_response_code(200);
            return json_encode($result);
        } else {
            http_response_code(404);
            return json_encode(array("message" => "No se encontraron inscripciones para este estudiante."));
        }
    }

    public function getByCourseId($id_curso) {
        $this->inscripcionModel->id_curso = $id_curso;
        $stmt = $this->inscripcionModel->getByCourseId();
        $num = $stmt->rowCount();

        if($num > 0) {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            http_response_code(200);
            return json_encode($result);
        } else {
            http_response_code(404);
            return json_encode(array("message" => "No se encontraron inscripciones para este curso."));
        }
    }

    public function createInscripcion($datos) {
        if(!empty($datos->id_estudiante) && !empty($datos->id_curso)) {
            $this->inscripcionModel->id_estudiante = $datos->id_estudiante;
            $this->inscripcionModel->id_curso = $datos->id_curso;
            $this->inscripcionModel->fecha_inicio = isset($datos->fecha_inicio) ? $datos->fecha_inicio : date('Y-m-d');
            $this->inscripcionModel->fecha_fin = isset($datos->fecha_fin) ? $datos->fecha_fin : null;

            if($this->inscripcionModel->post()) {
                http_response_code(201);
                return json_encode(array("message" => "Inscripción creada exitosamente.", "records" => $this->inscripcionModel));
            } else {
                http_response_code(503);
                return json_encode(array("message" => "No se pudo crear la inscripción."));
            }
        } else {
            http_response_code(400);
            return json_encode(array("message" => "Datos incompletos. Se requiere id_estudiante e id_curso."));
        }
    }
}
?>
