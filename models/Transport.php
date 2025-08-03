<?php

class Transport
{
    // 🔁 Récupère tous les transporteurs
    public static function all()
    {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT * FROM transports ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 🔍 Récupère un transporteur par son ID
    public static function find($id)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM transports WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ➕ Crée un nouveau transporteur
    public static function create($data)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            INSERT INTO transports (name, transport_type, contact_info, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        return $stmt->execute([
            $data['name'],
            $data['transport_type'],
            $data['contact_info'] ?? null
        ]);
    }

    // ✏️ Met à jour un transporteur existant
    public static function update($id, $data)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            UPDATE transports
            SET name = ?, transport_type = ?, contact_info = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['name'],
            $data['transport_type'],
            $data['contact_info'] ?? null,
            $id
        ]);
    }

    // ❌ Supprime un transporteur
    public static function delete($id)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM transports WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
