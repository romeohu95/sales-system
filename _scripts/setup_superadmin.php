<?php
include '../_ventas/includes/config/config.php';
include '../_ventas/includes/config/database_class.php';
include '../_ventas/includes/config/settings.php';

// Definir credenciales del usuario de prueba
$testUserEmail = 'test@example.com';
$testUserPassword = 'password123';
$hashedPassword = password_hash($testUserPassword, PASSWORD_BCRYPT);
$superadminRole = 'superadmin';

// Crear conexión a la base de datos
$database = new Database();
$db = $database->getConnection();

// Verificar si el usuario ya existe
$query = "SELECT * FROM users WHERE email = :email";
$stmt = $db->prepare($query);
$stmt->bindParam(':email', $testUserEmail);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    echo "El usuario ya existe.";
} else {
    // Insertar el usuario de prueba como superadmin
    $query = "INSERT INTO users (email, password, role) VALUES (:email, :password, :role)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $testUserEmail);
    $stmt->bindParam(':password', $hashedPassword);
    $stmt->bindParam(':role', $superadminRole);

    if ($stmt->execute()) {
        echo "Usuario superadmin creado exitosamente.";
    } else {
        echo "Error al crear el usuario superadmin.";
    }
}
?>