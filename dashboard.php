<?php
date_default_timezone_set('America/Sao_Paulo');
require_once 'verificar_login.php';
require_once 'conexao.php';

$filtro_status = isset($_GET['status']) ? $_GET['status'] : 'todos';
$filtro_tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'todos';
$filtro_area = isset($_GET['area']) ? $_GET['area'] : 'todos';
$pagina_atual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$registros_por_pagina = 10;
$offset = ($pagina_atual - 1) * $registros_por_pagina;

$where_conditions = [];
$params = [];

if ($filtro_status === 'arquivada') {
    $where_conditions[] = "status = :status"; $params[':status'] = $filtro_status;
} elseif ($filtro_status === 'todos') {
    $where_conditions[] = "status != 'arquivada'";
} else {
    $where_conditions[] = "status = :status"; $params[':status'] = $filtro_status;
}
if ($filtro_tipo !== 'todos') { $where_conditions[] = "tipo_manifestacao = :tipo"; $params[':tipo'] = $filtro_tipo; }
if ($filtro_area !== 'todos') { $where_conditions[] = "area_relacionada = :area"; $params[':area'] = $filtro_area; }

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

$sql_count = "SELECT COUNT(*) as total FROM manifestacoes $where_clause";
$stmt_count = $pdo->prepare($sql_count);
foreach ($params as $key => $value) { $stmt_count->bindValue($key, $value); }
$stmt_count->execute();
$total_registros = $stmt_count->fetch()['total'];
$total_paginas = ceil($total_registros / $registros_por_pagina);

$sql = "SELECT * FROM manifestacoes $where_clause ORDER BY data_criacao DESC LIMIT :offset, :limit";
$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) { $stmt->bindValue($key, $value); }
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':limit', $registros_por_pagina, PDO::PARAM_INT);
$stmt->execute();
$manifestacoes = $stmt->fetchAll();

$sql_contadores = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status='pendente' THEN 1 ELSE 0 END) as pendentes,
    SUM(CASE WHEN status='em_analise' THEN 1 ELSE 0 END) as em_analise,
    SUM(CASE WHEN status='respondida' THEN 1 ELSE 0 END) as respondidas,
    SUM(CASE WHEN status='arquivada' THEN 1 ELSE 0 END) as arquivadas,
    SUM(CASE WHEN tipo_manifestacao='sugestao' THEN 1 ELSE 0 END) as sugestoes,
    SUM(CASE WHEN tipo_manifestacao='critica' THEN 1 ELSE 0 END) as criticas,
    SUM(CASE WHEN tipo_manifestacao='elogio' THEN 1 ELSE 0 END) as elogios,
    SUM(CASE WHEN tipo_manifestacao='reclamacao' THEN 1 ELSE 0 END) as reclamacoes
FROM manifestacoes";
$stmt_contadores = $pdo->prepare($sql_contadores);
$stmt_contadores->execute();
$contadores = $stmt_contadores->fetch();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — Ouvidoria ASSEGO</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<?php include '_style_admin.php'; ?>
</head>
<body>
<!-- NAVBAR -->
<nav class="main-header navbar">
  <div class="container-fluid d-flex align-items-center gap-3">
    <button class="btn btn-link text-white d-lg-none p-0 me-1" id="sidebarToggle"><i class="fas fa-bars"></i></button>
    <a class="navbar-brand d-flex align-items-center gap-2 text-white text-decoration-none" href="dashboard.php">
      <img src="logo.png" alt="ASSEGO" style="height:40px">
      <span style="font-weight:700;font-size:1rem;letter-spacing:.2px">Ouvidoria ASSEGO</span>
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
    <!-- SIDEBAR -->
    <nav class="col-lg-2 sidebar" id="sidebar">
      
      <ul class="nav flex-column pb-3">
        <li class="nav-item"><a class="nav-link active" href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="manifestacoes.php"><i class="fas fa-comments"></i> Manifestações</a></li>
        <?php if (isset($nivel_acesso) && $nivel_acesso === 'admin'): ?>
        <li class="nav-item"><a class="nav-link" href="usuarios.php"><i class="fas fa-users"></i> Usuários</a></li>
        <?php endif; ?>
        <li class="nav-item"><a class="nav-link" href="relatorios.php"><i class="fas fa-chart-bar"></i> Relatórios</a></li>
      </ul>
    </nav>

    <!-- CONTENT -->
    <main class="col-lg-10 ms-sm-auto px-md-4 py-4 content-wrapper">
      <div class="page-header">
        <h2><span class="ph-icon"><i class="fas fa-tachometer-alt"></i></span>Dashboard</h2>
        <p>Visão geral das manifestações da ouvidoria</p>
      </div>

      <!-- STAT CARDS -->
      <div class="row g-3 mb-4">
        <?php
        $stats = [
            ['Total','fas fa-inbox',$contadores['total'],'navy'],
            ['Pendentes','fas fa-clock',$contadores['pendentes'],'red'],
            ['Em Análise','fas fa-search',$contadores['em_analise'],'blue'],
            ['Respondidas','fas fa-check-circle',$contadores['respondidas'],'green'],
            ['Arquivadas','fas fa-archive',$contadores['arquivadas'],'gray'],
            ['Sugestões','fas fa-lightbulb',$contadores['sugestoes'],'blue'],
            ['Críticas','fas fa-exclamation-circle',$contadores['criticas'],'yellow'],
            ['Elogios','fas fa-thumbs-up',$contadores['elogios'],'green'],
            ['Reclamações','fas fa-thumbs-down',$contadores['reclamacoes'],'red'],
        ];
        foreach($stats as $s): ?>
        <div class="col-6 col-sm-4 col-md-3 col-xl-2-4">
          <div class="card stat-card h-100">
            <div class="card-body">
              <div class="stat-icon <?php echo $s[3]; ?>"><i class="<?php echo $s[1]; ?>"></i></div>
              <div class="stat-value"><?php echo $s[2]; ?></div>
              <div class="stat-label"><?php echo $s[0]; ?></div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- FILTERS -->
      <div class="filter-card">
        <form action="" method="GET" id="filtroForm">
          <div class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
              <label class="form-label">Status</label>
              <select name="status" class="form-select form-select-sm" onchange="document.getElementById('filtroForm').submit()">
                <option value="todos" <?php echo $filtro_status==='todos'?'selected':''; ?>>Ativas (padrão)</option>
                <option value="pendente" <?php echo $filtro_status==='pendente'?'selected':''; ?>>Pendente</option>
                <option value="em_analise" <?php echo $filtro_status==='em_analise'?'selected':''; ?>>Em Análise</option>
                <option value="respondida" <?php echo $filtro_status==='respondida'?'selected':''; ?>>Respondida</option>
                <option value="arquivada" <?php echo $filtro_status==='arquivada'?'selected':''; ?>>Arquivadas</option>
              </select>
            </div>
            <div class="col-12 col-md-4">
              <label class="form-label">Tipo</label>
              <select name="tipo" class="form-select form-select-sm" onchange="document.getElementById('filtroForm').submit()">
                <option value="todos" <?php echo $filtro_tipo==='todos'?'selected':''; ?>>Todos os Tipos</option>
                <option value="sugestao" <?php echo $filtro_tipo==='sugestao'?'selected':''; ?>>Sugestão</option>
                <option value="critica" <?php echo $filtro_tipo==='critica'?'selected':''; ?>>Crítica</option>
                <option value="elogio" <?php echo $filtro_tipo==='elogio'?'selected':''; ?>>Elogio</option>
                <option value="reclamacao" <?php echo $filtro_tipo==='reclamacao'?'selected':''; ?>>Reclamação</option>
              </select>
            </div>
            <div class="col-12 col-md-4">
              <label class="form-label">Área</label>
              <select name="area" class="form-select form-select-sm" onchange="document.getElementById('filtroForm').submit()">
                <option value="todos" <?php echo $filtro_area==='todos'?'selected':''; ?>>Todas as Áreas</option>
                <?php $areas=['administracao'=>'Administração','academia'=>'Academia','assegomaissaude'=>'Assego + Saúde','atendimento'=>'Atendimento','comercial'=>'Comercial','comunicacao'=>'Comunicação','convenio'=>'Convênio','eventos'=>'Eventos','financeiro'=>'Financeiro','hotel'=>'Hotel','juridico'=>'Jurídico','limpeza'=>'Limpeza','lazer'=>'Área de Lazer','manutencao'=>'Manutenção','outros'=>'Outros','parque'=>'Parque Aquático','presidencia'=>'Presidência','recursoshumanos'=>'RH','restaurante'=>'Restaurante','seguranca'=>'Segurança','estacionamento'=>'Estacionamento','servicossociais'=>'Serviços Sociais','tecnologia'=>'Tecnologia'];
                foreach($areas as $k=>$v): ?>
                <option value="<?php echo $k; ?>" <?php echo $filtro_area===$k?'selected':''; ?>><?php echo $v; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </form>
      </div>

      <!-- TABLE -->
      <div class="card">
        <div class="card-header">
          <i class="fas fa-list" ></i> Manifestações
          <span class="ms-auto text-muted" style="font-size:.75rem;font-weight:400"><?php echo $total_registros; ?> registro(s)</span>
        </div>
        <div class="card-body p-0">
          <?php if (count($manifestacoes) > 0): ?>
          <div class="table-wrapper">
            <table class="table">
              <thead>
                <tr>
                  <th>ID</th><th>Tipo</th><th class="d-none d-md-table-cell">Área</th>
                  <th class="d-none d-sm-table-cell">Data</th><th>Status</th><th>Ações</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($manifestacoes as $m): ?>
                <tr>
                  <td><span style="color:var(--muted);font-size:.78rem">#<?php echo $m['id']; ?></span></td>
                  <td>
                    <?php $tipos=['sugestao'=>['fas fa-lightbulb','type-sugestao','Sugestão'],'critica'=>['fas fa-exclamation-circle','type-critica','Crítica'],'elogio'=>['fas fa-thumbs-up','type-elogio','Elogio'],'reclamacao'=>['fas fa-thumbs-down','type-reclamacao','Reclamação']];
                    $t=$tipos[$m['tipo_manifestacao']]??['fas fa-comment','type-sugestao',$m['tipo_manifestacao']]; ?>
                    <span class="type-badge <?php echo $t[1]; ?>"><i class="<?php echo $t[0]; ?> me-1"></i><span class="d-none d-md-inline"><?php echo $t[2]; ?></span></span>
                  </td>
                  <td class="d-none d-md-table-cell" style="font-size:.83rem"><?php echo ucfirst($m['area_relacionada']); ?></td>
                  <td class="d-none d-sm-table-cell" style="font-size:.8rem;color:var(--muted)"><?php echo date('d/m/Y H:i',strtotime($m['data_criacao'])); ?></td>
                  <td>
                    <?php $sl=['pendente'=>['status-pendente','Pendente'],'em_analise'=>['status-em_analise','Em Análise'],'respondida'=>['status-respondida','Respondida'],'arquivada'=>['status-arquivada','Arquivada']];
                    $sc=$sl[$m['status']]??['status-arquivada',$m['status']]; ?>
                    <span class="status-badge <?php echo $sc[0]; ?>"><?php echo $sc[1]; ?></span>
                  </td>
                  <td>
                    <a href="visualizar_manifestacao.php?id=<?php echo $m['id']; ?>" class="btn btn-primary btn-sm"><i class="fas fa-eye"></i> <span class="d-none d-md-inline">Ver</span></a>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <?php if ($total_paginas > 1): ?>
          <div class="d-flex justify-content-center py-3">
            <nav><ul class="pagination mb-0">
              <?php if ($pagina_atual > 1): ?><li class="page-item"><a class="page-link" href="?pagina=<?php echo $pagina_atual-1; ?>&status=<?php echo $filtro_status; ?>&tipo=<?php echo $filtro_tipo; ?>&area=<?php echo $filtro_area; ?>">&laquo;</a></li><?php endif; ?>
              <?php for ($i=1;$i<=$total_paginas;$i++): ?><li class="page-item <?php echo $i==$pagina_atual?'active':''; ?>"><a class="page-link" href="?pagina=<?php echo $i; ?>&status=<?php echo $filtro_status; ?>&tipo=<?php echo $filtro_tipo; ?>&area=<?php echo $filtro_area; ?>"><?php echo $i; ?></a></li><?php endfor; ?>
              <?php if ($pagina_atual < $total_paginas): ?><li class="page-item"><a class="page-link" href="?pagina=<?php echo $pagina_atual+1; ?>&status=<?php echo $filtro_status; ?>&tipo=<?php echo $filtro_tipo; ?>&area=<?php echo $filtro_area; ?>">&raquo;</a></li><?php endif; ?>
            </ul></nav>
          </div>
          <?php endif; ?>
          <?php else: ?>
          <div class="empty-state"><i class="fas fa-inbox"></i><h4>Nenhuma manifestação encontrada</h4><p>Não há manifestações com os filtros selecionados.</p></div>
          <?php endif; ?>
        </div>
      </div>
    </main>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php include '_js_sidebar.php'; ?>
<style>.col-xl-2-4{flex:0 0 auto;width:20%}@media(max-width:1199px){.col-xl-2-4{width:25%}}@media(max-width:767px){.col-xl-2-4{width:50%}}</style>
</body>
</html>
