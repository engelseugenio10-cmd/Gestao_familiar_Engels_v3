<?php
include 'conexao.php';

$sql = "SELECT 
            p.id, 
            p.nome, 
            p.familia,
            p.bi,
            p.nascimento, 
            p.genero,
            pai.nome as nome_pai,
            mae.nome as nome_mae
        FROM pessoas p
        LEFT JOIN pessoas pai ON p.pai_id = pai.id
        LEFT JOIN pessoas mae ON p.mae_id = mae.id
        ORDER BY p.id ASC"; // AGORA ORDENA POR ID

$pessoas = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);

function idade($n) { 
    return empty($n)||$n=='0000-00-00'?'-':(new DateTime())->diff(new DateTime($n))->y.' anos'; 
}

$total = count($pessoas);
$familias = array_filter(array_unique(array_column($pessoas, 'familia')));
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listar Membros | Gestão Familiar</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{
            font-family:'Inter','Segoe UI',sans-serif;
            background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);
            min-height:100vh;
            padding:20px;
        }
      .container{max-width:1400px;margin:0 auto}
      .header{
            background:rgba(255,255,255,0.95);
            backdrop-filter:blur(20px);
            padding:30px 40px;
            border-radius:20px;
            margin-bottom:25px;
            box-shadow:0 10px 30px rgba(0,0,0,0.15);
            display:flex;
            justify-content:space-between;
            align-items:center;
            flex-wrap:wrap;
            gap:20px;
        }
      .header-left{display:flex;align-items:center;gap:20px}
      .header-icon{
            width:70px;
            height:70px;
            background:linear-gradient(135deg,#667eea,#764ba2);
            border-radius:16px;
            display:flex;
            align-items:center;
            justify-content:center;
            box-shadow:0 8px 20px rgba(102,126,234,0.4);
        }
      .header-icon i{color:#fff;font-size:2em}
      .header-text h1{color:#1a1a2e;font-size:2em;font-weight:800;margin-bottom:5px}
      .header-text p{color:#64748b;font-size:1.05em}
      .header-stats{display:flex;gap:20px}
      .stat-card{
            background:linear-gradient(135deg,#667eea,#764ba2);
            padding:15px 25px;
            border-radius:14px;
            color:#fff;
            text-align:center;
            box-shadow:0 5px 15px rgba(102,126,234,0.3);
        }
      .stat-card .num{font-size:2em;font-weight:800;display:block}
      .stat-card .label{font-size:0.85em;opacity:0.9}
      .actions{display:flex;gap:12px;flex-wrap:wrap}
      .btn{
            padding:14px 28px;
            border-radius:12px;
            text-decoration:none;
            font-weight:700;
            display:inline-flex;
            align-items:center;
            gap:8px;
            transition:all 0.3s;
            border:none;
            cursor:pointer;
            font-size:1em;
        }
      .btn-primary{
            background:linear-gradient(135deg,#667eea,#764ba2);
            color:#fff;
            box-shadow:0 5px 15px rgba(102,126,234,0.4);
        }
      .btn-primary:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(102,126,234,0.5)}
      .btn-success{background:#10b981;color:#fff;box-shadow:0 5px 15px rgba(16,185,129,0.3)}
      .btn-success:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(16,185,129,0.4)}
      .btn-secondary{background:#f1f5f9;color:#475569}
      .btn-secondary:hover{background:#e2e8f0}
      .card{
            background:rgba(255,255,255,0.95);
            backdrop-filter:blur(20px);
            border-radius:20px;
            padding:30px;
            box-shadow:0 10px 30px rgba(0,0,0,0.15);
        }
      .search-box{margin-bottom:25px;position:relative}
      .search-box i{
            position:absolute;
            left:18px;
            top:50%;
            transform:translateY(-50%);
            color:#94a3b8;
            font-size:1.2em;
        }
      .search-box input{
            width:100%;
            padding:16px 16px 16px 50px;
            border:2px solid #e2e8f0;
            border-radius:14px;
            font-size:1em;
            transition:all 0.3s;
        }
      .search-box input:focus{
            outline:none;
            border-color:#667eea;
            box-shadow:0 0 0 4px rgba(102,126,234,0.1);
        }
      .table-wrapper{overflow-x:auto;border-radius:14px}
        table{width:100%;border-collapse:collapse;min-width:1000px}
        thead{background:linear-gradient(135deg,#667eea,#764ba2)}
        th{
            color:#fff;
            padding:18px 15px;
            text-align:left;
            font-weight:700;
            font-size:0.9em;
            text-transform:uppercase;
            letter-spacing:0.5px;
        }
        tbody tr{
            border-bottom:1px solid #e2e8f0;
            transition:background 0.2s; /* TIREI O SCALE DAQUI */
        }
        tbody tr:hover{background:#f8fafc} /* SEM ZOOM AGORA */
        td{padding:18px 15px;color:#334155}
      .nome-cell{font-weight:700;color:#1a1a2e;font-size:1.05em}
      .badge{
            padding:6px 14px;
            border-radius:20px;
            font-size:0.85em;
            font-weight:700;
            display:inline-flex;
            align-items:center;
            gap:5px;
        }
      .badge-familia{background:#e0e7ff;color:#4338ca}
      .badge-m{background:#dbeafe;color:#1e40af}
      .badge-f{background:#fce7f3;color:#9f1239}
      .badge-bi{background:#f1f5f9;color:#475569;font-family:monospace}
      .vazio{color:#94a3b8;font-style:italic}
      .acoes{display:flex;gap:8px}
      .btn-acao{
            width:38px;
            height:38px;
            border-radius:10px;
            display:flex;
            align-items:center;
            justify-content:center;
            text-decoration:none;
            transition:all 0.3s;
            border:none;
            cursor:pointer;
            font-size:1.1em;
        }
      .btn-edit{background:#fef3c7;color:#92400e}
      .btn-edit:hover{background:#fde68a}
      .btn-delete{background:#fee2e2;color:#991b1b}
      .btn-delete:hover{background:#fecaca}
        @media(max-width:768px){
           .header{padding:25px 20px}
           .header-left{flex-direction:column;text-align:center}
           .header-stats{width:100%;justify-content:center}
           .card{padding:20px 15px}
           .actions{width:100%}
           .btn{flex:1;justify-content:center}
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-left">
                <div class="header-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="header-text">
                    <h1>Agregados Familiares</h1>
                    <p>Gerencie todos os membros da família</p>
                </div>
            </div>
            
            <div class="header-stats">
                <div class="stat-card">
                    <span class="num"><?= $total ?></span>
                    <span class="label">Membros</span>
                </div>
                <div class="stat-card">
                    <span class="num"><?= count($familias) ?></span>
                    <span class="label">Famílias</span>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="actions" style="margin-bottom:25px;">
                <a href="cadastrar.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Novo Membro
                </a>
                <a href="exportar.php" class="btn btn-success">
                    <i class="fas fa-file-csv"></i> Exportar CSV
                </a>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-home"></i> Painel
                </a>
            </div>

            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="busca" placeholder="Buscar por nome, família ou BI...">
            </div>

            <div class="table-wrapper">
                <table id="tabela">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome Completo</th>
                            <th>Família</th>
                            <th>BI</th>
                            <th>Idade</th>
                            <th>Gênero</th>
                            <th>Pai</th>
                            <th>Mãe</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($pessoas as $p): ?>
                        <tr>
                            <td><strong><?= $p['id'] ?></strong></td>
                            <td class="nome-cell"><?= htmlspecialchars($p['nome']) ?></td>
                            <td>
                                <?php if($p['familia']): ?>
                                    <span class="badge badge-familia">
                                        <i class="fas fa-users"></i> <?= htmlspecialchars($p['familia']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="vazio">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($p['bi']): ?>
                                    <span class="badge badge-bi"><?= htmlspecialchars($p['bi']) ?></span>
                                <?php else: ?>
                                    <span class="vazio">-</span>
                                <?php endif; ?>
                            </td>
                            <td><?= idade($p['nascimento']) ?></td>
                            <td>
                                <?php if($p['genero'] == 'M'): ?>
                                    <span class="badge badge-m"><i class="fas fa-mars"></i> M</span>
                                <?php else: ?>
                                    <span class="badge badge-f"><i class="fas fa-venus"></i> F</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $p['nome_pai']? htmlspecialchars($p['nome_pai']) : '<span class="vazio">-</span>' ?></td>
                            <td><?= $p['nome_mae']? htmlspecialchars($p['nome_mae']) : '<span class="vazio">-</span>' ?></td>
                            <td>
                                <div class="acoes">
                                    <a href="editar.php?id=<?= $p['id'] ?>" class="btn-acao btn-edit" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="excluir.php?id=<?= $p['id'] ?>" class="btn-acao btn-delete" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir <?= htmlspecialchars($p['nome']) ?>?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('busca').addEventListener('keyup', function() {
            let filtro = this.value.toLowerCase();
            let linhas = document.querySelectorAll('#tabela tbody tr');
            
            linhas.forEach(function(linha) {
                let texto = linha.textContent.toLowerCase();
                linha.style.display = texto.includes(filtro)? '' : 'none';
            });
        });
    </script>
</body>
</html>