<?php
include 'conexao.php';

// 1. TOTAL DE PESSOAS
$sql_total = "SELECT COUNT(*) as total FROM pessoas";
$total_pessoas = $conn->query($sql_total)->fetch_assoc()['total'];

// 2. CONTAGEM POR SEXO
$sql_sexo = "SELECT sexo, COUNT(*) as qtd FROM pessoas GROUP BY sexo";
$result_sexo = $conn->query($sql_sexo);
$dados_sexo = [];
while($row = $result_sexo->fetch_assoc()){
    $dados_sexo[$row['sexo']] = $row['qtd'];
}
$homens = $dados_sexo['M'] ?? 0;
$mulheres = $dados_sexo['F'] ?? 0;

// 3. CASAMENTOS POR ANO
$sql_casamentos = "SELECT YEAR(data_casamento) as ano, COUNT(*) as qtd 
                   FROM casamentos 
                   GROUP BY YEAR(data_casamento) 
                   ORDER BY ano ASC";
$result_casamentos = $conn->query($sql_casamentos);

// 4. MÉDIA DE IDADE
$sql_idade = "SELECT AVG(TIMESTAMPDIFF(YEAR, data_nascimento, CURDATE())) as media_idade FROM pessoas";
$media_idade = round($conn->query($sql_idade)->fetch_assoc()['media_idade'], 1);

// 5. FAMÍLIA MAIS NUMEROSA
$sql_familia = "SELECT apelido_familiar, COUNT(*) as qtd 
                FROM pessoas 
                WHERE apelido_familiar IS NOT NULL AND apelido_familiar != '' 
                GROUP BY apelido_familiar 
                ORDER BY qtd DESC LIMIT 1";
$familia_top = $conn->query($sql_familia)->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Estatísticas - Gestão Familiar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        google.charts.load('current', {'packages':['corechart', 'bar']});
        google.charts.setOnLoadCallback(drawCharts);

        function drawCharts() {
            // GRÁFICO PIZZA - SEXO
            var dataSexo = google.visualization.arrayToDataTable([
                ['Sexo', 'Quantidade'],
                ['Masculino', <?= $homens ?>],
                ['Feminino', <?= $mulheres ?>]
            ]);
            var optionsSexo = {
                title: 'Distribuição por Sexo',
                colors: ['#0d6efd', '#dc3545'],
                is3D: true
            };
            var chartSexo = new google.visualization.PieChart(document.getElementById('chart_sexo'));
            chartSexo.draw(dataSexo, optionsSexo);

            // GRÁFICO BARRAS - CASAMENTOS POR ANO
            var dataCasamentos = google.visualization.arrayToDataTable([
                ['Ano', 'Casamentos'],
                <?php 
                if($result_casamentos->num_rows > 0){
                    while($row = $result_casamentos->fetch_assoc()){
                        echo "['".$row['ano']."', ".$row['qtd']."],";
                    }
                } else {
                    echo "['Sem dados', 0]";
                }
                ?>
            ]);
            var optionsCasamentos = {
                title: 'Casamentos por Ano',
                colors: ['#198754'],
                legend: { position: 'none' }
            };
            var chartCasamentos = new google.visualization.ColumnChart(document.getElementById('chart_casamentos'));
            chartCasamentos.draw(dataCasamentos, optionsCasamentos);
        }
    </script>
</head>
<body class="container mt-4">
    <h2 class="text-center mb-4">Painel de Estatísticas - BI</h2>
    <a href="index.php" class="btn btn-secondary mb-4">← Voltar ao Menu</a>

    <!-- CARDS COM NÚMEROS -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-bg-primary mb-3">
                <div class="card-body text-center">
                    <h5 class="card-title">Total de Pessoas</h5>
                    <h2><?= $total_pessoas ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-bg-success mb-3">
                <div class="card-body text-center">
                    <h5 class="card-title">Média de Idade</h5>
                    <h2><?= $media_idade ?> anos</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-bg-warning mb-3">
                <div class="card-body text-center">
                    <h5 class="card-title">Homens</h5>
                    <h2><?= $homens ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-bg-danger mb-3">
                <div class="card-body text-center">
                    <h5 class="card-title">Mulheres</h5>
                    <h2><?= $mulheres ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- GRÁFICOS -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <div id="chart_sexo" style="width: 100%; height: 400px;"></div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <div id="chart_casamentos" style="width: 100%; height: 400px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- FAMÍLIA MAIS NUMEROSA -->
    <?php if($familia_top){ ?>
    <div class="row mt-4">
        <div class="col-12">
            <div class="alert alert-info text-center">
                <h4>Família mais numerosa: <?= htmlspecialchars($familia_top['apelido_familiar']) ?></h4>
                <p>Com <?= $familia_top['qtd'] ?> membros cadastrados</p>
            </div>
        </div>
    </div>
    <?php } ?>

</body>
</html>
<?php $conn->close(); ?>