<?php
require_once 'verificar_login.php';
if ($nivel_acesso !== 'admin') { header('Location: dashboard.php'); exit; }
require_once 'conexao.php';

$mensagem_sucesso = '';
$mensagem_erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    $acao = $_POST['acao'];
    if ($acao === 'adicionar') {
        $nome = filter_input(INPUT_POST,'nome',FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST,'email',FILTER_SANITIZE_EMAIL);
        $senha = $_POST['senha'];
        $nivel_acesso_usuario = filter_input(INPUT_POST,'nivel_acesso',FILTER_SANITIZE_STRING);
        if (empty($nome)||empty($email)||empty($senha)||empty($nivel_acesso_usuario)) { $mensagem_erro="Todos os campos são obrigatórios."; }
        else {
            try {
                $sql_check="SELECT COUNT(*) as count FROM usuarios_admin WHERE email=:email"; $stmt_check=$pdo->prepare($sql_check); $stmt_check->bindParam(':email',$email); $stmt_check->execute();
                if($stmt_check->fetch()['count']>0){ $mensagem_erro="Este e-mail já está cadastrado."; }
                else {
                    $senha_hash=password_hash($senha,PASSWORD_DEFAULT);
                    $sql="INSERT INTO usuarios_admin (nome,email,senha,nivel_acesso) VALUES (:nome,:email,:senha,:nivel_acesso)";
                    $stmt=$pdo->prepare($sql); $stmt->bindParam(':nome',$nome); $stmt->bindParam(':email',$email); $stmt->bindParam(':senha',$senha_hash); $stmt->bindParam(':nivel_acesso',$nivel_acesso_usuario);
                    if($stmt->execute()) $mensagem_sucesso="Usuário adicionado com sucesso!"; else $mensagem_erro="Erro ao adicionar usuário.";
                }
            } catch(PDOException $e){ $mensagem_erro="Erro: ".$e->getMessage(); }
        }
    } else if ($acao==='atualizar'&&isset($_POST['id'])) {
        $id=filter_input(INPUT_POST,'id',FILTER_SANITIZE_NUMBER_INT);
        $nome=filter_input(INPUT_POST,'nome',FILTER_SANITIZE_STRING);
        $email=filter_input(INPUT_POST,'email',FILTER_SANITIZE_EMAIL);
        $nivel_acesso_usuario=filter_input(INPUT_POST,'nivel_acesso',FILTER_SANITIZE_STRING);
        $ativo=isset($_POST['ativo'])?1:0;
        if(!$id||$id==$_SESSION['usuario_id']){ $mensagem_erro="Não é possível editar seu próprio usuário por esta interface."; }
        else {
            try {
                $sql_check="SELECT COUNT(*) as count FROM usuarios_admin WHERE email=:email AND id!=:id"; $stmt_check=$pdo->prepare($sql_check); $stmt_check->bindParam(':email',$email); $stmt_check->bindParam(':id',$id); $stmt_check->execute();
                if($stmt_check->fetch()['count']>0){ $mensagem_erro="Este e-mail já está em uso."; }
                else {
                    if(!empty($_POST['senha'])){ $senha_hash=password_hash($_POST['senha'],PASSWORD_DEFAULT); $sql="UPDATE usuarios_admin SET nome=:nome,email=:email,senha=:senha,nivel_acesso=:nivel_acesso,ativo=:ativo WHERE id=:id"; $stmt=$pdo->prepare($sql); $stmt->bindParam(':senha',$senha_hash); }
                    else { $sql="UPDATE usuarios_admin SET nome=:nome,email=:email,nivel_acesso=:nivel_acesso,ativo=:ativo WHERE id=:id"; $stmt=$pdo->prepare($sql); }
                    $stmt->bindParam(':nome',$nome); $stmt->bindParam(':email',$email); $stmt->bindParam(':nivel_acesso',$nivel_acesso_usuario); $stmt->bindParam(':ativo',$ativo); $stmt->bindParam(':id',$id);
                    if($stmt->execute()) $mensagem_sucesso="Usuário atualizado!"; else $mensagem_erro="Erro ao atualizar.";
                }
            } catch(PDOException $e){ $mensagem_erro="Erro: ".$e->getMessage(); }
        }
    } else if ($acao==='excluir'&&isset($_POST['id'])) {
        $id=filter_input(INPUT_POST,'id',FILTER_SANITIZE_NUMBER_INT);
        if(!$id||$id==$_SESSION['usuario_id']){ $mensagem_erro="Não é possível excluir seu próprio usuário."; }
        else {
            try { $sql="DELETE FROM usuarios_admin WHERE id=:id"; $stmt=$pdo->prepare($sql); $stmt->bindParam(':id',$id); if($stmt->execute()) $mensagem_sucesso="Usuário excluído!"; else $mensagem_erro="Erro ao excluir."; }
            catch(PDOException $e){ $mensagem_erro="Erro: ".$e->getMessage(); }
        }
    }
}

try { $sql="SELECT * FROM usuarios_admin ORDER BY nome"; $stmt=$pdo->prepare($sql); $stmt->execute(); $usuarios=$stmt->fetchAll(); }
catch(PDOException $e){ $mensagem_erro="Erro: ".$e->getMessage(); $usuarios=[]; }
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Usuários — Ouvidoria ASSEGO</title>
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
        <li class="nav-item"><a class="nav-link" href="manifestacoes.php"><i class="fas fa-comments"></i> Manifestações</a></li>
        <li class="nav-item"><a class="nav-link active" href="usuarios.php"><i class="fas fa-users"></i> Usuários</a></li>
        <li class="nav-item"><a class="nav-link" href="relatorios.php"><i class="fas fa-chart-bar"></i> Relatórios</a></li>
      </ul>
    </nav>

    <main class="col-lg-10 ms-sm-auto px-md-4 py-4 content-wrapper">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div class="page-header" style="margin-bottom:0">
          <h2><span class="ph-icon"><i class="fas fa-users"></i></span>Usuários</h2>
          <p><?php echo count($usuarios); ?> usuário(s) cadastrado(s)</p>
        </div>
        <button id="btnAddUser" class="btn btn-primary btn-sm"><i class="fas fa-user-plus me-1"></i> Novo Usuário</button>
      </div>

      <?php if(!empty($mensagem_sucesso)): ?><div class="alert alert-success mb-3"><i class="fas fa-check-circle"></i><?php echo $mensagem_sucesso; ?></div><?php endif; ?>
      <?php if(!empty($mensagem_erro)): ?><div class="alert alert-danger mb-3"><i class="fas fa-exclamation-circle"></i><?php echo $mensagem_erro; ?></div><?php endif; ?>

      <div class="card">
        <div class="card-body p-0">
          <?php if(count($usuarios)>0): ?>
          <div class="table-wrapper">
            <table class="table">
              <thead>
                <tr><th>ID</th><th>Nome</th><th class="d-none d-md-table-cell">E-mail</th><th class="d-none d-sm-table-cell">Nível</th><th>Status</th><th class="d-none d-md-table-cell">Último Acesso</th><th>Ações</th></tr>
              </thead>
              <tbody>
                <?php foreach($usuarios as $u): $niveis=['admin'=>['Admin','level-admin'],'ouvidor'=>['Ouvidor','level-ouvidor'],'analista'=>['Analista','level-analista']]; $nl=$niveis[$u['nivel_acesso']]??[$u['nivel_acesso'],'level-admin']; ?>
                <tr>
                  <td style="color:var(--muted);font-size:.78rem">#<?php echo $u['id']; ?></td>
                  <td style="font-weight:500"><?php echo htmlspecialchars($u['nome']); ?></td>
                  <td class="d-none d-md-table-cell" style="font-size:.83rem;color:var(--muted)"><?php echo htmlspecialchars($u['email']); ?></td>
                  <td class="d-none d-sm-table-cell"><span class="status-badge <?php echo $nl[1]; ?>"><?php echo $nl[0]; ?></span></td>
                  <td><span class="status-badge <?php echo $u['ativo']?'status-active':'status-inactive'; ?>"><?php echo $u['ativo']?'Ativo':'Inativo'; ?></span></td>
                  <td class="d-none d-md-table-cell" style="font-size:.8rem;color:var(--muted)"><?php echo $u['ultimo_acesso']?date('d/m/Y H:i',strtotime($u['ultimo_acesso'])):'Nunca acessou'; ?></td>
                  <td>
                    <?php if($u['id']!=$_SESSION['usuario_id']): ?>
                    <div class="d-flex gap-1">
                      <button class="btn btn-primary btn-sm btn-edit" data-id="<?php echo $u['id']; ?>" title="Editar"><i class="fas fa-edit"></i></button>
                      <button class="btn btn-sm btn-delete" style="background:#FEF2F2;color:#B91C1C;border:none" data-id="<?php echo $u['id']; ?>" title="Excluir"><i class="fas fa-trash-alt"></i></button>
                    </div>
                    <?php else: ?>
                    <span style="font-size:.75rem;color:var(--muted);font-style:italic">Você</span>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <?php else: ?><div class="empty-state"><i class="fas fa-users"></i><h4>Nenhum usuário</h4><p>Clique em "Novo Usuário" para adicionar.</p></div><?php endif; ?>
        </div>
      </div>
    </main>
  </div>
</div>

<!-- Modal Adicionar -->
<div class="modal fade" id="addUserModal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Novo Usuário</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form action="" method="POST">
      <input type="hidden" name="acao" value="adicionar">
      <div class="modal-body">
        <div class="mb-3"><label class="form-label">Nome Completo</label><input type="text" name="nome" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">E-mail</label><input type="email" name="email" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Senha</label><input type="password" name="senha" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Nível de Acesso</label><select name="nivel_acesso" class="form-select"><option value="admin">Administrador</option><option value="ouvidor">Ouvidor</option><option value="analista">Analista</option></select></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary btn-sm">Adicionar</button></div>
    </form>
  </div></div>
</div>

<!-- Modal Editar -->
<div class="modal fade" id="editUserModal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title"><i class="fas fa-user-edit me-2"></i>Editar Usuário</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form action="" method="POST">
      <input type="hidden" name="acao" value="atualizar"><input type="hidden" name="id" id="edit_id">
      <div class="modal-body">
        <div class="mb-3"><label class="form-label">Nome</label><input type="text" id="edit_nome" name="nome" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">E-mail</label><input type="email" id="edit_email" name="email" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Nova Senha (deixe em branco para manter)</label><input type="password" id="edit_senha" name="senha" class="form-control"></div>
        <div class="mb-3"><label class="form-label">Nível</label><select id="edit_nivel_acesso" name="nivel_acesso" class="form-select"><option value="admin">Administrador</option><option value="ouvidor">Ouvidor</option><option value="analista">Analista</option></select></div>
        <div class="form-check"><input type="checkbox" class="form-check-input" id="edit_ativo" name="ativo" checked><label class="form-check-label" for="edit_ativo">Usuário Ativo</label></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary btn-sm">Salvar</button></div>
    </form>
  </div></div>
</div>

<!-- Modal Excluir -->
<div class="modal fade" id="deleteUserModal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header" style="background:#B91C1C"><h5 class="modal-title text-white"><i class="fas fa-trash-alt me-2"></i>Excluir Usuário</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form action="" method="POST">
      <input type="hidden" name="acao" value="excluir"><input type="hidden" name="id" id="delete_id">
      <div class="modal-body"><p>Tem certeza que deseja excluir este usuário? Esta ação não pode ser desfeita.</p></div>
      <div class="modal-footer"><button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-sm" style="background:#B91C1C;color:#fff;border:none">Excluir</button></div>
    </form>
  </div></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php include '_js_sidebar.php'; ?>
<script>
document.addEventListener('DOMContentLoaded',function(){
    const addModal=new bootstrap.Modal(document.getElementById('addUserModal'));
    const editModal=new bootstrap.Modal(document.getElementById('editUserModal'));
    const delModal=new bootstrap.Modal(document.getElementById('deleteUserModal'));
    document.getElementById('btnAddUser').addEventListener('click',()=>addModal.show());
    document.querySelectorAll('.btn-edit').forEach(b=>b.addEventListener('click',function(){
        const row=this.closest('tr');
        document.getElementById('edit_id').value=this.dataset.id;
        document.getElementById('edit_nome').value=row.cells[1].textContent.trim();
        document.getElementById('edit_email').value=row.cells[2].textContent.trim();
        document.getElementById('edit_senha').value='';
        const lvl=row.cells[3].textContent.trim();
        const m={'Admin':'admin','Ouvidor':'ouvidor','Analista':'analista'};
        const sel=document.getElementById('edit_nivel_acesso');
        for(let o of sel.options) if(o.text===lvl||o.value===m[lvl]){o.selected=true;break;}
        document.getElementById('edit_ativo').checked=row.cells[4].textContent.trim()==='Ativo';
        editModal.show();
    }));
    document.querySelectorAll('.btn-delete').forEach(b=>b.addEventListener('click',function(){
        document.getElementById('delete_id').value=this.dataset.id;
        delModal.show();
    }));
});
</script>
</body>
</html>
