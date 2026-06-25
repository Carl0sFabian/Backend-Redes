<?php
class AsistenciaController {
    private $asistenciaModel;

    public function __construct($db) {
        $this->asistenciaModel = new Asistencia($db);
    }

    public function getAsistencias() {
        $stmt = $this->asistenciaModel->get();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($result) {
            http_response_code(200);
            return json_encode($result);
        } else {
            http_response_code(404);
            return json_encode(array("message" => "No se encontraron asistencias."));
        }
    }

    public function getAsistenciaById($id) {
        $this->asistenciaModel->id = $id;
        $stmt = $this->asistenciaModel->getById();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            http_response_code(200);
            return json_encode($row);
        } else {
            http_response_code(404);
            return json_encode(array("message" => "Asistencia no encontrada."));
        }
    }

    public function getAsistenciasByUserId($id_usuario) {
        $this->asistenciaModel->id_usuario = $id_usuario;
        $stmt = $this->asistenciaModel->getByUserId();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($result) {
            http_response_code(200);
            return json_encode($result);
        } else {
            http_response_code(404);
            return json_encode(array("message" => "No se encontraron asistencias para este usuario."));
        }
    }

    public function getAsistenciasBySede($sede) {
        $this->asistenciaModel->sede = $sede;
        $stmt = $this->asistenciaModel->getBySede();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($result) {
            http_response_code(200);
            return json_encode($result);
        } else {
            http_response_code(404);
            return json_encode(array("message" => "No se encontraron asistencias en esta sede."));
        }
    }

    public function getAsistenciasByState($estado) {
        $this->asistenciaModel->estado = $estado;
        $stmt = $this->asistenciaModel->getByState();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($result) {
            http_response_code(200);
            return json_encode($result);
        } else {
            http_response_code(404);
            return json_encode(array("message" => "No se encontraron asistencias con este estado."));
        }
    }

    public function createAsistencia($datos) {
        if(
            !empty($datos->id_usuario) &&
            !empty($datos->sede) &&
            !empty($datos->estado)
        ) {
            $this->asistenciaModel->id_usuario = $datos->id_usuario;
            $this->asistenciaModel->fecha_hora = date('Y-m-d H:i:s');
            $this->asistenciaModel->sede = $datos->sede;
            $this->asistenciaModel->dispositivo_rfid = !empty($datos->dispositivo_rfid) ? $datos->dispositivo_rfid : "CAM-QR-READER";
            $this->asistenciaModel->estado = $datos->estado;

            if($this->asistenciaModel->post()) {
                http_response_code(201);
                return json_encode(array("message" => "Asistencia registrada exitosamente.", "id" => $this->asistenciaModel->id));
            } else {
                http_response_code(503);
                return json_encode(array("message" => "No se pudo registrar la asistencia en la base de datos."));
            }
        } else {
            http_response_code(400);
            return json_encode(array("message" => "Datos incompletos. Se requiere id_usuario, sede y estado."));
        }
    }
}
?>
