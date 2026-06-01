<?php
header('Content-Type: application/json; charset=utf-8');
include 'conexao.php';

// 1. Recebe os dados
$idUsuario   = isset($_REQUEST['idUsuario']) ? intval($_REQUEST['idUsuario']) : 1;
$idHistoria  = isset($_REQUEST['idHistoria']) ? intval($_REQUEST['idHistoria']) : 1;
$idCenaSolicitada = isset($_REQUEST['idCena']) ? intval($_REQUEST['idCena']) : 1;

// 2. BUSCAR O ACUMULADO DO USUÁRIO
$sqlProgresso = "SELECT pontos_disciplina, pontos_auto_compaixao, pontos_proposito 
                 FROM progresso_historia 
                 WHERE idUsuario = ? AND idHistoria = ?";

$stmtP = $conn->prepare($sqlProgresso);
$stmtP->bind_param("ii", $idUsuario, $idHistoria);
$stmtP->execute();
$resProgresso = $stmtP->get_result()->fetch_assoc();

// 3. BUSCAR A CENA (Mantendo Aliases para as propriedades principais da Cena)
$sqlCena = "SELECT idCena, 
                   texto_progresso as texto, 
                   imagem_fundo as imagemFundo, 
                   tipo_cena as tipo, 
                   nome_personagem as nomePersonagem, 
                   audio_voz as audioVoz, 
                   id_proxima_default as idProximaDefault,
                   imagem_lumen as imagemLumen
            FROM cena WHERE idCena = ?";

$stmtC = $conn->prepare($sqlCena);
$stmtC->bind_param("i", $idCenaSolicitada);
$stmtC->execute();
$cena = $stmtC->get_result()->fetch_assoc();

if (!$cena) {
    echo json_encode(["erro" => "Cena nao encontrada"]);
    exit;
}

// LÓGICA DE IMPACTO/FINAL
if ($cena['tipo'] == 'impacto' && empty($cena['idProximaDefault'])) {
    $disc = $resProgresso['pontos_disciplina'] ?? 0;
    $auto = $resProgresso['pontos_auto_compaixao'] ?? 0;
    $prop = $resProgresso['pontos_proposito'] ?? 0;
    
    if ($disc >= $auto && $disc >= $prop) $idFinal = 99;
    elseif ($auto >= $disc && $auto >= $prop) $idFinal = 100;
    else $idFinal = 101;
    
    $stmtC->bind_param("i", $idFinal);
    $stmtC->execute();
    $cena = $stmtC->get_result()->fetch_assoc();
}

// 4. BUSCAR ALTERNATIVAS (Padronizado para texto_botao)
$sqlAlt = "SELECT texto_botao, 
                  idCenaDestino, 
                  ganho_disciplina, 
                  ganho_auto_compaixao, 
                  ganho_proposito 
           FROM alternativa WHERE idCenaOrigem = ?";

$stmtA = $conn->prepare($sqlAlt);
$stmtA->bind_param("i", $cena['idCena']);
$stmtA->execute();
$resAlt = $stmtA->get_result();

$alternativas = [];
while ($alt = $resAlt->fetch_assoc()) {
    // Conversão explícita para Inteiros (Essencial para o Retrofit/GSON)
    $alt['idCenaDestino'] = (int)$alt['idCenaDestino'];
    $alt['ganho_disciplina'] = (int)$alt['ganho_disciplina'];
    $alt['ganho_auto_compaixao'] = (int)$alt['ganho_auto_compaixao'];
    $alt['ganho_proposito'] = (int)$alt['ganho_proposito'];
    
    $alternativas[] = $alt;
}
$cena['alternativas'] = $alternativas;

// 5. SAÍDA
echo json_encode($cena, JSON_UNESCAPED_UNICODE);

$stmtP->close(); $stmtC->close(); $stmtA->close(); $conn->close();
?>