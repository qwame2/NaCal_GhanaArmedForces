<?php
try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=nacal_db;port=3306", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $hash = password_hash('AdminRoot1!', PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'Emma'");
    $stmt->execute([$hash]);
    echo "Password updated successfully.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
