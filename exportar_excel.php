<?php
include 'conexao.php';
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=familia_engels.xls");
header("Pragma: no-cache");

echo "<table border='1'>";
echo "<tr><th>ID</th><th>Nome</th><th>Nascimento</th><th>Genero</th><th>Pai</th><th>Mae</th></tr>";

$sql = "SELECT p.id, p.nome, p.nascimento, p.genero, pai.nome as pai, mae.nome as mae
        FROM pessoas p
        LEFT JOIN pessoas pai ON p.pai_id = pai.id
        LEFT JOIN pessoas mae ON p.mae_id = mae.id";
        
foreach($conn->query($sql) as $row) {
    echo "<tr>";
    echo "<td>{$row['id']}</td>";
    echo "<td>{$row['nome']}</td>";
    echo "<td>{$row['nascimento']}</td>";
    echo "<td>{$row['genero']}</td>";
    echo "<td>{$row['pai']}</td>";
    echo "<td>{$row['mae']}</td>";
    echo "</tr>";
}
echo "</table>";
?>