<?php
setlocale(LC_TIME,'pt_BR','pt_BR.utf-8','pt_BR.utf-8','portuguese');
date_default_timezone_set('America/Sao_Paulo');
require_once 'verificar_login.php';
require_once 'conexao.php';

$periodo = isset($_GET['periodo']) ? $_GET['periodo'] : 'mensal';
$ano = isset($_GET['ano']) ? (int)$_GET['ano'] : date('Y');
$mes = isset($_GET['mes']) ? (int)$_GET['mes'] : date('m');

try {
    $sql_anos = "SELECT DISTINCT YEAR(data_criacao) as ano FROM manifestacoes ORDER BY ano DESC";
    $stmt_anos = $pdo->prepare($sql_anos);
    $stmt_anos->execute();
    $anos_disponiveis = $stmt_anos->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) { $anos_disponiveis = [date('Y')]; }

$dados_por_tipo=[]; $dados_por_area=[]; $dados_por_status=[]; $dados_por_tempo=[];

try {
    if ($periodo === 'mensal') {
        $filter=[':ano'=>$ano,':mes'=>$mes];
        $wh="WHERE YEAR(data_criacao)=:ano AND MONTH(data_criacao)=:mes";
        $group_tempo="DAY(data_criacao) as dia";
    } else {
        $filter=[':ano'=>$ano];
        $wh="WHERE YEAR(data_criacao)=:ano";
        $group_tempo="MONTH(data_criacao) as dia";
    }
    $run=function($sql,$f) use($pdo){ $s=$pdo->prepare($sql); foreach($f as $k=>$v) $s->bindValue($k,$v); $s->execute(); return $s; };
    $dados_por_tipo=$run("SELECT tipo_manifestacao,COUNT(*) as total FROM manifestacoes $wh GROUP BY tipo_manifestacao",$filter)->fetchAll(PDO::FETCH_KEY_PAIR);
    $dados_por_area=$run("SELECT area_relacionada,COUNT(*) as total FROM manifestacoes $wh GROUP BY area_relacionada",$filter)->fetchAll(PDO::FETCH_KEY_PAIR);
    $dados_por_status=$run("SELECT status,COUNT(*) as total FROM manifestacoes $wh GROUP BY status",$filter)->fetchAll(PDO::FETCH_KEY_PAIR);
    $dados_por_tempo=$run("SELECT $group_tempo,COUNT(*) as total FROM manifestacoes $wh GROUP BY dia ORDER BY dia",$filter)->fetchAll(PDO::FETCH_KEY_PAIR);
    $total_manifestacoes=$run("SELECT COUNT(*) as total FROM manifestacoes $wh",$filter)->fetch()['total'];
} catch (PDOException $e) { $erro="Erro: ".$e->getMessage(); $total_manifestacoes=0; }

// Build chart data
$labels_tipo_pt=['sugestao'=>'Sugestão','critica'=>'Crítica','elogio'=>'Elogio','reclamacao'=>'Reclamação'];
$labels_tipo=[]; $data_tipo=[];
foreach($labels_tipo_pt as $k=>$v){ $labels_tipo[]=$v; $data_tipo[]=$dados_por_tipo[$k]??0; }

$labels_status_pt=['pendente'=>'Pendente','em_analise'=>'Em Análise','respondida'=>'Respondida','arquivada'=>'Arquivada'];
$labels_status=[]; $data_status=[];
foreach($labels_status_pt as $k=>$v){ $labels_status[]=$v; $data_status[]=$dados_por_status[$k]??0; }

$labels_area=[]; $data_area=[];
foreach($dados_por_area as $a=>$v){ $labels_area[]=ucfirst($a); $data_area[]=$v; }

$labels_tempo=[]; $data_tempo=[];
if($periodo==='mensal'){
    $dias=cal_days_in_month(CAL_GREGORIAN,$mes,$ano);
    for($d=1;$d<=$dias;$d++){ $labels_tempo[]=$d; $data_tempo[]=$dados_por_tempo[$d]??0; }
} else {
    $mn=[1=>'Jan',2=>'Fev',3=>'Mar',4=>'Abr',5=>'Mai',6=>'Jun',7=>'Jul',8=>'Ago',9=>'Set',10=>'Out',11=>'Nov',12=>'Dez'];
    for($m=1;$m<=12;$m++){ $labels_tempo[]=$mn[$m]; $data_tempo[]=$dados_por_tempo[$m]??0; }
}

$chart_data_json = json_encode(['tipo'=>['labels'=>$labels_tipo,'data'=>$data_tipo],'status'=>['labels'=>$labels_status,'data'=>$data_status],'area'=>['labels'=>$labels_area,'data'=>$data_area],'tempo'=>['labels'=>$labels_tempo,'data'=>$data_tempo]]);
$meses_pt=[1=>'Janeiro',2=>'Fevereiro',3=>'Março',4=>'Abril',5=>'Maio',6=>'Junho',7=>'Julho',8=>'Agosto',9=>'Setembro',10=>'Outubro',11=>'Novembro',12=>'Dezembro'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Relatórios — Ouvidoria ASSEGO</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
<?php include '_style_admin.php'; ?>
</head>
<body>
<nav class="main-header navbar">
  <div class="container-fluid d-flex align-items-center gap-3">
    <button class="btn btn-link text-white d-lg-none p-0 me-1" id="sidebarToggle"><i class="fas fa-bars"></i></button>
    <a class="navbar-brand d-flex align-items-center gap-2 text-white text-decoration-none" href="dashboard.php">
      <img src="logo.png" alt="ASSEGO" style="height:40px">
      <span style="font-weight:600;font-size:.95rem">Ouvidoria ASSEGO</span>
    </a>
    <div class="ms-auto d-flex align-items-center gap-3">
      <div class="user-pill d-none d-sm-flex"><div class="user-avatar"><?php echo mb_strtoupper(mb_substr($_SESSION['usuario_nome'],0,2)); ?></div><span class="user-name"><?php echo htmlspecialchars(explode(' ',$_SESSION['usuario_nome'])[0]); ?></span></div>
      <a href="logout.php" class="btn btn-outline-light btn-sm"><i class="fas fa-sign-out-alt"></i> <span class="d-none d-sm-inline">Sair</span></a>
    </div>
  </div>
</nav>

<div class="container-fluid p-0">
  <div class="row g-0">
    <div class="overlay" id="sidebarOverlay"></div>
    <nav class="col-lg-2 sidebar" id="sidebar">
      
      <ul class="nav flex-column pb-3">
        <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="manifestacoes.php"><i class="fas fa-comments"></i> Manifestações</a></li>
        <?php if (isset($nivel_acesso) && $nivel_acesso === 'admin'): ?>
        <li class="nav-item"><a class="nav-link" href="usuarios.php"><i class="fas fa-users"></i> Usuários</a></li>
        <?php endif; ?>
        <li class="nav-item"><a class="nav-link active" href="relatorios.php"><i class="fas fa-chart-bar"></i> Relatórios</a></li>
      </ul>
    </nav>

    <main class="col-lg-10 ms-sm-auto px-md-4 py-4 content-wrapper">
      <div class="page-header">
        <h2><span class="ph-icon"><i class="fas fa-chart-bar"></i></span>Relatórios</h2>
        <p><?php echo $periodo==='mensal' ? $meses_pt[$mes].' de '.$ano : 'Ano de '.$ano; ?></p>
      </div>

      <!-- FILTER -->
      <div class="filter-card mb-4">
        <form action="" method="GET" class="row g-2 align-items-end">
          <div class="col-6 col-md-3">
            <label class="form-label">Período</label>
            <select id="periodo" name="periodo" class="form-select form-select-sm">
              <option value="mensal" <?php echo $periodo==='mensal'?'selected':''; ?>>Mensal</option>
              <option value="anual" <?php echo $periodo==='anual'?'selected':''; ?>>Anual</option>
            </select>
          </div>
          <div class="col-6 col-md-3" id="mes-group" <?php echo $periodo==='anual'?'style="display:none"':''; ?>>
            <label class="form-label">Mês</label>
            <select id="mes" name="mes" class="form-select form-select-sm">
              <?php for($m=1;$m<=12;$m++): ?><option value="<?php echo $m; ?>" <?php echo $m==$mes?'selected':''; ?>><?php echo $meses_pt[$m]; ?></option><?php endfor; ?>
            </select>
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label">Ano</label>
            <select id="ano" name="ano" class="form-select form-select-sm">
              <?php foreach($anos_disponiveis as $a): ?><option value="<?php echo $a; ?>" <?php echo $a==$ano?'selected':''; ?>><?php echo $a; ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="col-6 col-md-3">
            <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-filter me-1"></i> Aplicar</button>
          </div>
        </form>
      </div>

      <!-- STAT CARDS -->
      <div class="row g-3 mb-4">
        <?php $rs=[['Total','fas fa-inbox',$total_manifestacoes,'navy'],['Sugestões','fas fa-lightbulb',$dados_por_tipo['sugestao']??0,'blue'],['Críticas','fas fa-exclamation-circle',$dados_por_tipo['critica']??0,'yellow'],['Elogios','fas fa-thumbs-up',$dados_por_tipo['elogio']??0,'green'],['Reclamações','fas fa-thumbs-down',$dados_por_tipo['reclamacao']??0,'red']];
        foreach($rs as $r): ?>
        <div class="col-6 col-md-4 col-xl">
          <div class="card stat-card h-100">
            <div class="card-body">
              <div class="stat-icon <?php echo $r[3]; ?>"><i class="<?php echo $r[1]; ?>"></i></div>
              <div class="stat-value"><?php echo $r[2]; ?></div>
              <div class="stat-label"><?php echo $r[0]; ?></div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <?php if($total_manifestacoes>0): ?>
      <div class="row g-3">
        <div class="col-lg-6">
          <div class="card h-100">
            <div class="card-header"><i class="fas fa-chart-pie" ></i> Por Tipo</div>
            <div class="card-body"><div class="chart-container"><canvas id="chartTipo"></canvas></div></div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="card h-100">
            <div class="card-header"><i class="fas fa-chart-pie" ></i> Por Status</div>
            <div class="card-body"><div class="chart-container"><canvas id="chartStatus"></canvas></div></div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="card h-100">
            <div class="card-header"><i class="fas fa-chart-bar" ></i> Por Área</div>
            <div class="card-body"><div class="chart-container"><canvas id="chartArea"></canvas></div></div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="card h-100">
            <div class="card-header"><i class="fas fa-chart-line" ></i> Evolução</div>
            <div class="card-body"><div class="chart-container"><canvas id="chartTempo"></canvas></div></div>
          </div>
        </div>
      </div>
      <?php else: ?>
      <div class="card"><div class="card-body"><div class="empty-state"><i class="fas fa-chart-bar"></i><h4>Sem dados para o período</h4><p>Selecione outro período para visualizar os relatórios.</p></div></div></div>
      <?php endif; ?>
    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php include '_js_sidebar.php'; ?>
<script>
document.getElementById('periodo').addEventListener('change',function(){
    document.getElementById('mes-group').style.display=this.value==='mensal'?'block':'none';
});
<?php if($total_manifestacoes>0): ?>
const cd=<?php echo $chart_data_json; ?>;
const navy='#000E72';
const chartOpts=(type,data,colors,legend=true)=>({
    type,data,
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:legend,position:'bottom',labels:{boxWidth:12,font:{size:11,family:'Poppins'}}}}}
});

new Chart(document.getElementById('chartTipo'),{
    type:'doughnut',
    data:{labels:cd.tipo.labels,datasets:[{data:cd.tipo.data,backgroundColor:['#1D4ED8','#B45309','#047857','#B91C1C'],borderWidth:2,borderColor:'#fff'}]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom',labels:{boxWidth:12,font:{size:11,family:'Poppins'}}}}}
});
new Chart(document.getElementById('chartStatus'),{
    type:'doughnut',
    data:{labels:cd.status.labels,datasets:[{data:cd.status.data,backgroundColor:['#92400E','#1E40AF','#065F46','#6B7280'],borderWidth:2,borderColor:'#fff'}]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom',labels:{boxWidth:12,font:{size:11,family:'Poppins'}}}}}
});
new Chart(document.getElementById('chartArea'),{
    type:'bar',
    data:{labels:cd.area.labels,datasets:[{label:'Manifestações',data:cd.area.data,backgroundColor:'rgba(0,14,114,0.7)',borderRadius:5}]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{precision:0,font:{size:11}}},x:{ticks:{font:{size:10}}}}}
});
new Chart(document.getElementById('chartTempo'),{
    type:'line',
    data:{labels:cd.tempo.labels,datasets:[{label:'Manifestações',data:cd.tempo.data,borderColor:navy,backgroundColor:'rgba(0,14,114,0.08)',borderWidth:2,fill:true,tension:.3,pointBackgroundColor:navy,pointRadius:3}]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{precision:0,font:{size:11}}},x:{ticks:{font:{size:10}}}}}
});
<?php endif; ?>
</script>
</body>
</html>
