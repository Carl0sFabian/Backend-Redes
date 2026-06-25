<?php
class UsuarioController {
    private $usuarioModel;

    public function __construct($db) {
        $this->usuarioModel = new Usuario($db);
    }

    public function getUsuarios() {
        $stmt = $this->usuarioModel->get();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($result) {
            http_response_code(200);
            return json_encode($result);
        } else {
            http_response_code(404);
            return json_encode(array("message" => "No se encontraron inscripciones."));
        }
    }

    public function createUsuario($datos) {
        
        if(
            !empty($datos->codigo) &&
            !empty($datos->nombre_completo) &&
            !empty($datos->correo) &&
            !empty($datos->tipo) &&
            !empty($datos->password)
        ) {
            $this->usuarioModel->codigo = $datos->codigo;
            $this->usuarioModel->nombre_completo = $datos->nombre_completo;
            $this->usuarioModel->correo = $datos->correo;
            $this->usuarioModel->tipo = $datos->tipo;

            
            $hash_seguro = password_hash($datos->password, PASSWORD_BCRYPT);
            $this->usuarioModel->password = $hash_seguro;

            
            if($this->usuarioModel->post()) {
                http_response_code(201);
                return json_encode(array("message" => "Usuario creado exitosamente.", "records" => $this->usuarioModel));
            } else {
                http_response_code(503);
                return json_encode(array("message" => "No se pudo crear el usuario. El código o correo podría estar duplicado."));
            }
        } else {
            http_response_code(400);
            return json_encode(array("message" => "Datos incompletos. Por favor revise el formulario."));
        }
    }

    public function getUsuarioById($id) {
        $this->usuarioModel->id = $id;
        $stmt = $this->usuarioModel->getById();
        $num = $stmt->rowCount();

        if($num > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            http_response_code(200);
            return json_encode($row);
        } else {
            http_response_code(404);
            return json_encode(array("message" => "Usuario no encontrado."));
        }
    }

    public function updateUsuario($datos) {
        if(!empty($datos->id) && !empty($datos->codigo) && !empty($datos->nombre_completo) && !empty($datos->correo) && !empty($datos->tipo)) {
            
            $this->usuarioModel->id = $datos->id;
            $this->usuarioModel->codigo = $datos->codigo;
            $this->usuarioModel->nombre_completo = $datos->nombre_completo;
            $this->usuarioModel->correo = $datos->correo;
            $this->usuarioModel->tipo = $datos->tipo;

            if($this->usuarioModel->put()) {
                http_response_code(200);
                return json_encode(array("message" => "Usuario actualizado exitosamente."));
            } else {
                http_response_code(503);
                return json_encode(array("message" => "No se pudo actualizar el usuario."));
            }
        } else {
            http_response_code(400);
            return json_encode(array("message" => "Datos incompletos. Faltan campos."));
        }
    }

    public function deleteUsuario($datos) {
        if(!empty($datos->id)) {
            $this->usuarioModel->id = $datos->id;

            if($this->usuarioModel->delete()) {
                http_response_code(200);
                return json_encode(array("message" => "Usuario eliminado exitosamente."));
            } else {
                http_response_code(503);
                return json_encode(array("message" => "No se pudo eliminar el usuario."));
            }
        } else {
            http_response_code(400);
            return json_encode(array("message" => "Debe proporcionar el ID del usuario a eliminar."));
        }
    }
}
?>