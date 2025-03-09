<?php
session_start();
include '../_ventas/includes/config/database.php';
include '../_ventas/includes/config/settings.php';

function validateSuperadmin($email, $password) {
    global $server_ConexionDB, $user_ConexionDB, $password_ConexionDB, $database_ConexionDB;

    try {
        $conn = new PDO("mysql:host=$server_ConexionDB;dbname=$database_ConexionDB", $user_ConexionDB, $password_ConexionDB);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email AND role = 'superadmin'");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            return true;
        } else {
            return false;
        }
    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
        return false;
    }
}

$email = 'test@example.com';
$password = 'password123';

if (validateSuperadmin($email, $password)) {
    echo "Validación exitosa: Las credenciales del superadmin son correctas.";
} else {
    echo "Validación fallida: Las credenciales del superadmin no son correctas.";
}
?>