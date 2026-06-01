<?php
header('Content-Type: application/json; charset=utf-8');
include 'conexao.php';
 
$idUsuario  = isset($_REQUEST['idUsuario'])  ? intval($_REQUEST['idUsuario'])  : 1;
$idHistoria = isset($_REQUEST['idHistoria']) ? intval($_REQUEST['idHistoria']) : 1;
 
$sql = "UPDATE progresso_historia 
        SET pontos_disciplina = 0,
            pontos_auto_compaixao = 0,
            pontos_proposito = 0,
            idCenaAtual = 1
        WHERE idUsuario = ? AND idHistoria = ?";
 
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $idUsuario, $idHistoria);
 
if ($stmt->execute()) {
    echo json_encode(["status" => "sucesso"]);
} else {
    echo json_encode(["status" => "erro", "mensagem" => $conn->error]);
}
 
$stmt->close();
$conn->close();
?>
 