<?php
class RendimientoController {
    private $rendimientoModel;

    public function __construct($db) {
        $this->rendimientoModel = new Rendimiento($db);
    }

    public function getRendimientos() {
        $stmt = $this->rendimientoModel->get();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        http_response_code(200);
        return json_encode($result);
    }

    public function getByStudentId($id_estudiante) {
        $this->rendimientoModel->id_estudiante = $id_estudiante;
        $stmt = $this->rendimientoModel->getByStudentId();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        http_response_code(200);
        return json_encode($result);
    }

    public function getByCourseId($id_curso) {
        $this->rendimientoModel->id_curso = $id_curso;
        $stmt = $this->rendimientoModel->getByCourseId();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        http_response_code(200);
        return json_encode($result);
    }

    public function getTotalAttendances($id_estudiante) {
        $this->rendimientoModel->id_estudiante = $id_estudiante;
        $stmt = $this->rendimientoModel->getTotalAttendances();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        http_response_code(200);
        return json_encode($row);
    }

    public function getTotalAbsences($id_estudiante) {
        $this->rendimientoModel->id_estudiante = $id_estudiante;
        $stmt = $this->rendimientoModel->getTotalAbsences();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        http_response_code(200);
        return json_encode($row);
    }

    public function getAverageScore($id_estudiante) {
        $this->rendimientoModel->id_estudiante = $id_estudiante;
        $stmt = $this->rendimientoModel->getAverageScore();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        http_response_code(200);
        return json_encode($row);
    }

    public function createRendimiento($datos) {
        if(!empty($datos->id_estudiante) && !empty($datos->id_curso)) {
            $this->rendimientoModel->id_estudiante = $datos->id_estudiante;
            $this->rendimientoModel->id_curso = $datos->id_curso;
            $this->rendimientoModel->total_asistencias = isset($datos->total_asistencias) ? $datos->total_asistencias : 0;
            $this->rendimientoModel->total_faltas = isset($datos->total_faltas) ? $datos->total_faltas : 0;
            $this->rendimientoModel->nota_final = isset($datos->nota_final) ? $datos->nota_final : null;

            if($this->rendimientoModel->post()) {
                http_response_code(201);
                return json_encode(array("message" => "Rendimiento creado exitosamente.", "records" => $this->rendimientoModel));
            } else {
                http_response_code(503);
                return json_encode(array("message" => "No se pudo crear el rendimiento."));
            }
        } else {
            http_response_code(400);
            return json_encode(array("message" => "Datos incompletos. Se requiere id_estudiante e id_curso."));
        }
    }

    public function updateRendimiento($id, $datos) {
        $this->rendimientoModel->id = $id;
        $this->rendimientoModel->total_asistencias = isset($datos->total_asistencias) ? $datos->total_asistencias : 0;
        $this->rendimientoModel->total_faltas = isset($datos->total_faltas) ? $datos->total_faltas : 0;
        $this->rendimientoModel->nota_final = isset($datos->nota_final) ? $datos->nota_final : null;

        if($this->rendimientoModel->put()) {
            http_response_code(200);
            return json_encode(array("message" => "Rendimiento actualizado exitosamente."));
        } else {
            http_response_code(503);
            return json_encode(array("message" => "No se pudo actualizar el rendimiento."));
        }
    }
}
?>
