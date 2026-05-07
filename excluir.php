<?php
include 'conexao.php';
$id = $_GET['id'];

$stmt = $conn->prepare("DELETE FROM pessoas WHERE id = ?");
$stmt->execute([$id]);

header("Location: listar.php?msg=excluido");
exit;
?>