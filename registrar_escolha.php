<?php
header('Content-Type: application/json; charset=utf-8');
include 'conexao.php';

$idUsuario     = isset($_REQUEST['idUsuario'])   ? intval($_REQUEST['idUsuario'])   : 1;
$idHistoria    = isset($_REQUEST['idHistoria'])  ? intval($_REQUEST['idHistoria'])  : 1;
$idCenaDestino = $_REQUEST['idCenaDestino'] ?? $_REQUEST['id_cena_destino'] ?? $_REQUEST['idDestino'] ?? null;
$ganhoDisc     = intval($_REQUEST['ganho_disciplina']    ?? $_REQUEST['ganho_disc'] ?? 0);
$ganhoAuto     = intval($_REQUEST['ganho_auto_compaixao'] ?? $_REQUEST['ganho_auto'] ?? 0);
$ganhoProp     = intval($_REQUEST['ganho_proposito']     ?? $_REQUEST['ganho_prop'] ?? 0);

if ($idCenaDestino === null) {
    echo json_encode(["status" => "erro", "mensagem" => "Parametro idCenaDestino ausente"]);
    exit;
}
$idCenaDestino = intval($idCenaDestino);

// INSERT ... ON DUPLICATE KEY UPDATE
// Atômico: insere na primeira vez, atualiza nas seguintes.
// Requer UNIQUE KEY em (idUsuario, idHistoria) — já existe pelo comportamento anterior.
$sql = "INSERT INTO progresso_historia 
            (idUsuario, idHistoria, pontos_disciplina, pontos_auto_compaixao, pontos_proposito, idCenaAtual)
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            pontos_disciplina     = pontos_disciplina     + VALUES(pontos_disciplina),
            pontos_auto_compaixao = pontos_auto_compaixao + VALUES(pontos_auto_compaixao),
            pontos_proposito      = pontos_proposito      + VALUES(pontos_proposito),
            idCenaAtual           = VALUES(idCenaAtual)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiiiii",
    $idUsuario, $idHistoria,
    $ganhoDisc, $ganhoAuto, $ganhoProp,
    $idCenaDestino
);

if ($stmt->execute()) {
    echo json_encode(["status" => "sucesso"]);
} else {
    echo json_encode(["status" => "erro", "mensagem" => $conn->error]);
}

$stmt->close();
$conn->close();
?>