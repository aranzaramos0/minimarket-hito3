<?php
declare(strict_types=1);

final class ProductRepository
{
    public function __construct(private PDO $db) {}

    public function allActive(): array
    {
        return $this->db->query('SELECT id, nombre, proveedor, fecha_vencimiento, precio, stock, eliminado FROM Producto WHERE eliminado = 0 ORDER BY id DESC')->fetchAll();
    }

    public function findActive(int $id): ?array
    {
        $query = $this->db->prepare('SELECT id, nombre, proveedor, fecha_vencimiento, precio, stock, eliminado FROM Producto WHERE id = :id AND eliminado = 0');
        $query->execute(['id' => $id]);
        return $query->fetch() ?: null;
    }

    public function create(array $data): array
    {
        $query = $this->db->prepare('INSERT INTO Producto (nombre, proveedor, fecha_vencimiento, precio, stock) VALUES (:nombre, :proveedor, :fecha_vencimiento, :precio, :stock)');
        $query->execute($data);
        return $this->findActive((int) $this->db->lastInsertId());
    }

    public function update(int $id, array $data): ?array
    {
        $query = $this->db->prepare('UPDATE Producto SET nombre = :nombre, proveedor = :proveedor, fecha_vencimiento = :fecha_vencimiento, precio = :precio, stock = :stock WHERE id = :id AND eliminado = 0');
        $query->execute([...$data, 'id' => $id]);
        return $this->findActive($id);
    }

    public function softDelete(int $id): bool
    {
        $query = $this->db->prepare('UPDATE Producto SET eliminado = 1 WHERE id = :id AND eliminado = 0');
        $query->execute(['id' => $id]);
        return $query->rowCount() === 1;
    }
}
