<?php
include 'conexao.php';
$nome = $_POST['nome'];
$data_nascimento = $_POST['data_nascimento'];
$sexo = $_POST['sexo'];
$id_pai = !empty($_POST['id_pai']) ? $_POST['id_pai'] : NULL;
$id_mae = !empty($_POST['id_mae']) ? $_POST['id_mae'] : NULL;
$stmt = $conexao->prepare("INSERT INTO pessoas (nome, data_nascimento, sexo, id_pai, id_mae, apelido_familiar) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $_POST['nome'], $_POST['data_nascimento'], $_POST['sexo'], $pai, $mae, $_POST['apelido_familiar']);

$stmt->execute();
header("Location: cadastro.php?sucesso=1");
?>