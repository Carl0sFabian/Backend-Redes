<?php
class Rendimiento {
    private $conn;
    private $table_name = "rendimiento_estudiante";

    public $id;
    public $id_estudiante;
    public $id_curso;
    public $total_asistencias;
    public $total_faltas;
    public $nota_final;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function get() {
        $query = "SELECT r.id, r.id_estudiante, r.id_curso, r.total_asistencias, r.total_faltas, r.nota_final, u.nombre_completo as estudiante_nombre, c.nombre as curso_nombre
                  FROM " . $this->table_name . " r
                  LEFT JOIN usuarios u ON r.id_estudiante = u.id
                  LEFT JOIN cursos c ON r.id_curso = c.id";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getByStudentId() {
        $query = "SELECT r.id, r.id_estudiante, r.id_curso, r.total_asistencias, r.total_faltas, r.nota_final,
                         c.nombre as curso_nombre
                  FROM " . $this->table_name . " r
                  LEFT JOIN cursos c ON r.id_curso = c.id
                  WHERE r.id_estudiante = :id_estudiante";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_estudiante", $this->id_estudiante);
        $stmt->execute();
        return $stmt;
    }

    public function getByCourseId() {
        $query = "SELECT r.id, r.id_estudiante, r.id_curso, r.total_asistencias, r.total_faltas, r.nota_final,
                         u.nombre_completo as estudiante_nombre
                  FROM " . $this->table_name . " r
                  LEFT JOIN usuarios u ON r.id_estudiante = u.id
                  WHERE r.id_curso = :id_curso";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_curso", $this->id_curso);
        $stmt->execute();
        return $stmt;
    }

    public function getTotalAttendances() {
        $query = "SELECT SUM(total_asistencias) as total_asistencias
                  FROM " . $this->table_name . "
                  WHERE id_estudiante = :id_estudiante";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_estudiante", $this->id_estudiante);
        $stmt->execute();
        return $stmt;
    }

    public function getTotalAbsences() {
        $query = "SELECT SUM(total_faltas) as total_faltas
                  FROM " . $this->table_name . "
                  WHERE id_estudiante = :id_estudiante";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_estudiante", $this->id_estudiante);
        $stmt->execute();
        return $stmt;
    }

    public function getAverageScore() {
        $query = "SELECT AVG(nota_final) as promedio_nota
                  FROM " . $this->table_name . "
                  WHERE id_estudiante = :id_estudiante";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_estudiante", $this->id_estudiante);
        $stmt->execute();
        return $stmt;
    }

    public function post() {
        $query = "INSERT INTO " . $this->table_name . "
                  SET id_estudiante=:id_estudiante, id_curso=:id_curso,
                      total_asistencias=:total_asistencias, total_faltas=:total_faltas, nota_final=:nota_final";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id_estudiante", $this->id_estudiante);
        $stmt->bindParam(":id_curso", $this->id_curso);
        $stmt->bindParam(":total_asistencias", $this->total_asistencias);
        $stmt->bindParam(":total_faltas", $this->total_faltas);
        $stmt->bindParam(":nota_final", $this->nota_final);

        return $stmt->execute();
    }

    public function put() {
        $query = "UPDATE " . $this->table_name . "
                  SET total_asistencias=:total_asistencias,
                      total_faltas=:total_faltas,
                      nota_final=:nota_final
                  WHERE id=:id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":total_asistencias", $this->total_asistencias);
        $stmt->bindParam(":total_faltas", $this->total_faltas);
        $stmt->bindParam(":nota_final", $this->nota_final);

        return $stmt->execute();
    }
}
?>