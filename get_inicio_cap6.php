<?php
header('Content-Type: application/json; charset=utf-8');
include 'conexao.php';
 
$idUsuario  = isset($_GET['idUsuario'])  ? intval($_GET['idUsuario'])  : 1;
$idHistoria = isset($_GET['idHistoria']) ? intval($_GET['idHistoria']) : 1;
 
$sql = "SELECT pontos_disciplina, pontos_auto_compaixao, pontos_proposito 
        FROM progresso_historia 
        WHERE idUsuario = ? AND idHistoria = ?";
 
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $idUsuario, $idHistoria);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
 
$disc = $res['pontos_disciplina']      ?? 0;
$auto = $res['pontos_auto_compaixao']  ?? 0;
$prop = $res['pontos_proposito']       ?? 0;
 
// Decide qual caminho do Cap 6 baseado no atributo dominante
if ($disc >= $auto && $disc >= $prop)     $idCena = 129; // Disciplina
elseif ($auto >= $disc && $auto >= $prop) $idCena = 138; // Auto-compaixão
else                                       $idCena = 148; // Propósito
 
echo json_encode(["idCena" => $idCena], JSON_UNESCAPED_UNICODE);
 
$stmt->close();
$conn->close();
?>