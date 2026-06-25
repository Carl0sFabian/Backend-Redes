<?php
class Material {
    private $conn;
    private $table_name = "material";

    public $id;
    public $id_curso;
    public $titulo;
    public $tipo;
    public $url_archivo;
    public $fecha_publicacion;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function get() {
        $query = "SELECT m.id, m.id_curso, m.titulo, m.tipo, m.url_archivo, m.fecha_publicacion, c.nombre as curso_nombre
        FROM " . $this->table_name . " m
        LEFT JOIN cursos c ON m.id_curso = c.id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getById() {
        $query = "SELECT m.id, m.id_curso, m.titulo, m.tipo, m.url_archivo, m.fecha_publicacion, c.nombre as curso_nombre
                  FROM " . $this->table_name . " m
                  LEFT JOIN cursos c ON m.id_curso = c.id
                  WHERE m.id = ? LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        return $stmt;
    }

    public function post() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (id_curso, titulo, tipo, url_archivo) 
                  VALUES (:id_curso, :titulo, :tipo, :url_archivo)";

        $stmt = $this->conn->prepare($query);

        
        $this->id_curso = htmlspecialchars(strip_tags($this->id_curso));
        $this->titulo = htmlspecialchars(strip_tags($this->titulo));
        $this->tipo = htmlspecialchars(strip_tags($this->tipo));
        $this->url_archivo = htmlspecialchars(strip_tags($this->url_archivo));

        $stmt->bindParam(":id_curso", $this->id_curso);
        $stmt->bindParam(":titulo", $this->titulo);
        $stmt->bindParam(":tipo", $this->tipo);
        $stmt->bindParam(":url_archivo", $this->url_archivo);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);

        
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>