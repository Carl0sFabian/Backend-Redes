<?php
class Inscripcion {
    private $conn;
    private $table_name = "inscripciones";

    public $id;
    public $id_estudiante;
    public $id_curso;
    public $fecha_inicio;
    public $fecha_fin;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function get() {
        $query = "SELECT i.id, i.id_estudiante, i.id_curso, i.fecha_inicio, i.fecha_fin, u.nombre_completo as estudiante_nombre, c.nombre as curso_nombre
                  FROM " . $this->table_name . " i
                  LEFT JOIN usuarios u ON i.id_estudiante = u.id
                  LEFT JOIN cursos c ON i.id_curso = c.id";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getByStudentId() {
        $query = "SELECT i.id, i.id_estudiante, i.id_curso, i.fecha_inicio, i.fecha_fin,
                         c.nombre as curso_nombre
                  FROM " . $this->table_name . " i
                  LEFT JOIN cursos c ON i.id_curso = c.id
                  WHERE i.id_estudiante = :id_estudiante";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_estudiante", $this->id_estudiante);
        $stmt->execute();
        return $stmt;
    }

    public function getByCourseId() {
        $query = "SELECT i.id, i.id_estudiante, i.id_curso, i.fecha_inicio, i.fecha_fin,
                         u.nombre_completo as estudiante_nombre
                  FROM " . $this->table_name . " i
                  LEFT JOIN usuarios u ON i.id_estudiante = u.id
                  WHERE i.id_curso = :id_curso";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_curso", $this->id_curso);
        $stmt->execute();
        return $stmt;
    }

    public function post() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET id_estudiante=:id_estudiante, id_curso=:id_curso, 
                      fecha_inicio=:fecha_inicio, fecha_fin=:fecha_fin";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id_estudiante", $this->id_estudiante);
        $stmt->bindParam(":id_curso", $this->id_curso);
        $stmt->bindParam(":fecha_inicio", $this->fecha_inicio);
        $stmt->bindParam(":fecha_fin", $this->fecha_fin);

        return $stmt->execute();
    }
}
?>