<?php
include 'conexao.php';

$msg = '';
$tipo = '';

if($_POST) {
    $erro = false;
    
    $marido = $conn->query("SELECT nome, nascimento, genero FROM pessoas WHERE id = " . intval($_POST['marido_id']))->fetch();
    $esposa = $conn->query("SELECT nome, nascimento, genero FROM pessoas WHERE id = " . intval($_POST['esposa_id']))->fetch();
    
    if(!$marido || !$esposa) {
        $msg = "Erro: Marido ou esposa não encontrado!";
        $tipo = "erro";
        $erro = true;
    }
    
    if(!$erro && $marido['genero'] != 'M') {
        $msg = "Erro: {$marido['nome']} não é do sexo masculino!";
        $tipo = "erro";
        $erro = true;
    }
    
    if(!$erro && $esposa['genero'] != 'F') {
        $msg = "Erro: {$esposa['nome']} não é do sexo feminino!";
        $tipo = "erro";
        $erro = true;
    }
    
    if(!$erro) {
        $idadeMarido = (new DateTime())->diff(new DateTime($marido['nascimento']))->y;
        $idadeEsposa = (new DateTime())->diff(new DateTime($esposa['nascimento']))->y;
        
        if($idadeMarido < 18) {
            $msg = "Erro: {$marido['nome']} tem {$idadeMarido} anos. Mínimo: 18!";
            $tipo = "erro";
            $erro = true;
        }
        if(!$erro && $idadeEsposa < 18) {
            $msg = "Erro: {$esposa['nome']} tem {$idadeEsposa} anos. Mínimo: 18!";
            $tipo = "erro";
            $erro = true;
        }
    }
    
    if(!$erro) {
        $checkMarido = $conn->prepare("SELECT id FROM casamentos WHERE marido_id = ? AND status = 'ativo'");
        $checkMarido->execute([$_POST['marido_id']]);
        if($checkMarido->fetch()) {
            $msg = "Erro: {$marido['nome']} já está casado!";
            $tipo = "erro";
            $erro = true;
        }
    }
    
    if(!$erro) {
        $checkEsposa = $conn->prepare("SELECT id FROM casamentos WHERE esposa_id = ? AND status = 'ativo'");
        $checkEsposa->execute([$_POST['esposa_id']]);
        if($checkEsposa->fetch()) {
            $msg = "Erro: {$esposa['nome']} já está casada!";
            $tipo = "erro";
            $erro = true;
        }
    }
    
    if(!$erro) {
        try {
            $stmt = $conn->prepare("INSERT INTO casamentos (marido_id, esposa_id, data_casamento, certidao) VALUES (?,?,?,?)");
            $stmt->execute([
                $_POST['marido_id'],
                $_POST['esposa_id'],
                $_POST['data_casamento'],
                !empty($_POST['certidao']) ? $_POST['certidao'] : NULL
            ]);
            $msg = "Casamento registrado com sucesso!";
            $tipo = "sucesso";
            $_POST = array();
        } catch(Exception $e) {
            $msg = "Erro: " . $e->getMessage();
            $tipo = "erro";
        }
    }
}

$pessoas = $conn->query("
    SELECT p.id, p.nome, p.genero 
    FROM pessoas p
    WHERE TIMESTAMPDIFF(YEAR, p.nascimento, CURDATE()) >= 18
    AND p.id NOT IN (
        SELECT marido_id FROM casamentos WHERE status = 'ativo'
        UNION
        SELECT esposa_id FROM casamentos WHERE status = 'ativo'
    )
    ORDER BY p.nome
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Casamento | Gestão Familiar</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{
            font-family:'Segoe UI',sans-serif;
            background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);
            min-height:100vh;
            padding:20px;
            display:flex;
            align-items:center;
            justify-content:center;
        }
        .container{
            width:100%;
            max-width:850px;
            background:#fff;
            padding:50px;
            border-radius:24px;
            box-shadow:0 25px 50px rgba(0,0,0,0.15);
        }
        .header{text-align:center;margin-bottom:40px}
        .header-icon{
            width:80px;
            height:80px;
            background:linear-gradient(135deg,#ec4899,#db2777);
            border-radius:20px;
            display:flex;
            align-items:center;
            justify-content:center;
            margin:0 auto 20px;
            box-shadow:0 10px 25px rgba(236,72,153,0.4);
        }
        .header-icon i{color:#fff;font-size:2.2em}
        .header h1{color:#1a1a2e;font-size:2.2em;font-weight:800}
        .btn-voltar{
            display:inline-flex;
            align-items:center;
            gap:8px;
            background:#f1f5f9;
            color:#475569;
            padding:12px 24px;
            border-radius:12px;
            text-decoration:none;
            font-weight:600;
            margin-bottom:30px;
        }
        .msg{
            padding:18px 24px;
            border-radius:12px;
            margin-bottom:30px;
            font-weight:600;
        }
        .sucesso{background:#d1fae5;color:#065f46;border:2px solid #6ee7b7}
        .erro{background:#fee2e2;color:#991b1b;border:2px solid #fca5a5}
        .form-section{
            background:#f8fafc;
            padding:25px;
            border-radius:16px;
            margin-bottom:25px;
            border:2px solid #e2e8f0;
        }
        .form-group{margin-bottom:20px}
        .form-group label{
            display:block;
            margin-bottom:10px;
            font-weight:600;
            color:#334155;
        }
        .form-group select, .form-group input{
            width:100%;
            padding:14px 16px;
            border:2px solid #e2e8f0;
            border-radius:12px;
            font-size:1em;
        }
        .form-row{display:grid;grid-template-columns:1fr 1fr;gap:20px}
        .btn-salvar{
            width:100%;
            background:linear-gradient(135deg,#ec4899,#db2777);
            color:#fff;
            padding:18px;
            border:none;
            border-radius:14px;
            font-size:1.1em;
            font-weight:700;
            cursor:pointer;
        }
        @media(max-width:768px){
            .container{padding:30px 20px}
            .form-row{grid-template-columns:1fr}
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="btn-voltar">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
        
        <div class="header">
            <div class="header-icon">
                <i class="fas fa-ring"></i>
            </div>
            <h1>Registrar Casamento</h1>
        </div>

        <?php if($msg): ?>
            <div class="msg <?= $tipo ?>">
                <i class="fas fa-<?= $tipo == 'sucesso' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                <?= $msg ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-section">
                <div class="form-row">
                    <div class="form-group">
                        <label>Marido *</label>
                        <select name="marido_id" required>
                            <option value="">Selecione...</option>
                            <?php foreach($pessoas as $p): if($p['genero']=='M'): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nome']) ?></option>
                            <?php endif; endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Esposa *</label>
                        <select name="esposa_id" required>
                            <option value="">Selecione...</option>
                            <?php foreach($pessoas as $p): if($p['genero']=='F'): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nome']) ?></option>
                            <?php endif; endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="form-row">
                    <div class="form-group">
                        <label>Data do Casamento *</label>
                        <input type="date" name="data_casamento" max="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Nº da Certidão</label>
                        <input type="text" name="certidao" placeholder="Ex: 12345/2024">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-salvar">
                <i class="fas fa-save"></i> Registrar Casamento
            </button>
        </form>
    </div>
</body>
</html>