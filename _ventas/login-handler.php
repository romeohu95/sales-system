<?php
session_start();
include 'includes/config/database.php'; // Archivo donde se configura la conexión a la base de datos
include 'includes/config/settings.php'; // Archivo donde se configura la llave de cifrado y la duración de la sesión

function encryptCookie($data, $key) {
    $encryption_key = base64_encode(hash('sha256', $key, true));
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', $encryption_key, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}

function decryptCookie($data, $key) {
    $encryption_key = base64_encode(hash('sha256', $key, true));
    list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
    return openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key, 0, $iv);
}

function logSession($conn, $email, $success, $message) {
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $stmt = $conn->prepare("INSERT INTO log_sesiones (correo, exito, fecha, ip, mensaje, user_agent) VALUES (?, ?, NOW(), ?, ?, ?)");
    if ($stmt === false) {
        error_log("Error preparando la consulta para logSession: " . $conn->error);
        return;
    }
    $stmt->bind_param("sisss", $email, $success, $ip_address, $message, $user_agent);
    $stmt->execute();
    $stmt->close();
}

function registerSession($conn, $user_id, $tiempo_sesion) {
    $session_token = bin2hex(random_bytes(32));
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $stmt = $conn->prepare("INSERT INTO sesiones (id_usuario, sesion_token, ip_address, user_agent, fecha_creacion, fecha_expiracion, estado) VALUES (?, ?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL ? SECOND), 'abierta')");
    if ($stmt === false) {
        error_log("Error preparando la consulta para registerSession: " . $conn->error);
        return;
    }
    $stmt->bind_param("isssi", $user_id, $session_token, $ip_address, $user_agent, $tiempo_sesion);
    $stmt->execute();
    $stmt->close();
}

function clearCookies() {
    setcookie('email', '', time() - 3600, "/");
    setcookie('password', '', time() - 3600, "/");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];
    $remember_me = isset($_POST["remember_me"]);
    $use_cookie = isset($_POST["use_cookie"]) && $_POST["use_cookie"] == '1';

    // Si el checkbox "Recordarme" está marcado y use_cookie es verdadero, desencripta los valores de las cookies
    if ($remember_me && $use_cookie && isset($_COOKIE['email']) && isset($_COOKIE['password'])) {
        $email = decryptCookie($_COOKIE['email'], $publicKeyToken);
        $password = decryptCookie($_COOKIE['password'], $publicKeyToken);
    }

    // Consulta para verificar el usuario
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Error preparando la consulta para verificar el usuario: " . $conn->error);
        header("Location: pages-login.php?error=Internal server error");
        exit();
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Verificar la contraseña
        if (password_verify($password, $user['password'])) {
            // Establecer variables de sesión
            $_SESSION["loggedin"] = true;
            $_SESSION["email"] = $user['email'];

            // Registrar sesión
            registerSession($conn, $user['id'], $tiempo_sesion);
            
            // Registrar intento de sesión exitoso
            logSession($conn, $email, 1, "Inicio de sesión exitoso");

            // Establecer cookies si se marcó "Recordarme"
            if ($remember_me) {
                $encrypted_email = encryptCookie($email, $publicKeyToken);
                $encrypted_password = encryptCookie($password, $publicKeyToken);
                setcookie('email', $encrypted_email, time() + (86400 * 30), "/"); // 30 días
                setcookie('password', $encrypted_password, time() + (86400 * 30), "/"); // 30 días
            }

            // Redirigir a una página protegida
            header("Location: dashboard.php");
            exit();
        } else {
            // Contraseña incorrecta
            logSession($conn, $email, 0, "Contraseña incorrecta");
            clearCookies();
            header("Location: pages-login.php?error=Incorrect password&email=" . urlencode($email));
            exit();
        }
    } else {
        // Usuario no existe
        logSession($conn, $email, 0, "Usuario no existe");
        clearCookies();
        header("Location: pages-login.php?error=User does not exist");
        exit();
    }

    // Cerrar conexión
    $stmt->close();
} else {
    // Redirigir a la página de inicio de sesión si el método de solicitud no es POST
    header("Location: pages-login.php");
    exit();
}
?>