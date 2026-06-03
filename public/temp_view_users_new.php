<?php
try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=nacal_db;port=3306", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->query("SELECT id, name, username, role, is_admin, is_active FROM users");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: {$row['id']} | Name: {$row['name']} | Username: {$row['username']} | Role: {$row['role']} | IsAdmin: {$row['is_admin']} | IsActive: {$row['is_active']}<br>\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
