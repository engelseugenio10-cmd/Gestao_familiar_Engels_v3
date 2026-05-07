<?php
include 'conexao.php';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $marido_id = $_POST['marido_id'];
    $esposa_id = $_POST['esposa_id'];
    $data_casamento = $_POST['data_casamento'];
    $local = $_POST['local_casamento'] ?? null; // Campo opcional que tem na tua tabela
    
    if(empty($marido_id) || empty($esposa_id) || empty($data_casamento)){
        die("Erro: Preencha marido, esposa e data. <a href='casamento.php'>Voltar</a>");
    }
    
    if($marido_id == $esposa_id){
        die("Erro: Marido e esposa não podem ser iguais. <a href='casamento.php'>Voltar</a>");
    }
    
    // Verifica se já existe esse casamento
    $check = $conn->prepare("SELECT id FROM casamentos WHERE pessoa1_id = ? AND pessoa2_id = ?");
    $check->execute([$marido_id, $esposa_id]);
    if($check->rowCount() > 0){
        die("Erro: Este casal já está registrado. <a href='listar.php'>Ver casamentos</a>");
    }
    
    try {
        // USA pessoa1_id e pessoa2_id que é o nome real das tuas colunas
        $stmt = $conn->prepare("INSERT INTO casamentos (pessoa1_id, pessoa2_id, data_casamento, local_casamento) VALUES (?, ?, ?, ?)");
        $stmt->execute([$marido_id, $esposa_id, $data_casamento, $local]);
        
        header("Location: listar.php?msg=casamento_ok");
        exit;
        
    } catch(PDOException $e) {
        die("Erro no banco: " . $e->getMessage() . " <a href='casamento.php'>Voltar</a>");
    }
    
} else {
    header("Location: casamento.php");
    exit;
}
?>