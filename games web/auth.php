<?php
// Configuración de la conexión a la base de datos
$dbhost = 'localhost';
$dbuser = 'c2721154_anon';
$dbpass = 'delaFA79no';
$dbname = 'c2721154_anon';

$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Manejar la acción solicitada
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'login') {
        login($conn);
    } elseif ($action === 'register') {
        register($conn);
    } else {
        echo json_encode(["success" => false, "message" => "Acción no válida."]);
    }
}

// Función para manejar el inicio de sesión
function login($conn) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        echo json_encode(["success" => false, "message" => "Por favor, completa todos los campos."]);
        return;
    }

    $stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($hashedPassword);
        $stmt->fetch();

        if (password_verify($password, $hashedPassword)) {
            echo json_encode(["success" => true, "message" => "Inicio de sesión exitoso."]);
        } else {
            echo json_encode(["success" => false, "message" => "Contraseña incorrecta."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "El usuario no existe."]);
    }

    $stmt->close();
}

// Función para manejar el registro
function register($conn) {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($email) || empty($password)) {
        echo json_encode(["success" => false, "message" => "Por favor, completa todos los campos."]);
        return;
    }

    // Verificar si el usuario ya existe
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "El usuario ya está registrado."]);
        $stmt->close();
        return;
    }

    $stmt->close();

    // Insertar nuevo usuario
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $hashedPassword);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Registro exitoso."]);
    } else {
        echo json_encode(["success" => false, "message" => "Error al registrar el usuario."]);
    }

    $stmt->close();
}

$conn->close();
?>
