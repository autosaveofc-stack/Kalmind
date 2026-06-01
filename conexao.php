<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "bdkalmind";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    // Garante que o cabeçalho seja JSON mesmo na falha
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        "status" => "erro",
        "mensagem" => "Falha na conexão com o banco de dados local: " . $conn->connect_error
    ], JSON_UNESCAPED_UNICODE);
    exit; // Para a execução de forma limpa
}

$conn->set_charset("utf8");
?>