<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Confirmação — Ouvidoria ASSEGO</title>
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
main{flex:1;display:flex;align-items:center;justify-content:center;padding:32px 16px}
.card{background:#fff;border:1px solid var(--border);border-radius:14px;box-shadow:0 4px 20px rgba(0,14,114,.08);padding:36px;max-width:560px;width:100%}
.success-circle{width:80px;height:80px;background:linear-gradient(135deg,#047857,#059669);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;box-shadow:0 8px 24px rgba(5,150,105,.3)}
.success-circle i{font-size:2rem;color:#fff}
h2{font-size:1.3rem;font-weight:700;color:var(--navy);text-align:center;margin-bottom:8px}
.subtitle{font-size:.85rem;color:var(--muted);text-align:center;margin-bottom:24px}
.protocol-box{background:var(--bg);border:2px dashed var(--border);border-radius:10px;padding:18px;text-align:center;margin-bottom:20px}
.protocol-label{font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.8px;color:var(--muted);margin-bottom:6px}
.protocol-num{font-size:1.1rem;font-weight:700;color:var(--navy);font-family:monospace;letter-spacing:1px;word-break:break-all}
.copy-btn{background:transparent;border:none;cursor:pointer;color:var(--navy);padding:4px 8px;border-radius:6px;font-size:.85rem;margin-left:6px;transition:background .15s}
.copy-btn:hover{background:var(--bg)}
.alert-warn{background:#FFFBEB;border:1px solid #FDE68A;color:#92400E;border-radius:10px;padding:12px 16px;font-size:.82rem;display:flex;align-items:flex-start;gap:10px;margin-bottom:20px}
.alert-warn i{flex-shrink:0;margin-top:1px}
.steps{background:#EFF6FF;border-radius:10px;padding:16px 20px;margin-bottom:24px}
.steps h4{font-size:.82rem;font-weight:700;color:#1D4ED8;margin-bottom:10px;display:flex;align-items:center;gap:6px}
.steps ol{margin:0;padding-left:20px}
.steps ol li{font-size:.82rem;color:#1e40af;margin-bottom:5px}
.copy-success{background:#D1FAE5;color:#065F46;border-radius:8px;padding:8px 14px;font-size:.8rem;text-align:center;margin-top:8px;display:none}
.btn-home{background:var(--navy);color:#fff;border:none;border-radius:10px;padding:12px 24px;font-family:'Poppins',sans-serif;font-size:.9rem;font-weight:600;cursor:pointer;width:100%;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:8px;transition:background .15s}
.btn-home:hover{background:#000855;color:#fff}
.site-footer{background:var(--navy);color:rgba(255,255,255,.6);text-align:center;padding:12px;font-size:.75rem}
</style>
</head>
<body>
<?php
$protocolo = isset($_GET['protocolo']) ? $_GET['protocolo'] : '';
$anonimo = isset($_GET['anonimo']) ? $_GET['anonimo'] : 0;
$email = isset($_GET['email']) ? $_GET['email'] : '';
?>
<header>
  <div class="site-header">
    <img src="logo.png" alt="ASSEGO">
    <h1>Ouvidoria ASSEGO</h1>
  </div>
  <div class="header-accent"></div>
</header>

<main>
  <div class="card">
    <div class="success-circle"><i class="fas fa-check"></i></div>
    <h2>Manifestação Enviada!</h2>
    <p class="subtitle">Sua manifestação foi registrada com sucesso e será analisada pela nossa equipe.</p>

    <div class="protocol-box">
      <div class="protocol-label">Número de Protocolo</div>
      <div class="d-flex align-items-center justify-content-center">
        <span class="protocol-num" id="protocolo"><?php echo htmlspecialchars($protocolo); ?></span>
        <button class="copy-btn" id="copyBtn" title="Copiar protocolo"><i class="fas fa-copy"></i></button>
      </div>
      <div class="copy-success" id="copySuccess"><i class="fas fa-check me-1"></i> Protocolo copiado!</div>
    </div>

    <div class="alert-warn">
      <i class="fas fa-exclamation-triangle"></i>
      <div><strong>Importante:</strong> Guarde este protocolo! Ele é a única forma de consultar o status da sua manifestação posteriormente.</div>
    </div>

    <?php if($anonimo==0&&!empty($email)): ?>
    <div class="alert-warn" style="background:#EFF6FF;border-color:#BFDBFE;color:#1e40af">
      <i class="fas fa-info-circle"></i>
      <div>Uma confirmação foi associada ao e-mail <strong><?php echo htmlspecialchars($email); ?></strong>. O protocolo é obrigatório para consultas.</div>
    </div>
    <?php endif; ?>

    <div class="steps">
      <h4><i class="fas fa-list-ol"></i> Como acompanhar</h4>
      <ol>
        <li>Acesse a página inicial da Ouvidoria ASSEGO</li>
        <li>Clique em "Consultar Minhas Manifestações"</li>
        <li>Informe o número de protocolo<?php echo $anonimo==0?' e seu e-mail':''; ?></li>
        <li>Visualize o status e a resposta da equipe</li>
      </ol>
    </div>

    <a href="index.html" class="btn-home"><i class="fas fa-home"></i> Voltar para a Página Inicial</a>
  </div>
</main>

<footer class="site-footer">Ouvidoria ASSEGO &copy; 2026 — Canal oficial de comunicação com os associados</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('copyBtn').addEventListener('click',function(){
    const p=document.getElementById('protocolo').textContent;
    navigator.clipboard?navigator.clipboard.writeText(p):((t=document.createElement('textarea'))&&(t.value=p,document.body.appendChild(t),t.select(),document.execCommand('copy'),document.body.removeChild(t)));
    const s=document.getElementById('copySuccess');s.style.display='block';
    setTimeout(()=>s.style.display='none',3000);
});
</script>
</body>
</html>
