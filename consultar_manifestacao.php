<?php
date_default_timezone_set('America/Sao_Paulo');
require_once 'conexao.php';

$protocolo = isset($_GET['protocolo']) ? $_GET['protocolo'] : '';
$email = isset($_GET['email']) ? $_GET['email'] : '';
$error_message = '';
$manifestacao = null;

if (!empty($protocolo)) {
    try {
        $sql = "SELECT * FROM manifestacoes WHERE protocolo = :protocolo";
        $params = [':protocolo' => $protocolo];
        if (!empty($email)) { $sql .= " AND email = :email AND anonimo = 0"; $params[':email'] = $email; }
        $stmt = $pdo->prepare($sql);
        foreach ($params as $p => $v) { $stmt->bindValue($p, $v); }
        $stmt->execute();
        if ($stmt->rowCount() > 0) { $manifestacao = $stmt->fetch(PDO::FETCH_ASSOC); }
        else {
            $stmt_check = $pdo->prepare("SELECT anonimo FROM manifestacoes WHERE protocolo = :protocolo");
            $stmt_check->bindValue(':protocolo', $protocolo);
            $stmt_check->execute();
            if ($stmt_check->rowCount() > 0) {
                $r = $stmt_check->fetch();
                $error_message = $r['anonimo']==0 ? "E-mail não corresponde ao protocolo informado." : "Protocolo não encontrado.";
            } else { $error_message = "Protocolo não encontrado. Verifique e tente novamente."; }
        }
    } catch (PDOException $e) { $error_message = "Erro: " . $e->getMessage(); }
}

$status_labels=['pendente'=>'Pendente','em_analise'=>'Em Análise','respondida'=>'Respondida','arquivada'=>'Arquivada'];
$tipos=['sugestao'=>'Sugestão','critica'=>'Crítica','elogio'=>'Elogio','reclamacao'=>'Reclamação'];
$status_cls=['pendente'=>'s-pendente','em_analise'=>'s-analise','respondida'=>'s-respondida','arquivada'=>'s-arquivada'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Consultar Manifestação — Ouvidoria ASSEGO</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--navy:#000E72;--yellow:#FFDF00;--bg:#f0f2f8;--border:#e5e7eb;--text:#1e1e3a;--muted:#6b7280}
*{box-sizing:border-box}
body{font-family:'Poppins',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;flex-direction:column}
.site-header{background:var(--navy);padding:13px 20px;display:flex;align-items:center;gap:12px;box-shadow:0 2px 12px rgba(0,14,114,.25)}
.site-header img{height:34px;}
.site-header h1{font-size:.95rem;font-weight:700;color:#fff;margin:0}
.header-accent{height:3px;background:linear-gradient(90deg,var(--yellow),#FFB800)}
main{flex:1;display:flex;align-items:flex-start;justify-content:center;padding:32px 16px}
.wrap{width:100%;max-width:600px}
.card{background:#fff;border:1px solid var(--border);border-radius:14px;box-shadow:0 4px 20px rgba(0,14,114,.08);overflow:hidden;margin-bottom:20px}
.card-head{padding:18px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px}
.card-head h2{font-size:1rem;font-weight:700;color:var(--navy);margin:0}
.card-head i{color:var(--yellow);font-size:1.1rem}
.card-body{padding:24px}
.form-label{font-size:.8rem;font-weight:600;color:var(--text);display:block;margin-bottom:5px}
.form-control{border:1px solid var(--border);border-radius:8px;padding:10px 13px;font-size:.875rem;color:var(--text);font-family:'Poppins',sans-serif;width:100%;transition:border .15s,box-shadow .15s}
.form-control:focus{outline:none;border-color:var(--navy);box-shadow:0 0 0 3px rgba(0,14,114,.1)}
.btn-search{background:var(--navy);color:#fff;border:none;border-radius:8px;padding:10px 20px;font-family:'Poppins',sans-serif;font-size:.875rem;font-weight:600;cursor:pointer;width:100%;display:flex;align-items:center;justify-content:center;gap:8px;transition:background .15s}
.btn-search:hover{background:#000855}
.s-pendente{background:#FEF3C7;color:#92400E}
.s-analise{background:#DBEAFE;color:#1E40AF}
.s-respondida{background:#D1FAE5;color:#065F46}
.s-arquivada{background:#F3F4F6;color:#6B7280}
.status-chip{display:inline-block;padding:5px 14px;border-radius:20px;font-size:.78rem;font-weight:600}
.info-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:20px}
@media(max-width:500px){.info-grid{grid-template-columns:1fr}}
.info-block{background:var(--bg);border-radius:8px;padding:12px}
.info-block .lbl{font-size:.68rem;font-weight:600;text-transform:uppercase;letter-spacing:.6px;color:var(--muted);margin-bottom:3px}
.info-block .val{font-size:.87rem;font-weight:500}
.msg-box{background:var(--bg);border-radius:10px;padding:16px;font-size:.88rem;line-height:1.7;margin-bottom:20px}
.resp-box{border-left:4px solid var(--navy);padding:16px 18px;background:#eef0fb;border-radius:0 10px 10px 0;margin-bottom:20px}
.resp-box h5{font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--navy);margin-bottom:8px}
.resp-box .resp-text{font-size:.88rem;line-height:1.7}
.wait-box{background:#FFFBEB;border:1px solid #FDE68A;border-radius:10px;padding:20px;text-align:center;margin-bottom:20px}
.wait-box i{font-size:2rem;color:#D97706;margin-bottom:8px;display:block}
.wait-box h4{font-size:.95rem;font-weight:600;color:#92400E;margin-bottom:4px}
.wait-box p{font-size:.82rem;color:#B45309;margin:0}
.error-box{text-align:center;padding:32px}
.error-box i{font-size:3rem;color:#B91C1C;margin-bottom:12px;display:block;opacity:.6}
.error-box h3{font-size:1.05rem;font-weight:700;color:var(--text);margin-bottom:6px}
.error-box p{font-size:.85rem;color:var(--muted)}
.btn-back{background:#fff;color:var(--navy);border:2px solid var(--navy);border-radius:8px;padding:9px 18px;font-family:'Poppins',sans-serif;font-size:.85rem;font-weight:600;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:7px;transition:all .15s}
.btn-back:hover{background:var(--navy);color:#fff}
.site-footer{background:var(--navy);color:rgba(255,255,255,.6);text-align:center;padding:12px;font-size:.75rem}
</style>
</head>
<body>
<header>
  <div class="site-header">
    <img src="logo.png" alt="ASSEGO">
    <h1>Ouvidoria ASSEGO</h1>
  </div>
  <div class="header-accent"></div>
</header>

<main>
  <div class="wrap">
    <?php if(!empty($protocolo)&&empty($error_message)&&$manifestacao): ?>
    <!-- RESULT -->
    <div class="card">
      <div class="card-head"><i class="fas fa-file-alt"></i><h2>Detalhes da Manifestação</h2></div>
      <div class="card-body">
        <?php $sc=$status_cls[$manifestacao['status']??'pendente']??'s-arquivada'; ?>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
          <div>
            <div style="font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.6px;color:var(--muted);margin-bottom:4px">Protocolo</div>
            <div style="font-size:.85rem;font-family:monospace;color:var(--navy);font-weight:700"><?php echo htmlspecialchars($manifestacao['protocolo']); ?></div>
          </div>
          <span class="status-chip <?php echo $sc; ?>"><?php echo $status_labels[$manifestacao['status']??'pendente']; ?></span>
        </div>

        <div class="info-grid">
          <div class="info-block"><div class="lbl">Tipo</div><div class="val"><?php echo $tipos[$manifestacao['tipo_manifestacao']]??'N/A'; ?></div></div>
          <div class="info-block"><div class="lbl">Área</div><div class="val"><?php echo ucfirst($manifestacao['area_relacionada']); ?></div></div>
          <div class="info-block"><div class="lbl">Data de Envio</div><div class="val"><?php echo date('d/m/Y H:i',strtotime($manifestacao['data_criacao'])); ?></div></div>
          <div class="info-block">
            <div class="lbl">Identificação</div>
            <div class="val"><?php echo $manifestacao['anonimo']==0?htmlspecialchars($manifestacao['nome']):'Anônimo'; ?></div>
          </div>
        </div>

        <h4 style="font-size:.85rem;font-weight:700;color:var(--navy);margin-bottom:8px"><i class="fas fa-comment-alt me-2" style="color:var(--yellow)"></i>Sua Mensagem</h4>
        <div class="msg-box"><?php echo nl2br(htmlspecialchars($manifestacao['mensagem'])); ?></div>

        <?php if(!empty($manifestacao['imagem_blob'])): ?>
        <h4 style="font-size:.85rem;font-weight:700;color:var(--navy);margin-bottom:8px"><i class="fas fa-image me-2" style="color:var(--yellow)"></i>Imagem Anexada</h4>
        <img src="exibir_imagem.php?id=<?php echo $manifestacao['id']; ?>" alt="Imagem" style="max-width:100%;border-radius:10px;margin-bottom:20px">
        <?php endif; ?>

        <?php if(!empty($manifestacao['resposta'])): ?>
        <h4 style="font-size:.85rem;font-weight:700;color:var(--navy);margin-bottom:8px"><i class="fas fa-reply me-2" style="color:var(--yellow)"></i>Resposta da Ouvidoria</h4>
        <div class="resp-box">
          <h5><i class="fas fa-shield-alt me-1"></i>Equipe ASSEGO</h5>
          <div class="resp-text"><?php echo nl2br(htmlspecialchars($manifestacao['resposta'])); ?></div>
          <div style="font-size:.73rem;color:var(--muted);margin-top:8px"><i class="fas fa-clock me-1"></i>Respondido em <?php echo date('d/m/Y H:i',strtotime($manifestacao['data_atualizacao'])); ?></div>
        </div>
        <?php else: ?>
        <div class="wait-box">
          <i class="fas fa-hourglass-half"></i>
          <h4>Aguardando Resposta</h4>
          <p>Sua manifestação está sendo analisada. Em breve você receberá uma resposta.</p>
        </div>
        <?php endif; ?>

        <a href="index.html" class="btn-back"><i class="fas fa-arrow-left"></i> Voltar à página inicial</a>
      </div>
    </div>

    <?php elseif(!empty($error_message)): ?>
    <!-- ERROR -->
    <div class="card">
      <div class="card-body">
        <div class="error-box">
          <i class="fas fa-exclamation-circle"></i>
          <h3>Não foi possível localizar</h3>
          <p><?php echo $error_message; ?></p>
          <a href="index.html" class="btn-back mt-3"><i class="fas fa-arrow-left"></i> Voltar</a>
        </div>
      </div>
    </div>

    <?php else: ?>
    <!-- FORM -->
    <div class="card">
      <div class="card-head"><i class="fas fa-search"></i><h2>Consultar Manifestação</h2></div>
      <div class="card-body">
        <p style="font-size:.85rem;color:var(--muted);margin-bottom:20px">Informe o número de protocolo recebido após o envio da sua manifestação.</p>
        <form action="consultar_manifestacao.php" method="GET">
          <div class="mb-3"><label class="form-label">Número de Protocolo</label><input type="text" class="form-control" name="protocolo" placeholder="Ex: 20260331-0001-abc123" required></div>
          <div class="mb-4"><label class="form-label">E-mail <span style="font-weight:400;color:var(--muted)">(se não for anônimo)</span></label><input type="email" class="form-control" name="email" placeholder="seu@email.com"></div>
          <button type="submit" class="btn-search"><i class="fas fa-search"></i> Consultar</button>
        </form>
        <div style="text-align:center;margin-top:20px"><a href="index.html" class="btn-back"><i class="fas fa-arrow-left"></i> Voltar à página inicial</a></div>
      </div>
    </div>
    <?php endif; ?>
  </div>
</main>

<footer class="site-footer">Ouvidoria ASSEGO &copy; 2026 — Canal oficial de comunicação com os associados</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
