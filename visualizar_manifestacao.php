<?php
date_default_timezone_set('America/Sao_Paulo');
require_once 'verificar_login.php';
require_once 'conexao.php';

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if (!$id) { header('Location: dashboard.php'); exit; }

$mensagem_sucesso = '';
$mensagem_erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novo_status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
    $resposta = filter_input(INPUT_POST, 'resposta', FILTER_SANITIZE_STRING);
    try {
        $sql = "UPDATE manifestacoes SET status = :status, resposta = :resposta WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':status', $novo_status);
        $stmt->bindParam(':resposta', $resposta);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        if ($stmt->execute()) { $mensagem_sucesso = "Manifestação atualizada com sucesso!"; }
        else { $mensagem_erro = "Erro ao atualizar manifestação."; }
    } catch (PDOException $e) { $mensagem_erro = "Erro: " . $e->getMessage(); }
}

try {
    $sql = "SELECT * FROM manifestacoes WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    if ($stmt->rowCount() === 0) { header('Location: dashboard.php'); exit; }
    $manifestacao = $stmt->fetch();
} catch (PDOException $e) { $mensagem_erro = "Erro: " . $e->getMessage(); $manifestacao = null; }

$tipos = ['sugestao'=>'Sugestão','critica'=>'Crítica','elogio'=>'Elogio','reclamacao'=>'Reclamação'];
$status_labels = ['pendente'=>'Pendente','em_analise'=>'Em Análise','respondida'=>'Respondida','arquivada'=>'Arquivada'];
$tipo_icons = ['sugestao'=>['fas fa-lightbulb','type-sugestao'],'critica'=>['fas fa-exclamation-circle','type-critica'],'elogio'=>['fas fa-thumbs-up','type-elogio'],'reclamacao'=>['fas fa-thumbs-down','type-reclamacao']];
$areas=['administracao'=>'Administração','academia'=>'Academia','assegomaissaude'=>'Assego + Saúde','atendimento'=>'Atendimento','comercial'=>'Comercial','comunicacao'=>'Comunicação','convenio'=>'Convênio','eventos'=>'Eventos','financeiro'=>'Financeiro','hotel'=>'Hotel','juridico'=>'Jurídico','limpeza'=>'Limpeza','lazer'=>'Área de Lazer','manutencao'=>'Manutenção','outros'=>'Outros','parque'=>'Parque Aquático','presidencia'=>'Presidência','recursoshumanos'=>'RH','restaurante'=>'Restaurante','seguranca'=>'Segurança','estacionamento'=>'Estacionamento','servicossociais'=>'Serviços Sociais','tecnologia'=>'Tecnologia'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manifestação #<?php echo $id; ?> — Ouvidoria ASSEGO</title>
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
      <div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
        <a href="manifestacoes.php" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Voltar</a>
        <div>
          <h2 class="mb-0" style="font-size:1.25rem;font-weight:700;color:var(--navy)">Manifestação <span style="color:var(--muted)">#<?php echo $id; ?></span></h2>
          <?php if($manifestacao && $manifestacao['protocolo']): ?>
          <div style="font-size:.73rem;color:var(--muted);font-family:monospace;margin-top:2px"><?php echo htmlspecialchars($manifestacao['protocolo']); ?></div>
          <?php endif; ?>
        </div>
      </div>

      <?php if (!empty($mensagem_sucesso)): ?>
      <div class="alert alert-success mb-3"><i class="fas fa-check-circle"></i><?php echo $mensagem_sucesso; ?></div>
      <?php endif; ?>
      <?php if (!empty($mensagem_erro)): ?>
      <div class="alert alert-danger mb-3"><i class="fas fa-exclamation-circle"></i><?php echo $mensagem_erro; ?></div>
      <?php endif; ?>

      <?php if ($manifestacao): ?>
      <?php $ti=$tipo_icons[$manifestacao['tipo_manifestacao']]??['fas fa-comment','type-sugestao']; ?>
      
      <!-- TYPE + STATUS HEADER -->
      <div class="card mb-3">
        <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-3" style="padding:16px 20px">
          <span class="type-badge <?php echo $ti[1]; ?>" style="font-size:.85rem;padding:7px 14px">
            <i class="<?php echo $ti[0]; ?> me-2"></i><?php echo $tipos[$manifestacao['tipo_manifestacao']]??$manifestacao['tipo_manifestacao']; ?>
          </span>
          <?php $sl=['pendente'=>['status-pendente','Pendente'],'em_analise'=>['status-em_analise','Em Análise'],'respondida'=>['status-respondida','Respondida'],'arquivada'=>['status-arquivada','Arquivada']]; $sc=$sl[$manifestacao['status']]??['status-arquivada',$manifestacao['status']]; ?>
          <span class="status-badge <?php echo $sc[0]; ?>" style="font-size:.85rem;padding:7px 14px"><?php echo $sc[1]; ?></span>
        </div>
      </div>

      <div class="row g-3">
        <!-- LEFT: details + message -->
        <div class="col-lg-7">
          <div class="card mb-3">
            <div class="card-header"><i class="fas fa-info-circle" ></i> Informações</div>
            <div class="card-body">
              <div class="row g-2">
                <div class="col-sm-6"><div class="info-block"><span class="lbl">Área</span><span class="val"><?php echo $areas[$manifestacao['area_relacionada']]??ucfirst($manifestacao['area_relacionada']); ?></span></div></div>
                <div class="col-sm-6"><div class="info-block"><span class="lbl">Data de Envio</span><span class="val"><?php echo date('d/m/Y H:i',strtotime($manifestacao['data_criacao'])); ?></span></div></div>
                <div class="col-sm-6">
                  <div class="info-block"><span class="lbl">Manifestante</span>
                    <span class="val"><?php echo $manifestacao['anonimo'] ? '<em style="color:var(--muted);font-style:italic">Anônimo</em>' : htmlspecialchars($manifestacao['nome']??''); ?></span>
                  </div>
                </div>
                <?php if(!$manifestacao['anonimo'] && $manifestacao['email']): ?>
                <div class="col-sm-6"><div class="info-block"><span class="lbl">E-mail</span><span class="val" style="color:var(--navy)"><?php echo htmlspecialchars($manifestacao['email']); ?></span></div></div>
                <?php endif; ?>
                <?php if(!empty($manifestacao['ip_remetente'])): ?>
                <div class="col-sm-6"><div class="info-block"><span class="lbl">IP do Remetente</span><span class="val" style="font-family:monospace;font-size:.85rem"><?php echo htmlspecialchars($manifestacao['ip_remetente']); ?></span></div></div>
                <?php endif; ?>
                <?php if($manifestacao['data_atualizacao'] && $manifestacao['data_atualizacao']!==$manifestacao['data_criacao']): ?>
                <div class="col-sm-6"><div class="info-block"><span class="lbl">Última Atualização</span><span class="val"><?php echo date('d/m/Y H:i',strtotime($manifestacao['data_atualizacao'])); ?></span></div></div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <div class="card mb-3">
            <div class="card-header"><i class="fas fa-comment-alt" ></i> Mensagem</div>
            <div class="card-body">
              <div style="background:var(--bg);border-radius:var(--radius);padding:16px;font-size:.88rem;line-height:1.7;color:var(--text)">
                <?php echo nl2br(htmlspecialchars($manifestacao['mensagem'])); ?>
              </div>
            </div>
          </div>

          <?php if (!empty($manifestacao['imagem_path']) || !empty($manifestacao['imagem_blob'])): ?>
          <div class="card mb-3">
            <div class="card-header"><i class="fas fa-image" ></i> Imagem Anexada</div>
            <div class="card-body">
              <img src="exibir_imagem.php?id=<?php echo $manifestacao['id']; ?>" alt="Imagem" class="img-fluid rounded" style="max-height:320px">
            </div>
          </div>
          <?php endif; ?>

          <?php if (!empty($manifestacao['resposta'])): ?>
          <div class="card mb-3">
            <div class="card-header"><i class="fas fa-reply" ></i> Resposta Enviada</div>
            <div class="card-body">
              <div class="resp-box">
                <h6><i class="fas fa-shield-alt me-1"></i>Ouvidoria ASSEGO</h6>
                <div style="font-size:.87rem;line-height:1.7"><?php echo nl2br(htmlspecialchars($manifestacao['resposta'])); ?></div>
                <div style="font-size:.75rem;color:var(--muted);margin-top:10px"><i class="fas fa-clock me-1"></i>Respondido em <?php echo date('d/m/Y H:i',strtotime($manifestacao['data_atualizacao'])); ?></div>
              </div>
            </div>
          </div>
          <?php endif; ?>
        </div>

        <!-- RIGHT: action form -->
        <div class="col-lg-5">
          <div class="card" style="position:sticky;top:20px">
            <div class="card-header">
              <i class="fas fa-paper-plane" ></i>
              <?php echo (!empty($manifestacao['resposta'])) ? 'Atualizar Resposta' : 'Responder Manifestação'; ?>
            </div>
            <div class="card-body">
              <form action="" method="POST">
                <div class="mb-3">
                  <label class="form-label">Status da Manifestação</label>
                  <select name="status" class="form-select">
                    <option value="pendente" <?php echo $manifestacao['status']==='pendente'?'selected':''; ?>>Pendente</option>
                    <option value="em_analise" <?php echo $manifestacao['status']==='em_analise'?'selected':''; ?>>Em Análise</option>
                    <option value="respondida" <?php echo $manifestacao['status']==='respondida'?'selected':''; ?>>Respondida</option>
                    <option value="arquivada" <?php echo $manifestacao['status']==='arquivada'?'selected':''; ?>>Arquivada</option>
                  </select>
                </div>
                <div class="mb-3">
                  <label class="form-label">Resposta</label>
                  <textarea name="resposta" class="form-control" rows="8" placeholder="Digite a resposta para o manifestante..."><?php echo htmlspecialchars($manifestacao['resposta']??''); ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary w-100">
                  <i class="fas fa-paper-plane me-2"></i><?php echo (!empty($manifestacao['resposta'])) ? 'Atualizar' : 'Enviar Resposta'; ?>
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>
    </main>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php include '_js_sidebar.php'; ?>
</body>
</html>
