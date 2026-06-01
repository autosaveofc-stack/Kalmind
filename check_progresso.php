<?php
header('Content-Type: application/json; charset=utf-8');
include 'conexao.php';

// 1. Recebe os dados com proteção contra valores vazios
$idUsuario  = isset($_GET['idUsuario']) ? intval($_GET['idUsuario']) : 1;
$idHistoria = isset($_GET['idHistoria']) ? intval($_GET['idHistoria']) : 1;

// 2. Se não houver IDs válidos, retorna um erro amigável ao invés de quebrar
if ($idUsuario == 0 || $idHistoria == 0) {
    echo json_encode(["status" => "erro", "mensagem" => "Parametros insuficientes"]);
    exit;
}

// 3. Busca a última cena onde o usuário parou
// AJUSTE: Mudei para $conn para casar com sua variável de conexão
$sql = "SELECT idCenaAtual FROM progresso_historia WHERE idUsuario = ? AND idHistoria = ? ORDER BY idCenaAtual DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $idUsuario, $idHistoria);
$stmt->execute();
$resultado = $stmt->get_result()->fetch_assoc();

if ($resultado) {
    // Se encontrou progresso, retorna a cena salva
    echo json_encode([
        "status" => "continua", 
        "idCena" => (int)$resultado['idCenaAtual']
    ], JSON_UNESCAPED_UNICODE);
} else {
    // Se não encontrou, avisa ao Android para começar da cena 1
    echo json_encode([
        "status" => "novo", 
        "idCena" => 1
    ], JSON_UNESCAPED_UNICODE);
}

$stmt->close();
$conn->close();
?>