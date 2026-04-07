<?php
session_start();
date_default_timezone_set('America/Sao_Paulo');

if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'conexao.php';
    
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha'];
    
    try {
        $sql = "SELECT id, nome, email, senha, nivel_acesso FROM usuarios_admin WHERE email = :email AND ativo = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() === 1) {
            $usuario = $stmt->fetch();
            if (password_verify($senha, $usuario['senha'])) {
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                $_SESSION['usuario_email'] = $usuario['email'];
                $_SESSION['nivel_acesso'] = $usuario['nivel_acesso'];
                
                $sql_update = "UPDATE usuarios_admin SET ultimo_acesso = NOW() WHERE id = :id";
                $stmt_update = $pdo->prepare($sql_update);
                $stmt_update->bindParam(':id', $usuario['id']);
                $stmt_update->execute();
                
                header('Location: dashboard.php');
                exit;
            } else {
                $erro = "Senha incorreta.";
            }
        } else {
            $erro = "E-mail não encontrado ou usuário inativo.";
        }
    } catch (PDOException $e) {
        $erro = "Erro ao processar login: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — Ouvidoria ASSEGO</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Poppins',sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;position:relative;overflow:hidden}

/* ── CAROUSEL BG ── */
.bg-carousel{position:fixed;inset:0;z-index:0}
.bg-slide{position:absolute;inset:0;background-size:cover;background-position:center;opacity:0;transition:opacity 1.2s ease-in-out;animation:none}
.bg-slide.active{opacity:1}
.bg-slide:nth-child(1){background-image:url('foto1.jpg')}
.bg-slide:nth-child(2){background-image:url('foto2.jpg')}
.bg-slide:nth-child(3){background-image:url('foto3.jpg')}
/* blue gradient overlay on top of photos */
.bg-overlay{position:absolute;inset:0;background:linear-gradient(135deg,rgba(0,14,114,.82) 0%,rgba(0,26,170,.70) 50%,rgba(0,14,114,.88) 100%);z-index:1}
.login-wrap{position:relative;z-index:2;width:100%;max-width:420px}
.login-card{background:#fff;border-radius:18px;overflow:hidden;box-shadow:0 24px 64px rgba(0,0,0,.25)}
.login-head{background:linear-gradient(135deg,#000E72 0%,#001aaa 100%);padding:32px 36px 28px;text-align:center;position:relative}
.login-head::after{content:'';position:absolute;bottom:-1px;left:0;right:0;height:4px;background:linear-gradient(90deg,#FFDF00,#FFB800)}
.login-logo{display:flex;justify-content:center;margin:0 auto 14px;}
.login-logo img{height:72px;}
.login-head h1{font-size:1.2rem;font-weight:700;color:#fff;margin-bottom:3px}
.login-head p{font-size:.78rem;color:rgba(255,255,255,.65);margin:0}
.login-body{padding:32px 36px}
.form-group{margin-bottom:18px}
.form-group label{display:block;font-size:.8rem;font-weight:600;color:#1e1e3a;margin-bottom:6px}
.form-group .input-wrap{position:relative}
.form-group .input-wrap i{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:.85rem}
.form-group input{width:100%;padding:10px 13px 10px 36px;border:1px solid #e5e7eb;border-radius:9px;font-family:'Poppins',sans-serif;font-size:.875rem;color:#1e1e3a;transition:border .15s,box-shadow .15s;background:#fafafa}
.form-group input:focus{outline:none;border-color:#000E72;box-shadow:0 0 0 3px rgba(0,14,114,.1);background:#fff}
.error-msg{background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;border-radius:8px;padding:10px 13px;font-size:.82rem;display:flex;align-items:center;gap:8px;margin-bottom:16px}
.btn-login{width:100%;padding:11px;background:#000E72;color:#fff;border:none;border-radius:9px;font-family:'Poppins',sans-serif;font-size:.9rem;font-weight:600;cursor:pointer;transition:background .15s,transform .1s;letter-spacing:.3px}
.btn-login:hover{background:#000855;transform:translateY(-1px)}
.btn-login:active{transform:translateY(0)}
.back-link{text-align:center;margin-top:20px;font-size:.8rem}
.back-link a{color:#6b7280;text-decoration:none;display:inline-flex;align-items:center;gap:5px}
.back-link a:hover{color:#000E72}
.login-footer{background:#f9fafb;border-top:1px solid #f0f0f0;padding:14px 36px;text-align:center}
.login-footer p{font-size:.73rem;color:#9ca3af;margin:0}
</style>
</head>
<body>
<div class="bg-carousel">
  <div class="bg-slide active"></div>
  <div class="bg-slide"></div>
  <div class="bg-slide"></div>
  <div class="bg-overlay"></div>
</div>
<div class="login-wrap">
  <div class="login-card">
    <div class="login-head">
      <div class="login-logo">
        <img src="logo.png" alt="ASSEGO">
      </div>
      <h1>Ouvidoria ASSEGO</h1>
      <p>Painel Administrativo</p>
    </div>
    <div class="login-body">
      <?php if (!empty($erro)): ?>
      <div class="error-msg"><i class="fas fa-exclamation-circle"></i><?php echo $erro; ?></div>
      <?php endif; ?>
      <form method="POST" action="">
        <div class="form-group">
          <label for="email"><i class="fas fa-envelope" style="margin-right:5px;color:#000E72"></i> E-mail</label>
          <div class="input-wrap">
            <i class="fas fa-envelope"></i>
            <input type="email" id="email" name="email" placeholder="seu@assego.com.br" required>
          </div>
        </div>
        <div class="form-group">
          <label for="senha"><i class="fas fa-lock" style="margin-right:5px;color:#000E72"></i> Senha</label>
          <div class="input-wrap">
            <i class="fas fa-lock"></i>
            <input type="password" id="senha" name="senha" placeholder="••••••••" required>
          </div>
        </div>
        <button type="submit" class="btn-login"><i class="fas fa-sign-in-alt" style="margin-right:8px"></i>Entrar</button>
      </form>
      <div class="back-link">
        <a href="index.html"><i class="fas fa-arrow-left"></i> Voltar ao formulário público</a>
      </div>
    </div>
    <div class="login-footer">
      <p><i class="fas fa-shield-alt" style="margin-right:4px;color:#FFDF00"></i> Sistema protegido — ASSEGO © 2026</p>
    </div>
  </div>
</div>
<script>
const slides=document.querySelectorAll('.bg-slide');
let cur=0;
setInterval(()=>{
  slides[cur].classList.remove('active');
  cur=(cur+1)%slides.length;
  slides[cur].classList.add('active');
},5000);
</script>
</body>
</html>