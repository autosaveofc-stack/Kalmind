<?php
header('Content-Type: application/json; charset=utf-8');
include 'conexao.php';

$sql = "SELECT h.idHistoria, h.nome, COUNT(c.idCena) as total_cenas
        FROM historia h
        INNER JOIN cena c ON c.idHistoria = h.idHistoria
        GROUP BY h.idHistoria, h.nome";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$historias = [];
while ($row = $result->fetch_assoc()) {
    $row['total_cenas'] = (int)$row['total_cenas'];
    $historias[] = $row;
}

echo json_encode($historias, JSON_UNESCAPED_UNICODE);
$stmt->close();
$conn->close();
?>