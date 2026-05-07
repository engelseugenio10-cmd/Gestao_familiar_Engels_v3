<?php
include 'conexao.php';
$pessoas = $conn->query("SELECT * FROM pessoas")->fetchAll(PDO::FETCH_ASSOC);
$casamentos = $conn->query("SELECT * FROM casamentos")->fetchAll(PDO::FETCH_ASSOC);
$porId = array_column($pessoas, null, 'id');

function box($p) {
    if(!$p) return '<div class="box vazio">?</div>';
    $nome = explode(' ', $p['nome'])[0];
    return '<div class="box">'.htmlspecialchars($nome).'</div>';
}

function getPais($id, $porId) {
    $p = $porId[$id]?? null;
    return [$porId[$p['pai_id']]?? null, $porId[$p['mae_id']]?? null];
}

function getIrmaos($id, $porId) {
    $p = $porId[$id]?? null;
    if(!$p ||!$p['pai_id'] ||!$p['mae_id']) return [];
    $irmaos = [];
    foreach($porId as $outro) {
        if($outro['id']!= $id && $outro['pai_id'] == $p['pai_id'] && $outro['mae_id'] == $p['mae_id']) {
            $irmaos[] = $outro;
        }
    }
    return $irmaos;
}

// Acha o Engels
$voce = null;
foreach($pessoas as $p) if(strpos($p['nome'], 'Engels')!== false) $voce = $p;
if(!$voce) $voce = end($pessoas);

list($pai, $mae) = getPais($voce['id'], $porId);
$irmaos = getIrmaos($voce['id'], $porId);

// AVÓS + GAMBIARRA DO MOXCHENA
list($avo_pai, $avoh_pai) = $pai? getPais($pai['id'], $porId) : [null, null];
list($avo_mae, $avoh_mae) = $mae? getPais($mae['id'], $porId) : [null, null];

// FORÇA MOXCHENA SE A MARIA TIVER SOZINHA
if($avoh_pai && str_contains($avoh_pai['nome'], 'Maria') &&!$avo_pai) {
    $avo_pai = ['id' => 999, 'nome' => 'MOiaCHENA MATEUS MAPOMBE', 'genero' => 'M'];
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Árvore Genealógica Completa</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            background: #fff url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 600"><path fill="%23e8f5e9" d="M400 100c-50 0-100 30-120 80s-20 120 0 150 70 50 120 50 100-20 120-50 20-100 0-150-70-80-120-80z"/></svg>') no-repeat center;
            background-size: 600px;
            padding: 40px 20px;
        }
     .header { text-align: center; margin-bottom: 40px; }
     .header h1 { color: #2d6a4f; font-size: 2em; }
     .btn-voltar { display: inline-block; background: #2d6a4f; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; margin-top: 10px; }
     .arvore { display: flex; flex-direction: column; align-items: center; gap: 40px; }
     .nivel { display: flex; gap: 50px; justify-content: center; position: relative; }
     .box {
            background: white;
            border: 3px solid #000;
            border-radius: 25px;
            padding: 12px 25px;
            font-weight: 700;
            font-size: 0.9em;
            text-transform: uppercase;
            min-width: 120px;
            text-align: center;
            position: relative;
            z-index: 2;
        }
     .box.vazio { border-style: dashed; color: #ccc; }
     .casal { position: relative; display: flex; gap: 20px; }
     .casal::after {
            content: '';
            position: absolute;
            bottom: -20px;
            left: 50%;
            width: 3px;
            height: 20px;
            border-left: 3px dashed #000;
            transform: translateX(-50%);
        }
     .filhos { position: relative; display: flex; gap: 30px; padding-top: 40px; }
     .filhos::before {
            content: '';
            position: absolute;
            top: 0;
            left: 10%;
            right: 10%;
            height: 3px;
            border-top: 3px dashed #000;
        }
     .filho { position: relative; }
     .filho::before {
            content: '';
            position: absolute;
            top: -40px;
            left: 50%;
            width: 3px;
            height: 40px;
            border-left: 3px dashed #000;
            transform: translateX(-50%);
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>ÁRVORE GENEALÓGICA</h1>
        <a href="index.php" class="btn-voltar">← Voltar</a>
    </div>

    <div class="arvore">
        <!-- AVÓS -->
        <div class="nivel">
            <?php if($avo_pai || $avoh_pai):?>
            <div class="casal"><?=box($avo_pai)?><?=box($avoh_pai)?></div>
            <?php endif;?>
            <?php if($avo_mae || $avoh_mae):?>
            <div class="casal"><?=box($avo_mae)?><?=box($avoh_mae)?></div>
            <?php endif;?>
        </div>

        <!-- PAIS -->
        <div class="nivel">
            <?php if($pai || $mae):?>
            <div class="casal"><?=box($pai)?><?=box($mae)?></div>
            <?php endif;?>
        </div>

        <!-- VOCÊ + IRMÃOS -->
        <div class="nivel">
            <div class="filhos">
                <?php foreach(array_merge([$voce], $irmaos) as $filho):?>
                <div class="filho"><?=box($filho)?></div>
                <?php endforeach;?>
            </div>
        </div>
    </div>
</body>
</html>