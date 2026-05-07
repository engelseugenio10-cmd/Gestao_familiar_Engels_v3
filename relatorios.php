<?php
include 'conexao.php';

// BUSCA DADOS
$pessoas = $conn->query("SELECT * FROM pessoas WHERE nascimento IS NOT NULL AND nascimento != '0000-00-00'")->fetchAll(PDO::FETCH_ASSOC);

// 1. PIRÂMIDE ETÁRIA
$faixas = ['Crianças' => 0, 'Jovens' => 0, 'Adultos' => 0, 'Idosos' => 0];
$faixasM = ['Crianças' => 0, 'Jovens' => 0, 'Adultos' => 0, 'Idosos' => 0];
$faixasF = ['Crianças' => 0, 'Jovens' => 0, 'Adultos' => 0, 'Idosos' => 0];
$total = count($pessoas);

foreach($pessoas as $p) {
    $idade = (new DateTime())->diff(new DateTime($p['nascimento']))->y;
    $gen = $p['genero'];
    
    if($idade < 12) {
        $faixas['Crianças']++;
        $gen == 'M' ? $faixasM['Crianças']++ : $faixasF['Crianças']++;
    } elseif($idade < 18) {
        $faixas['Jovens']++;
        $gen == 'M' ? $faixasM['Jovens']++ : $faixasF['Jovens']++;
    } elseif($idade < 65) {
        $faixas['Adultos']++;
        $gen == 'M' ? $faixasM['Adultos']++ : $faixasF['Adultos']++;
    } else {
        $faixas['Idosos']++;
        $gen == 'M' ? $faixasM['Idosos']++ : $faixasF['Idosos']++;
    }
}

// 2. TAXA DE DEPENDÊNCIA
$dependentes = $faixas['Crianças'] + $faixas['Jovens'] + $faixas['Idosos'];
$ativos = $faixas['Adultos'];
$taxaDependencia = $ativos > 0 ? round(($dependentes / $ativos) * 100, 1) : 0;

// 3. MÉDIA DE FILHOS
$casais = $conn->query("
    SELECT LEAST(pai_id, mae_id) as pai, GREATEST(pai_id, mae_id) as mae, COUNT(*) as filhos
    FROM pessoas WHERE pai_id IS NOT NULL AND mae_id IS NOT NULL 
    GROUP BY pai, mae
")->fetchAll(PDO::FETCH_ASSOC);

$totalCasais = count($casais);
$totalFilhos = array_sum(array_column($casais, 'filhos'));
$mediaFilhos = $totalCasais > 0 ? round($totalFilhos / $totalCasais, 2) : 0;

// 4. CONSANGUINIDADE
$consanguineos = [];
foreach($casais as $casal) {
    $sql = "SELECT 
                p1.nome as nome_pai, p1.pai_id as ap1, p1.mae_id as am1,
                p2.nome as nome_mae, p2.pai_id as ap2, p2.mae_id as am2
            FROM pessoas p1, pessoas p2 
            WHERE p1.id = ? AND p2.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$casal['pai'], $casal['mae']]);
    $d = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($d) {
        $comuns = array_intersect(
            array_filter([$d['ap1'], $d['am1']]),
            array_filter([$d['ap2'], $d['am2']])
        );
        if(!empty($comuns)) {
            $consanguineos[] = ['pai' => $d['nome_pai'], 'mae' => $d['nome_mae']];
        }
    }
}

// ESTATÍSTICAS GERAIS
$totalM = count(array_filter($pessoas, fn($p) => $p['genero'] == 'M'));
$totalF = count(array_filter($pessoas, fn($p) => $p['genero'] == 'F'));
$familias = array_filter(array_unique(array_column($pessoas, 'familia')));
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard BI | Gestão Familiar</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{
            font-family:'Inter','Segoe UI',sans-serif;
            background:#0f172a;
            min-height:100vh;
            padding:20px;
            color:#e2e8f0;
        }
      .container{max-width:1600px;margin:0 auto}
      .header{
            background:linear-gradient(135deg,#1e293b 0%,#0f172a 100%);
            padding:40px;
            border-radius:24px;
            margin-bottom:30px;
            border:1px solid #334155;
            box-shadow:0 20px 60px rgba(0,0,0,0.5);
        }
      .header-content{
            display:flex;
            justify-content:space-between;
            align-items:center;
            flex-wrap:wrap;
            gap:30px;
        }
      .header-left{display:flex;align-items:center;gap:25px}
      .header-icon{
            width:80px;
            height:80px;
            background:linear-gradient(135deg,#3b82f6,#8b5cf6);
            border-radius:20px;
            display:flex;
            align-items:center;
            justify-content:center;
            box-shadow:0 10px 30px rgba(59,130,246,0.5);
        }
      .header-icon i{color:#fff;font-size:2.5em}
      .header-text h1{
            font-size:2.5em;
            font-weight:900;
            background:linear-gradient(135deg,#3b82f6,#8b5cf6);
            -webkit-background-clip:text;
            -webkit-text-fill-color:transparent;
            margin-bottom:8px;
        }
      .header-text p{color:#94a3b8;font-size:1.1em}
      .btn{
            padding:16px 32px;
            border-radius:14px;
            text-decoration:none;
            font-weight:700;
            display:inline-flex;
            align-items:center;
            gap:10px;
            transition:all 0.3s;
            background:#1e293b;
            color:#e2e8f0;
            border:2px solid #334155;
        }
      .btn:hover{
            background:#334155;
            transform:translateY(-2px);
            box-shadow:0 10px 25px rgba(59,130,246,0.3);
        }
      .stats-grid{
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(280px,1fr));
            gap:25px;
            margin-bottom:30px;
        }
      .stat-card{
            background:linear-gradient(135deg,#1e293b 0%,#0f172a 100%);
            padding:30px;
            border-radius:20px;
            border:1px solid #334155;
            position:relative;
            overflow:hidden;
            transition:all 0.3s;
        }
      .stat-card:hover{
            transform:translateY(-5px);
            box-shadow:0 20px 40px rgba(59,130,246,0.3);
            border-color:#3b82f6;
        }
      .stat-card::before{
            content:'';
            position:absolute;
            top:0;
            right:0;
            width:100px;
            height:100px;
            background:radial-gradient(circle,#3b82f6,transparent);
            opacity:0.1;
        }
      .stat-icon{
            width:60px;
            height:60px;
            border-radius:16px;
            display:flex;
            align-items:center;
            justify-content:center;
            margin-bottom:20px;
            font-size:1.8em;
        }
      .stat-icon.blue{background:linear-gradient(135deg,#3b82f6,#2563eb);box-shadow:0 8px 20px rgba(59,130,246,0.4)}
      .stat-icon.green{background:linear-gradient(135deg,#10b981,#059669);box-shadow:0 8px 20px rgba(16,185,129,0.4)}
      .stat-icon.purple{background:linear-gradient(135deg,#8b5cf6,#7c3aed);box-shadow:0 8px 20px rgba(139,92,246,0.4)}
      .stat-icon.orange{background:linear-gradient(135deg,#f59e0b,#d97706);box-shadow:0 8px 20px rgba(245,158,11,0.4)}
      .stat-value{
            font-size:3em;
            font-weight:900;
            color:#fff;
            margin-bottom:8px;
        }
      .stat-label{
            color:#94a3b8;
            font-size:1em;
            font-weight:600;
            margin-bottom:15px;
        }
      .stat-badge{
            padding:8px 16px;
            border-radius:20px;
            font-size:0.85em;
            font-weight:700;
            display:inline-block;
        }
      .badge-success{background:rgba(16,185,129,0.2);color:#10b981;border:1px solid #10b981}
      .badge-warning{background:rgba(245,158,11,0.2);color:#f59e0b;border:1px solid #f59e0b}
      .badge-danger{background:rgba(239,68,68,0.2);color:#ef4444;border:1px solid #ef4444}
      .chart-section{
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:25px;
            margin-bottom:30px;
        }
      .chart-card{
            background:linear-gradient(135deg,#1e293b 0%,#0f172a 100%);
            padding:35px;
            border-radius:20px;
            border:1px solid #334155;
        }
      .chart-card.full{grid-column:1/-1}
      .chart-title{
            font-size:1.5em;
            font-weight:800;
            color:#fff;
            margin-bottom:25px;
            display:flex;
            align-items:center;
            gap:12px;
        }
      .chart-title i{color:#3b82f6}
      .chart-container{position:relative;height:350px}
      .info-grid{
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
            gap:15px;
            margin-top:25px;
        }
      .info-box{
            background:#0f172a;
            padding:20px;
            border-radius:14px;
            border:1px solid #334155;
            text-align:center;
        }
      .info-box-value{
            font-size:2em;
            font-weight:900;
            color:#3b82f6;
            margin-bottom:5px;
        }
      .info-box-label{
            color:#94a3b8;
            font-size:0.9em;
            font-weight:600;
        }
      .consanguinidade-list{
            max-height:300px;
            overflow-y:auto;
        }
      .consanguinidade-item{
            background:rgba(239,68,68,0.1);
            border:1px solid #ef4444;
            border-left:4px solid #ef4444;
            padding:18px;
            border-radius:12px;
            margin-bottom:12px;
            display:flex;
            align-items:center;
            gap:12px;
        }
      .consanguinidade-item i{color:#ef4444;font-size:1.3em}
      .empty-state{
            text-align:center;
            padding:60px 20px;
            color:#64748b;
        }
      .empty-state i{
            font-size:4em;
            color:#10b981;
            margin-bottom:20px;
        }
      .empty-state h3{
            font-size:1.5em;
            margin-bottom:10px;
            color:#e2e8f0;
        }
        @media(max-width:1024px){
           .chart-section{grid-template-columns:1fr}
        }
        @media(max-width:768px){
           .header{padding:30px 20px}
           .header-content{flex-direction:column;text-align:center}
           .stats-grid{grid-template-columns:1fr}
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-content">
                <div class="header-left">
                    <div class="header-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="header-text">
                        <h1>Dashboard BI</h1>
                        <p>Análise Demográfica e Estatística Completa</p>
                    </div>
                </div>
                <a href="index.php" class="btn">
                    <i class="fas fa-arrow-left"></i> Voltar ao Painel
                </a>
            </div>
        </div>

        <!-- CARDS DE ESTATÍSTICAS -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-value"><?= $total ?></div>
                <div class="stat-label">Total de Membros</div>
                <span class="stat-badge badge-success"><?= $totalM ?>H / <?= $totalF ?>M</span>
            </div>

            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-balance-scale"></i>
                </div>
                <div class="stat-value"><?= $taxaDependencia ?>%</div>
                <div class="stat-label">Taxa de Dependência</div>
                <?php if($taxaDependencia < 50): ?>
                    <span class="stat-badge badge-success">Baixa</span>
                <?php elseif($taxaDependencia < 100): ?>
                    <span class="stat-badge badge-warning">Moderada</span>
                <?php else: ?>
                    <span class="stat-badge badge-danger">Alta</span>
                <?php endif; ?>
            </div>

            <div class="stat-card">
                <div class="stat-icon purple">
                    <i class="fas fa-baby"></i>
                </div>
                <div class="stat-value"><?= $mediaFilhos ?></div>
                <div class="stat-label">Média Filhos/Casal</div>
                <span class="stat-badge badge-success"><?= $totalCasais ?> casais</span>
            </div>

            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fas fa-home"></i>
                </div>
                <div class="stat-value"><?= count($familias) ?></div>
                <div class="stat-label">Agregados Familiares</div>
                <span class="stat-badge badge-success">Ativos</span>
            </div>
        </div>

        <!-- GRÁFICOS -->
        <div class="chart-section">
            <div class="chart-card">
                <div class="chart-title">
                    <i class="fas fa-chart-bar"></i> Pirâmide Etária Geral
                </div>
                <div class="chart-container">
                    <canvas id="piramideChart"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <div class="chart-title">
                    <i class="fas fa-venus-mars"></i> Distribuição por Gênero
                </div>
                <div class="chart-container">
                    <canvas id="generoChart"></canvas>
                </div>
            </div>
        </div>

        <!-- DISTRIBUIÇÃO DETALHADA -->
        <div class="chart-card full">
            <div class="chart-title">
                <i class="fas fa-chart-pie"></i> Distribuição Detalhada por Faixa Etária
            </div>
            <div class="info-grid">
                <?php foreach($faixas as $faixa => $qtd): 
                    $perc = $total > 0 ? round(($qtd/$total)*100,1) : 0;
                ?>
                <div class="info-box">
                    <div class="info-box-value"><?= $qtd ?></div>
                    <div class="info-box-label"><?= $faixa ?><br>(<?= $perc ?>%)</div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- CONSANGUINIDADE -->
        <div class="chart-card full">
            <div class="chart-title">
                <i class="fas fa-exclamation-triangle"></i> Análise de Consanguinidade
            </div>
            <?php if(empty($consanguineos)): ?>
                <div class="empty-state">
                    <i class="fas fa-check-circle"></i>
                    <h3>Nenhuma união consanguínea detectada</h3>
                    <p>Todos os casais cadastrados não possuem ascendência comum próxima</p>
                </div>
            <?php else: ?>
                <div class="stat-badge badge-danger" style="margin-bottom:20px">
                    <i class="fas fa-exclamation-circle"></i> <?= count($consanguineos) ?> união(ões) detectada(s)
                </div>
                <div class="consanguinidade-list">
                    <?php foreach($consanguineos as $c): ?>
                        <div class="consanguinidade-item">
                            <i class="fas fa-users"></i>
                            <div>
                                <strong><?= htmlspecialchars($c['pai']) ?></strong> & <strong><?= htmlspecialchars($c['mae']) ?></strong>
                                <div style="color:#94a3b8;font-size:0.9em;margin-top:3px">Possuem ancestrais em comum</div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // GRÁFICO PIRÂMIDE ETÁRIA
        const ctx1 = document.getElementById('piramideChart');
        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: ['Crianças (0-11)', 'Jovens (12-17)', 'Adultos (18-64)', 'Idosos (65+)'],
                datasets: [{
                    label: 'Quantidade',
                    data: [<?= $faixas['Crianças'] ?>, <?= $faixas['Jovens'] ?>, <?= $faixas['Adultos'] ?>, <?= $faixas['Idosos'] ?>],
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(139, 92, 246, 0.8)',
                        'rgba(245, 158, 11, 0.8)'
                    ],
                    borderColor: [
                        'rgba(59, 130, 246, 1)',
                        'rgba(16, 185, 129, 1)',
                        'rgba(139, 92, 246, 1)',
                        'rgba(245, 158, 11, 1)'
                    ],
                    borderWidth: 2,
                    borderRadius: 12
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#fff',
                        bodyColor: '#e2e8f0',
                        borderColor: '#334155',
                        borderWidth: 1,
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                let total = <?= $total ?>;
                                let perc = ((context.parsed.y / total) * 100).toFixed(1);
                                return context.parsed.y + ' pessoas (' + perc + '%)';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, color: '#94a3b8' },
                        grid: { color: '#334155' }
                    },
                    x: {
                        ticks: { color: '#94a3b8' },
                        grid: { display: false }
                    }
                }
            }
        });

        // GRÁFICO GÊNERO
        const ctx2 = document.getElementById('generoChart');
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['Masculino', 'Feminino'],
                datasets: [{
                    data: [<?= $totalM ?>, <?= $totalF ?>],
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(236, 72, 153, 0.8)'
                    ],
                    borderColor: [
                        'rgba(59, 130, 246, 1)',
                        'rgba(236, 72, 153, 1)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: '#e2e8f0', padding: 20, font: { size: 14 } }
                    },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#fff',
                        bodyColor: '#e2e8f0',
                        borderColor: '#334155',
                        borderWidth: 1,
                        padding: 12
                    }
                }
            }
        });
    </script>
</body>
</html>