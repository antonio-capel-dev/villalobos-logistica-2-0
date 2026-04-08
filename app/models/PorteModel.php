<?php
// app/models/PorteModel.php

namespace Models;

use Core\Model;

class PorteModel extends Model {

    public function getTodos(): array {
        $stmt = $this->db->query("
            SELECT p.*,
                   c.nombre AS cliente_nombre,
                   d.nombre AS conductor_nombre
            FROM portes p
            LEFT JOIN usuarios c ON p.cliente_id   = c.id
            LEFT JOIN usuarios d ON p.conductor_id = d.id
            ORDER BY p.fecha_programada DESC
        ");
        return $stmt->fetchAll();
    }

    public function getPorCliente(int $clienteId): array {
        $stmt = $this->db->prepare("
            SELECT p.*,
                   c.nombre AS cliente_nombre,
                   d.nombre AS conductor_nombre
            FROM portes p
            LEFT JOIN usuarios c ON p.cliente_id   = c.id
            LEFT JOIN usuarios d ON p.conductor_id = d.id
            WHERE p.cliente_id = :id
            ORDER BY p.fecha_programada DESC
        ");
        $stmt->execute([':id' => $clienteId]);
        return $stmt->fetchAll();
    }

    public function getPorId(int $id): array|false {
        $stmt = $this->db->prepare("SELECT * FROM portes WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
}
