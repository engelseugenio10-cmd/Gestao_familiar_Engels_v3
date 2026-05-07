<?php
include 'conexao.php';

// Estatísticas
$total_pessoas = $conn->query("SELECT COUNT(*) FROM pessoas")->fetchColumn();
$total_casais = $conn->query("SELECT COUNT(*) FROM casamentos")->fetchColumn();
$total_homens = $conn->query("SELECT COUNT(*) FROM pessoas WHERE genero = 'M'")->fetchColumn();
$total_mulheres = $conn->query("SELECT COUNT(*) FROM pessoas WHERE genero = 'F'")->fetchColumn();

// Últimos cadastrados
$ultimos = $conn->query("SELECT *, TIMESTAMPDIFF(YEAR, nascimento, CURDATE()) as idade FROM pessoas ORDER BY id DESC LIMIT 5")->fetchAll();

// Destaques
$mais_velho = $conn->query("SELECT *, TIMESTAMPDIFF(YEAR, nascimento, CURDATE()) as idade FROM pessoas ORDER BY nascimento ASC LIMIT 1")->fetch();
$mais_novo = $conn->query("SELECT *, TIMESTAMPDIFF(YEAR, nascimento, CURDATE()) as idade FROM pessoas ORDER BY nascimento DESC LIMIT 1")->fetch();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão Familiar</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { background: linear-gradient(135deg, #4a6fa5 0%, #3a5a8a 100%); color: white; padding: 25px; border-radius: 15px; text-align: center; margin-bottom: 25px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        .header h1 { font-size: 2.2em; margin-bottom: 5px; }
        .header p { opacity: 0.9; font-size: 0.95em; }
        .stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 20px; }
        .stat-card { background: white; padding: 25px; border-radius: 12px; text-align: center; box-shadow: 0 3px 10px rgba(0,0,0,0.08); }
        .stat-card i { font-size: 2.5em; margin-bottom: 10px; }
        .stat-card .numero { font-size: 2.5em; font-weight: bold; color: #333; }
        .stat-card .label { color: #666; font-size: 0.9em; margin-top: 5px; }
        .stat1 i { color: #3498db; } .stat2 i { color: #e91e63; } .stat3 i { color: #2ecc71; } .stat4 i { color: #9b59b6; }
        .botoes { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 25px; }
        .btn { background: #4a6fa5; color: white; padding: 18px; border-radius: 10px; text-decoration: none; text-align: center; font-weight: 600; transition: 0.3s; display: flex; align-items: center; justify-content: center; gap: 10px; font-size: 1em; }
        .btn:hover { background: #3a5a8a; transform: translateY(-3px); box-shadow: 0 5px 15px rgba(74,111,165,0.3); }
        .btn i { font-size: 1.2em; }
        .grid-info { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
        .box { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 3px 10px rgba(0,0,0,0.08); }
        .box-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #eee; }
        .box-header h3 { font-size: 1.2em; color: #333; }
        .btn-novo { background: #2ecc71; color: white; padding: 6px 15px; border-radius: 6px; text-decoration: none; font-size: 0.85em; font-weight: 600; }
        .btn-novo:hover { background: #27ae60; }
        .lista-pessoa { border-bottom: 1px solid #f0f0f0; padding: 12px 0; position: relative; }
        .lista-pessoa:last-child { border-bottom: none; }
        .nome { font-weight: 600; color: #333; margin-bottom: 3px; }
        .data { font-size: 0.85em; color: #999; }
        .genero-tag { position: absolute; right: 0; top: 12px; padding: 3px 10px; border-radius: 12px; font-size: 0.8em; font-weight: 600; }
        .tag-m { background: #e3f2fd; color: #1976d2; } .tag-f { background: #fce4ec; color: #c2185b; }
        .destaque-item { padding: 12px 0; border-bottom: 1px solid #f0f0f0; }
        .destaque-item:last-child { border-bottom: none; }
        .destaque-label { font-size: 0.85em; color: #666; margin-bottom: 5px; }
        .destaque-nome { font-weight: 600; color: #333; }
        .destaque-idade { font-size: 0.9em; color: #999; }
        .vazio { text-align: center; padding: 30px; color: #999; }
        @media (max-width: 968px) { .stats { grid-template-columns: 1fr 1fr; } .botoes { grid-template-columns: 1fr 1fr; } .grid-info { grid-template-columns: 1fr; } }
        @media (max-width: 600px) { .botoes { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-users"></i> Gestão Familiar</h1>
            <p>Sistema de Controle de Genealogia - Engels Eugenio Mateus</p>
        </div>

        <div class="stats">
            <div class="stat-card stat1">
                <i class="fas fa-mars"></i>
                <div class="numero"><?= $total_homens ?></div>
                <div class="label">Homens Cadastrados</div>
            </div>
            <div class="stat-card stat2">
                <i class="fas fa-venus"></i>
                <div class="numero"><?= $total_mulheres ?></div>
                <div class="label">Mulheres Cadastradas</div>
            </div>
            <div class="stat-card stat3">
                <i class="fas fa-users"></i>
                <div class="numero"><?= $total_pessoas ?></div>
                <div class="label">Total de Pessoas</div>
            </div>
            <div class="stat-card stat4">
                <i class="fas fa-heart"></i>
                <div class="numero"><?= $total_casais ?></div>
                <div class="label">Casamentos Registrados</div>
            </div>
        </div>

        <div class="botoes">
            <a href="cadastro.php" class="btn"><i class="fas fa-user-plus"></i> Cadastrar Pessoa</a>
            <a href="listar.php" class="btn"><i class="fas fa-list"></i> Listar Famílias</a>
            <a href="casamento.php" class="btn"><i class="fas fa-heart"></i> Novo Casamento</a>
            <a href="arvore.php" class="btn"><i class="fas fa-sitemap"></i> Árvore Genealógica</a>
            <a href="relatorios.php" class="btn"><i class="fas fa-chart-bar"></i> Relatórios</a>
            <a href="exportar_excel.php" class="btn"><i class="fas fa-download"></i> Exportar Dados</a>
        </div>

        <div class="grid-info">
            <div class="box">
                <div class="box-header">
                    <h3><i class="fas fa-clock"></i> Últimos Cadastrados</h3>
                    <a href="cadastro.php" class="btn-novo">+ Novo</a>
                </div>
                <?php if(empty($ultimos)): ?>
                    <div class="vazio">
                        <i class="fas fa-inbox" style="font-size: 2em; opacity: 0.3; margin-bottom: 10px;"></i>
                        <p>Nenhuma pessoa cadastrada ainda</p>
                    </div>
                <?php else: ?>
                    <?php foreach($ultimos as $p): ?>
                        <div class="lista-pessoa">
                            <span class="genero-tag tag-<?= strtolower($p['genero']) ?>">
                                <?= $p['genero'] == 'M' ? 'Masculino' : 'Feminino' ?>
                            </span>
                            <div class="nome"><?= htmlspecialchars($p['nome']) ?></div>
                            <div class="data"><?= date('d/m/Y', strtotime($p['nascimento'])) ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="box">
                <div class="box-header">
                    <h3><i class="fas fa-star"></i> Destaques</h3>
                </div>
                <?php if($mais_velho): ?>
                    <div class="destaque-item">
                        <div class="destaque-label"><i class="fas fa-arrow-up"></i> Mais Velho</div>
                        <div class="destaque-nome"><?= htmlspecialchars($mais_velho['nome']) ?></div>
                        <div class="destaque-idade"><?= $mais_velho['idade'] ?> anos</div>
                    </div>
                <?php endif; ?>
                <?php if($mais_novo): ?>
                    <div class="destaque-item">
                        <div class="destaque-label"><i class="fas fa-arrow-down"></i> Mais Novo</div>
                        <div class="destaque-nome"><?= htmlspecialchars($mais_novo['nome']) ?></div>
                        <div class="destaque-idade"><?= $mais_novo['idade'] ?> anos</div>
                    </div>
                <?php endif; ?>
                <?php if(!$mais_velho && !$mais_novo): ?>
                    <div class="vazio">Sem dados</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>