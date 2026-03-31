<?php
require_once 'verificar_login.php';

if ($nivel_acesso !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

require_once 'conexao.php';

$mensagem_sucesso = '';
$mensagem_erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    $acao = $_POST['acao'];
    
    // Add novo usuario 
    if ($acao === 'adicionar') {
        $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $senha = $_POST['senha'];
        $nivel_acesso_usuario = filter_input(INPUT_POST, 'nivel_acesso', FILTER_SANITIZE_STRING);
        
        if (empty($nome) || empty($email) || empty($senha) || empty($nivel_acesso_usuario)) {
            $mensagem_erro = "Todos os campos são obrigatórios.";
        } else {
            try {
                // verifica email 
                $sql_check = "SELECT COUNT(*) as count FROM usuarios_admin WHERE email = :email";
                $stmt_check = $pdo->prepare($sql_check);
                $stmt_check->bindParam(':email', $email);
                $stmt_check->execute();
                $email_existe = $stmt_check->fetch()['count'] > 0;
                
                if ($email_existe) {
                    $mensagem_erro = "Este e-mail já está cadastrado.";
                } else {
                    // Hash da senha
                    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                    
                    $sql = "INSERT INTO usuarios_admin (nome, email, senha, nivel_acesso) VALUES (:nome, :email, :senha, :nivel_acesso)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':nome', $nome);
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':senha', $senha_hash);
                    $stmt->bindParam(':nivel_acesso', $nivel_acesso_usuario);
                    
                    if ($stmt->execute()) {
                        $mensagem_sucesso = "Usuário adicionado com sucesso!";
                    } else {
                        $mensagem_erro = "Erro ao adicionar usuário.";
                    }
                }
            } catch (PDOException $e) {
                $mensagem_erro = "Erro ao processar sua solicitação: " . $e->getMessage();
            }
        }
    }
    
    //ataulizar usuario existente 
    else if ($acao === 'atualizar' && isset($_POST['id'])) {
        $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
        $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $nivel_acesso_usuario = filter_input(INPUT_POST, 'nivel_acesso', FILTER_SANITIZE_STRING);
        $ativo = isset($_POST['ativo']) ? 1 : 0;
        
        // ve se o id é valido 
        if (!$id || $id == $_SESSION['usuario_id']) {
            $mensagem_erro = "Não é possível editar seu próprio usuário por esta interface.";
        } else {
            try {
                // verifica email
                $sql_check = "SELECT COUNT(*) as count FROM usuarios_admin WHERE email = :email AND id != :id";
                $stmt_check = $pdo->prepare($sql_check);
                $stmt_check->bindParam(':email', $email);
                $stmt_check->bindParam(':id', $id);
                $stmt_check->execute();
                $email_existe = $stmt_check->fetch()['count'] > 0;
                
                if ($email_existe) {
                    $mensagem_erro = "Este e-mail já está sendo usado por outro usuário.";
                } else {
                    // Se uma nova senha foi fornecida
                    if (!empty($_POST['senha'])) {
                        $senha = $_POST['senha'];
                        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                        
                        $sql = "UPDATE usuarios_admin SET nome = :nome, email = :email, senha = :senha, nivel_acesso = :nivel_acesso, ativo = :ativo WHERE id = :id";
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(':nome', $nome);
                        $stmt->bindParam(':email', $email);
                        $stmt->bindParam(':senha', $senha_hash);
                        $stmt->bindParam(':nivel_acesso', $nivel_acesso_usuario);
                        $stmt->bindParam(':ativo', $ativo);
                        $stmt->bindParam(':id', $id);
                    } else {
                        $sql = "UPDATE usuarios_admin SET nome = :nome, email = :email, nivel_acesso = :nivel_acesso, ativo = :ativo WHERE id = :id";
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(':nome', $nome);
                        $stmt->bindParam(':email', $email);
                        $stmt->bindParam(':nivel_acesso', $nivel_acesso_usuario);
                        $stmt->bindParam(':ativo', $ativo);
                        $stmt->bindParam(':id', $id);
                    }
                    
                    if ($stmt->execute()) {
                        $mensagem_sucesso = "Usuário atualizado com sucesso!";
                    } else {
                        $mensagem_erro = "Erro ao atualizar usuário.";
                    }
                }
            } catch (PDOException $e) {
                $mensagem_erro = "Erro ao processar sua solicitação: " . $e->getMessage();
            }
        }
    }
    
    // excluir usuario 
    else if ($acao === 'excluir' && isset($_POST['id'])) {
        $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
        
        if (!$id || $id == $_SESSION['usuario_id']) {
            $mensagem_erro = "Não é possível excluir seu próprio usuário.";
        } else {
            try {
                $sql = "DELETE FROM usuarios_admin WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $id);
                
                if ($stmt->execute()) {
                    $mensagem_sucesso = "Usuário excluído com sucesso!";
                } else {
                    $mensagem_erro = "Erro ao excluir usuário.";
                }
            } catch (PDOException $e) {
                $mensagem_erro = "Erro ao processar sua solicitação: " . $e->getMessage();
            }
        }
    }
}

// procurar todos os usuarios 
try {
    $sql = "SELECT * FROM usuarios_admin ORDER BY nome";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $usuarios = $stmt->fetchAll();
} catch (PDOException $e) {
    $mensagem_erro = "Erro ao buscar usuários: " . $e->getMessage();
    $usuarios = [];
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Usuários - Ouvidoria Assego</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            color: #000E72;
            background-color: #f5f5f5;
            overflow-x: hidden;
        }
        
        .main-header {
            background: linear-gradient(to right, #000E72, #FFDF00);
            color: white;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        
        .navbar-brand img {
            height: 40px;
        }
        
        .sidebar {
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            min-height: calc(100vh - 56px);
            transition: all 0.3s;
        }
        
        .sidebar .nav-link {
            color: #000E72;
            border-radius: 5px;
            margin-bottom: 5px;
            transition: background 0.3s;
        }
        
        .sidebar .nav-link:hover, 
        .sidebar .nav-link.active {
            background-color: #f0f0f0;
        }
        
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .status-active {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-inactive {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .action-btn {
            display: inline-block;
            padding: 0.375rem 0.75rem;
            border-radius: 5px;
            font-size: 0.875rem;
            text-decoration: none;
            transition: background 0.3s ease;
            margin-right: 0.25rem;
            cursor: pointer;
            border: none;
        }
        
        .edit-btn {
            background-color: #e3f2fd;
            color: #0d47a1;
        }
        
        .delete-btn {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .edit-btn:hover {
            background-color: #bbdefb;
        }
        
        .delete-btn:hover {
            background-color: #f5c6cb;
        }
        
        .modal-header {
            background-color: #000E72;
            color: white;
        }
        
        .modal-content {
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border: none;
        }
        
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }
        
        /* Alterações para sidebar em modo mobile */
        @media (max-width: 991.98px) {
            .sidebar {
                position: fixed;
                top: 56px;
                left: -250px;
                width: 250px;
                z-index: 1030;
                height: calc(100vh - 56px);
                overflow-y: auto;
                padding-top: 15px;
            }
            
            .sidebar.show {
                left: 0;
            }
            
            .content-wrapper {
                width: 100%;
            }
            
            .overlay {
                display: none;
                position: fixed;
                top: 56px;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.4);
                z-index: 1020;
            }
            
            .overlay.show {
                display: block;
            }
            
            .table-responsive {
                font-size: 14px;
            }
        }

        @media (max-width: 576px) {
            /* Ajustes para tabelas em telas muito pequenas */
            .table {
                font-size: 13px;
            }
            
            /* Garantir que os botões tenham largura mínima suficiente */
            .btn-sm {
                padding: 0.25rem 0.5rem;
                border-radius: 3px;
                margin: 2px;
            }
            
            /* Manter tamanho consistente para ícones */
            .fas {
                font-size: 14px;
            }
            
            /* Ajustar badges e status para caber em células menores */
            .status-badge {
                font-size: 10px;
                padding: 2px 5px;
            }
            
            /* Garantir que as colunas de ID fiquem mais estreitas */
            table th:first-child,
            table td:first-child {
                max-width: 40px;
                width: 40px;
            }
            
            /* Garantir que a coluna de ações tenha largura suficiente */
            table th:last-child,
            table td:last-child {
                min-width: 80px;
            }
        }

        /* Adicionar uma barra de rolagem visível na tabela */
        .table-responsive {
            border-radius: 10px;
            overflow-x: auto;
            scrollbar-width: thin;
            scrollbar-color: #c1c1c1 #f5f5f5;
        }

        /* Ajustar scrollbars para Chrome/Safari */
        .table-responsive::-webkit-scrollbar {
            height: 8px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: #f5f5f5;
            border-radius: 4px;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background-color: #c1c1c1;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <!-- Navbar principal -->
    <nav class="navbar navbar-expand-lg main-header">
        <div class="container-fluid">
            <!-- Toggle button for sidebar -->
            <button class="btn btn-link text-white d-lg-none me-2" id="sidebarToggle" type="button">
                <i class="fas fa-bars"></i>
            </button>
            
            <!-- Logo and Brand -->
            <a class="navbar-brand text-white d-flex align-items-center" href="#">
                <img src="https://assego.com.br/wp-content/uploads/2023/11/logo.png-mini2.png" alt="Assego Logo" class="me-2">
                <span>Ouvidoria Assego</span>
            </a>
            
            <!-- User Info -->
            <div class="ms-auto d-flex align-items-center">
                <span class="text-white me-3 d-none d-sm-block">Olá, <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></span>
                <a href="logout.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-sign-out-alt"></i> <span class="d-none d-sm-inline">Sair</span>
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Overlay for mobile -->
            <div class="overlay" id="sidebarOverlay"></div>
            
            <!-- Sidebar -->
            <nav class="col-lg-2 d-lg-block sidebar" id="sidebar">
                <div class="position-sticky">
                    <ul class="nav flex-column mt-3">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manifestacoes.php">
                                <i class="fas fa-comments"></i> Manifestações
                            </a>
                        </li>
                        <?php if ($nivel_acesso === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link active" href="usuarios.php">
                                <i class="fas fa-users"></i> Usuários
                            </a>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="relatorios.php">
                                <i class="fas fa-chart-bar"></i> Relatórios
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-lg-10 ms-sm-auto px-md-4 py-4 content-wrapper">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-4">
                    <h2><i class="fas fa-users"></i> Gerenciar Usuários</h2>
                    <button id="btnAddUser" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> <span class="d-none d-sm-inline">Adicionar Usuário</span>
                    </button>
                </div>

                <?php if (!empty($mensagem_sucesso)): ?>
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle"></i> <?php echo $mensagem_sucesso; ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($mensagem_erro)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $mensagem_erro; ?>
                </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <?php if (count($usuarios) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nome</th>
                                        <th class="d-none d-md-table-cell">E-mail</th>
                                        <th class="d-none d-sm-table-cell">Nível</th>
                                        <th>Status</th>
                                        <th class="d-none d-md-table-cell">Último Acesso</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($usuarios as $usuario): ?>
                                    <tr>
                                        <td>#<?php echo $usuario['id']; ?></td>
                                        <td><?php echo htmlspecialchars($usuario['nome']); ?></td>
                                        <td class="d-none d-md-table-cell"><?php echo htmlspecialchars($usuario['email']); ?></td>
                                        <td class="d-none d-sm-table-cell">
                                            <?php 
                                            $niveis = [
                                                'admin' => 'Admin',
                                                'ouvidor' => 'Ouvidor',
                                                'analista' => 'Analista'
                                            ];
                                            echo $niveis[$usuario['nivel_acesso']]; 
                                            ?>
                                        </td>
                                        <td>
                                            <span class="status-badge <?php echo $usuario['ativo'] ? 'status-active' : 'status-inactive'; ?>">
                                                <?php echo $usuario['ativo'] ? 'Ativo' : 'Inativo'; ?>
                                            </span>
                                        </td>
                                        <td class="d-none d-md-table-cell">
                                            <?php echo isset($usuario['ultimo_acesso']) && $usuario['ultimo_acesso'] ? date('d/m/Y H:i', strtotime($usuario['ultimo_acesso'])) : 'Nunca acessou'; ?>
                                        </td>
                                        <td>
                                            <?php if ($usuario['id'] != $_SESSION['usuario_id']): ?>
                                            <div class="d-flex flex-nowrap">
                                                <button class="btn btn-sm btn-primary me-1 btn-edit" data-id="<?php echo $usuario['id']; ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger btn-delete" data-id="<?php echo $usuario['id']; ?>">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                            <?php else: ?>
                                            <span class="badge bg-secondary">Seu usuário</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-users fa-3x mb-3"></i>
                            <h3>Nenhum usuário encontrado</h3>
                            <p>Não existem usuários cadastrados no sistema.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal Adicionar Usuário -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel"><i class="fas fa-user-plus"></i> Adicionar Usuário</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="POST">
                    <input type="hidden" name="acao" value="adicionar">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome Completo</label>
                            <input type="text" id="nome" name="nome" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">E-mail</label>
                            <input type="email" id="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="senha" class="form-label">Senha</label>
                            <input type="password" id="senha" name="senha" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="nivel_acesso" class="form-label">Nível de Acesso</label>
                            <select id="nivel_acesso" name="nivel_acesso" class="form-select" required>
                                <option value="admin">Administrador</option>
                                <option value="ouvidor">Ouvidor</option>
                                <option value="analista">Analista</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Adicionar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Usuário -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel"><i class="fas fa-user-edit"></i> Editar Usuário</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="POST">
                    <input type="hidden" name="acao" value="atualizar">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_nome" class="form-label">Nome Completo</label>
                            <input type="text" id="edit_nome" name="nome" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_email" class="form-label">E-mail</label>
                            <input type="email" id="edit_email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_senha" class="form-label">Nova Senha (deixe em branco para manter a atual)</label>
                            <input type="password" id="edit_senha" name="senha" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="edit_nivel_acesso" class="form-label">Nível de Acesso</label>
                            <select id="edit_nivel_acesso" name="nivel_acesso" class="form-select" required>
                                <option value="admin">Administrador</option>
                                <option value="ouvidor">Ouvidor</option>
                                <option value="analista">Analista</option>
                            </select>
                        </div>
                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="edit_ativo" name="ativo" checked>
                            <label class="form-check-label" for="edit_ativo">Usuário Ativo</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Atualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Excluir Usuário -->
    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteUserModalLabel"><i class="fas fa-trash-alt"></i> Excluir Usuário</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="POST">
                    <input type="hidden" name="acao" value="excluir">
                    <input type="hidden" name="id" id="delete_id">
                    <div class="modal-body">
                        <p>Tem certeza que deseja excluir este usuário? Esta ação não pode ser desfeita.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Excluir</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar toggle for mobile
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                    overlay.classList.toggle('show');
                });
            }
            
            // Close sidebar when clicking on overlay
            if (overlay) {
                overlay.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    overlay.classList.remove('show');
                });
            }
            
            // Active link
            const currentPage = window.location.pathname.split('/').pop();
            const navLinks = document.querySelectorAll('.nav-link');
            
            navLinks.forEach(link => {
                const href = link.getAttribute('href');
                if (href === currentPage) {
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            });
            
            // Inicializa os modais do Bootstrap
            const addUserModal = new bootstrap.Modal(document.getElementById('addUserModal'));
            const editUserModal = new bootstrap.Modal(document.getElementById('editUserModal'));
            const deleteUserModal = new bootstrap.Modal(document.getElementById('deleteUserModal'));
            
            // Botão para abrir modal de adicionar usuário
            const btnAddUser = document.getElementById('btnAddUser');
            if (btnAddUser) {
                btnAddUser.addEventListener('click', function() {
                    addUserModal.show();
                });
            }
            
            // Botões de editar usuário
            const btnsEdit = document.querySelectorAll('.btn-edit');
            btnsEdit.forEach(btn => {
                btn.addEventListener('click', function() {
                    const userId = this.getAttribute('data-id');
                    
                    // Obter dados da linha da tabela
                    const userRow = this.closest('tr');
                    const userName = userRow.cells[1].textContent;
                    const userEmail = userRow.cells[2].textContent;
                    const userRole = userRow.cells[3].textContent.trim();
                    const userActive = userRow.cells[4].textContent.trim() === 'Ativo';
                    
                    // Preencher o modal com os dados do usuário
                    document.getElementById('edit_id').value = userId;
                    document.getElementById('edit_nome').value = userName;
                    document.getElementById('edit_email').value = userEmail;
                    document.getElementById('edit_senha').value = '';
                    
                    // Selecionar o nível de acesso correto
                    const roleSelect = document.getElementById('edit_nivel_acesso');
                    let roleFound = false;
                    
                    for(let i = 0; i < roleSelect.options.length; i++) {
                        if(roleSelect.options[i].text === userRole) {
                            roleSelect.selectedIndex = i;
                            roleFound = true;
                            break;
                        }
                    }
                    
                    // Se não encontrou exatamente (por causa da abreviação em telas pequenas), tenta encontrar parcialmente
                    if (!roleFound) {
                        const roles = {
                            'Admin': 'admin',
                            'Ouvidor': 'ouvidor',
                            'Analista': 'analista'
                        };
                        
                        for (const [key, value] of Object.entries(roles)) {
                            if (userRole.includes(key)) {
                                for(let i = 0; i < roleSelect.options.length; i++) {
                                    if(roleSelect.options[i].value === value) {
                                        roleSelect.selectedIndex = i;
                                        break;
                                    }
                                }
                                break;
                            }
                        }
                    }
                    
                    // Definir checkbox de ativo
                    document.getElementById('edit_ativo').checked = userActive;
                    
                    editUserModal.show();
                });
            });
            
            // Botões de excluir usuário
            const btnsDelete = document.querySelectorAll('.btn-delete');
            btnsDelete.forEach(btn => {
                btn.addEventListener('click', function() {
                    const userId = this.getAttribute('data-id');
                    document.getElementById('delete_id').value = userId;
                    deleteUserModal.show();
                });
            });
        });
    </script>
</body>
</html>