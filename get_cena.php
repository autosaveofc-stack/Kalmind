<?php
header('Content-Type: application/json; charset=utf-8');
include 'conexao.php'; 

// 1. Pega o ID da cena via GET. Se não vier nada, usa 1.
$id = isset($_GET['id']) ? intval($_GET['id']) : 1;

// 2. BUSCA A CENA E O NOME DA HISTÓRIA
// Removido: cor_personagem (causava erro)
// Ajustado: texto_progresso e tipo_cena para bater com seu CREATE TABLE
$sqlCena = "SELECT c.idCena, 
                   c.texto_progresso as texto, 
                   c.imagem_fundo, 
                   c.tipo_cena as tipo, 
                   c.nome_personagem, 
                   c.audio_voz,
                   c.id_proxima_default,
                   h.nome as nome_historia 
            FROM cena c 
            INNER JOIN historia h ON c.idHistoria = h.idHistoria 
            WHERE c.idCena = ?";

$stmt = $conn->prepare($sqlCena);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultCena = $stmt->get_result();

if ($cena = $resultCena->fetch_assoc()) {
    
    // 3. BUSCA AS ALTERNATIVAS (Os botões de escolha)
    // Buscamos os ganhos de atributos para o Java processar o progresso
    $sqlAlt = "SELECT texto_botao as textoBotao, 
                      idCenaDestino, 
                      ganho_disciplina, 
                      ganho_auto_compaixao, 
                      ganho_proposito 
               FROM alternativa 
               WHERE idCenaOrigem = ?";
               
    $stmtAlt = $conn->prepare($sqlAlt);
    $stmtAlt->bind_param("i", $id);
    $stmtAlt->execute();
    $resultAlt = $stmtAlt->get_result();

    $alternativas = [];
    while ($alt = $resultAlt->fetch_assoc()) {
        $alternativas[] = $alt; 
    }

    // 4. MONTAGEM DO PACOTE FINAL
    $cena['alternativas'] = $alternativas;

    // 5. ENVIO PARA O ANDROID
    echo json_encode($cena, JSON_UNESCAPED_UNICODE);

} else {
    http_response_code(404);
    echo json_encode(["erro" => "Cena nao encontrada"], JSON_UNESCAPED_UNICODE);
}

$stmt->close();
if(isset($stmtAlt)) $stmtAlt->close();
$conn->close();
?>