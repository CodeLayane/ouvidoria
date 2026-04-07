<?php
date_default_timezone_set('America/Sao_Paulo');
require_once 'verificar_login.php';
require_once 'conexao.php';

$filtro_status = isset($_GET['status']) ? $_GET['status'] : 'todos';
$filtro_tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'todos';
$filtro_area = isset($_GET['area']) ? $_GET['area'] : 'todos';
$filtro_data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : '';
$filtro_data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : '';
$pagina_atual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$registros_por_pagina = 15;
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
if (!empty($filtro_data_inicio)) { $where_conditions[] = "data_criacao >= :data_inicio"; $params[':data_inicio'] = $filtro_data_inicio . ' 00:00:00'; }
if (!empty($filtro_data_fim)) { $where_conditions[] = "data_criacao <= :data_fim"; $params[':data_fim'] = $filtro_data_fim . ' 23:59:59'; }

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

$areas = ['administracao'=>'Administração','academia'=>'Academia','assegomaissaude'=>'Assego + Saúde','atendimento'=>'Atendimento','comercial'=>'Comercial','comunicacao'=>'Comunicação','convenio'=>'Convênio','eventos'=>'Eventos','financeiro'=>'Financeiro','hotel'=>'Hotel','juridico'=>'Jurídico','limpeza'=>'Limpeza','lazer'=>'Área de Lazer','manutencao'=>'Manutenção','outros'=>'Outros','parque'=>'Parque Aquático','presidencia'=>'Presidência','recursoshumanos'=>'RH','restaurante'=>'Restaurante','seguranca'=>'Segurança','estacionamento'=>'Estacionamento','servicossociais'=>'Serviços Sociais','tecnologia'=>'Tecnologia'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manifestações — Ouvidoria ASSEGO</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
        <li class="nav-item"><a class="nav-link active" href="manifestacoes.php"><i class="fas fa-comments"></i> Manifestações</a></li>
        <?php if (isset($nivel_acesso) && $nivel_acesso === 'admin'): ?>
        <li class="nav-item"><a class="nav-link" href="usuarios.php"><i class="fas fa-users"></i> Usuários</a></li>
        <?php endif; ?>
        <li class="nav-item"><a class="nav-link" href="relatorios.php"><i class="fas fa-chart-bar"></i> Relatórios</a></li>
      </ul>
    </nav>

    <main class="col-lg-10 ms-sm-auto px-md-4 py-4 content-wrapper">
      <div class="page-header">
        <h2><span class="ph-icon"><i class="fas fa-comments"></i></span>Manifestações</h2>
        <p><?php echo $total_registros; ?> registro(s) encontrado(s)</p>
      </div>

      <!-- FILTERS -->
      <div class="filter-card">
        <form action="" method="GET">
          <div class="row g-2">
            <div class="col-6 col-md-3">
              <label class="form-label">Status</label>
              <select name="status" class="form-select form-select-sm">
                <option value="todos" <?php echo $filtro_status==='todos'?'selected':''; ?>>Ativas (padrão)</option>
                <option value="pendente" <?php echo $filtro_status==='pendente'?'selected':''; ?>>Pendente</option>
                <option value="em_analise" <?php echo $filtro_status==='em_analise'?'selected':''; ?>>Em Análise</option>
                <option value="respondida" <?php echo $filtro_status==='respondida'?'selected':''; ?>>Respondida</option>
                <option value="arquivada" <?php echo $filtro_status==='arquivada'?'selected':''; ?>>Arquivadas</option>
              </select>
            </div>
            <div class="col-6 col-md-3">
              <label class="form-label">Tipo</label>
              <select name="tipo" class="form-select form-select-sm">
                <option value="todos" <?php echo $filtro_tipo==='todos'?'selected':''; ?>>Todos</option>
                <option value="sugestao" <?php echo $filtro_tipo==='sugestao'?'selected':''; ?>>Sugestão</option>
                <option value="critica" <?php echo $filtro_tipo==='critica'?'selected':''; ?>>Crítica</option>
                <option value="elogio" <?php echo $filtro_tipo==='elogio'?'selected':''; ?>>Elogio</option>
                <option value="reclamacao" <?php echo $filtro_tipo==='reclamacao'?'selected':''; ?>>Reclamação</option>
              </select>
            </div>
            <div class="col-6 col-md-3">
              <label class="form-label">Área</label>
              <select name="area" class="form-select form-select-sm">
                <option value="todos" <?php echo $filtro_area==='todos'?'selected':''; ?>>Todas</option>
                <?php foreach($areas as $k=>$v): ?><option value="<?php echo $k; ?>" <?php echo $filtro_area===$k?'selected':''; ?>><?php echo $v; ?></option><?php endforeach; ?>
              </select>
            </div>
            <div class="col-6 col-md-3">
              <label class="form-label">Data Inicial</label>
              <input type="date" name="data_inicio" class="form-control form-control-sm" value="<?php echo $filtro_data_inicio; ?>">
            </div>
            <div class="col-6 col-md-3">
              <label class="form-label">Data Final</label>
              <input type="date" name="data_fim" class="form-control form-control-sm" value="<?php echo $filtro_data_fim; ?>">
            </div>
            <div class="col-12 col-md-9 d-flex align-items-end gap-2">
              <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter me-1"></i> Filtrar</button>
              <a href="manifestacoes.php" class="btn btn-outline-secondary btn-sm"><i class="fas fa-sync-alt me-1"></i> Limpar</a>
            </div>
          </div>
        </form>
      </div>

      <div class="card">
        <div class="card-header"><i class="fas fa-list" ></i> Lista de Manifestações</div>
        <div class="card-body p-0">
          <?php if (count($manifestacoes) > 0): ?>
          <div class="table-wrapper">
            <table class="table">
              <thead>
                <tr>
                  <th>ID</th><th>Tipo</th><th class="d-none d-md-table-cell">Área</th>
                  <th class="d-none d-sm-table-cell">Mensagem</th><th class="d-none d-lg-table-cell">Data</th>
                  <th class="d-none d-md-table-cell">Identificação</th><th>Status</th><th>Ações</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($manifestacoes as $m):
                $tipos=['sugestao'=>['fas fa-lightbulb','type-sugestao','Sugestão'],'critica'=>['fas fa-exclamation-circle','type-critica','Crítica'],'elogio'=>['fas fa-thumbs-up','type-elogio','Elogio'],'reclamacao'=>['fas fa-thumbs-down','type-reclamacao','Reclamação']];
                $t=$tipos[$m['tipo_manifestacao']]??['fas fa-comment','type-sugestao',$m['tipo_manifestacao']];
                $sl=['pendente'=>['status-pendente','Pendente'],'em_analise'=>['status-em_analise','Em Análise'],'respondida'=>['status-respondida','Respondida'],'arquivada'=>['status-arquivada','Arquivada']];
                $sc=$sl[$m['status']]??['status-arquivada',$m['status']];
                ?>
                <tr>
                  <td><span style="color:var(--muted);font-size:.78rem">#<?php echo $m['id']; ?></span></td>
                  <td><span class="type-badge <?php echo $t[1]; ?>"><i class="<?php echo $t[0]; ?> me-1"></i><span class="d-none d-md-inline"><?php echo $t[2]; ?></span></span></td>
                  <td class="d-none d-md-table-cell" style="font-size:.83rem"><?php echo $areas[$m['area_relacionada']]??ucfirst($m['area_relacionada']); ?></td>
                  <td class="d-none d-sm-table-cell"><div class="message-preview" style="font-size:.82rem;color:var(--muted)"><?php echo htmlspecialchars($m['mensagem']); ?></div></td>
                  <td class="d-none d-lg-table-cell" style="font-size:.8rem;color:var(--muted)"><?php echo date('d/m/Y H:i',strtotime($m['data_criacao'])); ?></td>
                  <td class="d-none d-md-table-cell">
                    <?php if($m['anonimo']): ?>
                    <span style="font-size:.78rem;color:var(--muted);font-style:italic"><i class="fas fa-user-secret me-1"></i>Anônimo</span>
                    <?php else: ?>
                    <span style="font-size:.78rem"><i class="fas fa-user me-1" style="color:#000E72"></i>Identificado</span>
                    <?php endif; ?>
                  </td>
                  <td><span class="status-badge <?php echo $sc[0]; ?>"><?php echo $sc[1]; ?></span></td>
                  <td><a href="visualizar_manifestacao.php?id=<?php echo $m['id']; ?>" class="btn btn-primary btn-sm"><i class="fas fa-eye"></i></a></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <?php if ($total_paginas > 1): ?>
          <div class="d-flex justify-content-center py-3">
            <nav><ul class="pagination mb-0">
              <?php $qs="status=$filtro_status&tipo=$filtro_tipo&area=$filtro_area&data_inicio=$filtro_data_inicio&data_fim=$filtro_data_fim"; ?>
              <?php if($pagina_atual>1): ?><li class="page-item"><a class="page-link" href="?pagina=<?php echo $pagina_atual-1; ?>&<?php echo $qs; ?>">&laquo;</a></li><?php endif; ?>
              <?php for($i=1;$i<=$total_paginas;$i++): ?><li class="page-item <?php echo $i==$pagina_atual?'active':''; ?>"><a class="page-link" href="?pagina=<?php echo $i; ?>&<?php echo $qs; ?>"><?php echo $i; ?></a></li><?php endfor; ?>
              <?php if($pagina_atual<$total_paginas): ?><li class="page-item"><a class="page-link" href="?pagina=<?php echo $pagina_atual+1; ?>&<?php echo $qs; ?>">&raquo;</a></li><?php endif; ?>
            </ul></nav>
          </div>
          <?php endif; ?>
          <?php else: ?>
          <div class="empty-state"><i class="fas fa-inbox"></i><h4>Nenhuma manifestação encontrada</h4><p>Altere os filtros e tente novamente.</p></div>
          <?php endif; ?>
        </div>
      </div>
    </main>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php include '_js_sidebar.php'; ?>
</body>
</html>
