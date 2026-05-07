<?php
include 'conexao.php';

$id = $_GET['id'] ?? 0;

// BUSCA A PESSOA
$pessoa = $conn->query("SELECT * FROM pessoas WHERE id = $id")->fetch(PDO::FETCH_ASSOC);
if(!$pessoa) die("Pessoa não encontrada!");

// ATUALIZA SE ENVIOU FORM
if($_POST) {
    try {
        $stmt = $conn->prepare("UPDATE pessoas SET nome=?, familia=?, bi=?, nascimento=?, genero=?, pai_id=?, mae_id=? WHERE id=?");
        $stmt->execute([
            $_POST['nome'],
            $_POST['familia'],
            $_POST['bi'],
            $_POST['nascimento'],
            $_POST['genero'],
            $_POST['pai_id']?: NULL,
            $_POST['mae_id']?: NULL,
            $id
        ]);
        header("Location: listar.php?sucesso=1");
        exit;
    } catch(Exception $e) {
        $msg = "Erro: " . $e->getMessage();
        $tipo = "erro";
    }
}

// BUSCA PESSOAS PRA SELECT DE PAI/MÃE
$pessoas = $conn->query("SELECT id, nome, genero FROM pessoas WHERE id != $id ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Membro | Gestão Familiar</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{
            font-family:'Inter','Segoe UI',sans-serif;
            background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);
            min-height:100vh;
            padding:20px;
            display:flex;
            align-items:center;
            justify-content:center;
        }
      .container{
            width:100%;
            max-width:800px;
            background:rgba(255,255,255,0.95);
            backdrop-filter:blur(20px);
            padding:50px;
            border-radius:24px;
            box-shadow:0 25px 50px rgba(0,0,0,0.15);
            border:1px solid rgba(255,255,255,0.3);
            animation:slideUp 0.5s ease;
        }
        @keyframes slideUp{
            from{opacity:0;transform:translateY(30px)}
            to{opacity:1;transform:translateY(0)}
        }
      .header{text-align:center;margin-bottom:40px}
      .header-icon{
            width:80px;
            height:80px;
            background:linear-gradient(135deg,#f59e0b,#d97706);
            border-radius:20px;
            display:flex;
            align-items:center;
            justify-content:center;
            margin:0 auto 20px;
            box-shadow:0 10px 25px rgba(245,158,11,0.4);
        }
      .header-icon i{color:#fff;font-size:2.2em}
      .header h1{color:#1a1a2e;font-size:2.2em;font-weight:800;margin-bottom:8px}
      .header p{color:#64748b;font-size:1.1em}
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
            transition:all 0.3s;
        }
      .btn-voltar:hover{background:#e2e8f0;transform:translateX(-5px)}
      .msg{
            padding:18px 24px;
            border-radius:12px;
            margin-bottom:30px;
            font-weight:600;
            display:flex;
            align-items:center;
            gap:12px;
        }
      .erro{background:#fee2e2;color:#991b1b;border:2px solid #fca5a5}
      .form-section{
            background:#f8fafc;
            padding:25px;
            border-radius:16px;
            margin-bottom:25px;
            border:2px solid #e2e8f0;
        }
      .section-title{
            font-size:1.1em;
            font-weight:700;
            color:#475569;
            margin-bottom:20px;
            display:flex;
            align-items:center;
            gap:10px;
        }
      .section-title i{color:#f59e0b}
      .form-group{margin-bottom:20px}
      .form-group label{
            display:block;
            margin-bottom:10px;
            font-weight:600;
            color:#334155;
            font-size:0.95em;
        }
      .input-wrapper{position:relative}
      .input-wrapper i{
            position:absolute;
            left:16px;
            top:50%;
            transform:translateY(-50%);
            color:#94a3b8;
            font-size:1.1em;
        }
      .form-group input, .form-group select{
            width:100%;
            padding:14px 16px 14px 48px;
            border:2px solid #e2e8f0;
            border-radius:12px;
            font-size:1em;
            transition:all 0.3s;
            background:#fff;
            font-family:inherit;
        }
      .form-group input:focus, .form-group select:focus{
            outline:none;
            border-color:#f59e0b;
            box-shadow:0 0 0 4px rgba(245,158,11,0.1);
        }
      .form-row{display:grid;grid-template-columns:1fr 1fr;gap:20px}
      .obrigatorio{color:#ef4444;margin-left:3px}
      .hint{color:#64748b;font-size:0.85em;margin-top:5px;display:block}
      .btn-salvar{
            width:100%;
            background:linear-gradient(135deg,#f59e0b,#d97706);
            color:#fff;
            padding:18px;
            border:none;
            border-radius:14px;
            font-size:1.1em;
            font-weight:700;
            cursor:pointer;
            transition:all 0.3s;
            display:flex;
            align-items:center;
            justify-content:center;
            gap:10px;
            box-shadow:0 10px 25px rgba(245,158,11,0.4);
        }
      .btn-salvar:hover{
            transform:translateY(-2px);
            box-shadow:0 15px 35px rgba(245,158,11,0.5);
        }
      .btn-salvar:active{transform:translateY(0)}
        @media(max-width:768px){
           .container{padding:30px 20px}
           .form-row{grid-template-columns:1fr}
           .header h1{font-size:1.8em}
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="listar.php" class="btn-voltar">
            <i class="fas fa-arrow-left"></i> Voltar à Lista
        </a>
        
        <div class="header">
            <div class="header-icon">
                <i class="fas fa-user-edit"></i>
            </div>
            <h1>Editar Membro</h1>
            <p>Atualize os dados de <?= htmlspecialchars($pessoa['nome']) ?></p>
        </div>

        <?php if(isset($msg)): ?>
            <div class="msg <?= $tipo ?>">
                <i class="fas fa-exclamation-circle"></i>
                <?= $msg ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-id-card"></i> Dados Pessoais
                </div>
                
                <div class="form-group">
                    <label>Nome Completo <span class="obrigatorio">*</span></label>
                    <div class="input-wrapper">
                        <i class="fas fa-user"></i>
                        <input type="text" name="nome" value="<?= htmlspecialchars($pessoa['nome']) ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Agregado Familiar <span class="obrigatorio">*</span></label>
                        <div class="input-wrapper">
                            <i class="fas fa-users"></i>
                            <input type="text" name="familia" value="<?= htmlspecialchars($pessoa['familia']) ?>" placeholder="Ex: Nhica, Mateus, Mapombe" required>
                        </div>
                        <span class="hint">Digite qualquer nome de família</span>
                    </div>

                    <div class="form-group">
                        <label>Nº do BI</label>
                        <div class="input-wrapper">
                            <i class="fas fa-address-card"></i>
                            <input type="text" name="bi" value="<?= htmlspecialchars($pessoa['bi']) ?>" maxlength="20" placeholder="Ex: 123456789A">
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Data de Nascimento <span class="obrigatorio">*</span></label>
                        <div class="input-wrapper">
                            <i class="fas fa-calendar"></i>
                            <input type="date" name="nascimento" value="<?= $pessoa['nascimento'] ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Gênero <span class="obrigatorio">*</span></label>
                        <div class="input-wrapper">
                            <i class="fas fa-venus-mars"></i>
                            <select name="genero" required>
                                <option value="M" <?= $pessoa['genero']=='M'?'selected':'' ?>>Masculino</option>
                                <option value="F" <?= $pessoa['genero']=='F'?'selected':'' ?>>Feminino</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-sitemap"></i> Vínculo Familiar
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Pai</label>
                        <div class="input-wrapper">
                            <i class="fas fa-male"></i>
                            <select name="pai_id">
                                <option value="">Não informado</option>
                                <?php foreach($pessoas as $p): if($p['genero']=='M'): ?>
                                    <option value="<?= $p['id'] ?>" <?= $p['id']==$pessoa['pai_id']?'selected':'' ?>>
                                        <?= htmlspecialchars($p['nome']) ?>
                                    </option>
                                <?php endif; endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Mãe</label>
                        <div class="input-wrapper">
                            <i class="fas fa-female"></i>
                            <select name="mae_id">
                                <option value="">Não informado</option>
                                <?php foreach($pessoas as $p): if($p['genero']=='F'): ?>
                                    <option value="<?= $p['id'] ?>" <?= $p['id']==$pessoa['mae_id']?'selected':'' ?>>
                                        <?= htmlspecialchars($p['nome']) ?>
                                    </option>
                                <?php endif; endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-salvar">
                <i class="fas fa-save"></i> Salvar Alterações
            </button>
        </form>
    </div>
</body>
</html>