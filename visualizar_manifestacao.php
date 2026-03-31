<?php
date_default_timezone_set('America/Sao_Paulo');
require_once 'verificar_login.php';
require_once 'conexao.php';

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if (!$id) {
    header('Location: dashboard.php');
    exit;
}

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
        
        if ($stmt->execute()) {
            $mensagem_sucesso = "Manifestação atualizada com sucesso!";
        } else {
            $mensagem_erro = "Erro ao atualizar manifestação.";
        }
    } catch (PDOException $e) {
        $mensagem_erro = "Erro ao processar sua solicitação: " . $e->getMessage();
    }
}

try {
    $sql = "SELECT * FROM manifestacoes WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        header('Location: dashboard.php');
        exit;
    }
    
    $manifestacao = $stmt->fetch();
} catch (PDOException $e) {
    $mensagem_erro = "Erro ao buscar manifestação: " . $e->getMessage();
    $manifestacao = null;
}

$tipos = [
    'sugestao' => 'Sugestão',
    'critica' => 'Crítica',
    'elogio' => 'Elogio',
    'reclamacao' => 'Reclamação'
];

$status_labels = [
    'pendente' => 'Pendente',
    'em_analise' => 'Em Análise',
    'respondida' => 'Respondida',
    'arquivada' => 'Arquivada'
];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizar Manifestação - Ouvidoria Assego</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
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
        
        .status-pendente {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-em_analise {
            background-color: #cce5ff;
            color: #004085;
        }
        
        .status-respondida {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-arquivada {
            background-color: #e2e3e5;
            color: #383d41;
        }
        
        .manifestacao-tipo {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 600;
        }
        
        .manifestacao-tipo.sugestao {
            background-color: #e3f2fd;
            color: #0d47a1;
        }
        
        .manifestacao-tipo.critica {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .manifestacao-tipo.elogio {
            background-color: #d4edda;
            color: #155724;
        }
        
        .manifestacao-tipo.reclamacao {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .manifestacao-mensagem {
            background-color: #f8f9fa;
            padding: 1.5rem;
            border-radius: 5px;
            margin-bottom: 1.5rem;
        }
        
        .resposta-box {
            background-color: #f0f0f0;
            padding: 1.5rem;
            border-radius: 5px;
            border-left: 4px solid #000E72;
            margin-bottom: 1.5rem;
        }
        
        .info-label {
            font-size: 0.875rem;
            color: #6c757d;
            margin-bottom: 0.25rem;
        }
        
        .info-value {
            font-weight: 600;
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
        }
    </style>
</head>
<body>
    
    <nav class="navbar navbar-expand-lg main-header">
        <div class="container-fluid">
           
            <button class="btn btn-link text-white d-lg-none me-2" id="sidebarToggle" type="button">
                <i class="fas fa-bars"></i>
            </button>
            
            
            <a class="navbar-brand text-white d-flex align-items-center" href="#">
                <img src="https://assego.com.br/wp-content/uploads/2023/11/logo.png-mini2.png" alt="Assego Logo" class="me-2">
                <span>Ouvidoria Assego</span>
            </a>
            
            
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
                            <a class="nav-link active" href="manifestacoes.php">
                                <i class="fas fa-comments"></i> Manifestações
                            </a>
                        </li>
                        <?php if (isset($nivel_acesso) && $nivel_acesso === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="usuarios.php">
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
                    <div>
                        <a href="dashboard.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Voltar
                        </a>
                    </div>
                    <h2 class="mb-0">Manifestação #<?php echo $id; ?></h2>
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

                <?php if ($manifestacao): ?>
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="manifestacao-tipo <?php echo $manifestacao['tipo_manifestacao']; ?>">
                            <?php 
                            $icones = [
                                'sugestao' => '<i class="fas fa-lightbulb"></i>',
                                'critica' => '<i class="fas fa-exclamation-circle"></i>',
                                'elogio' => '<i class="fas fa-thumbs-up"></i>',
                                'reclamacao' => '<i class="fas fa-thumbs-down"></i>'
                            ];
                            echo $icones[$manifestacao['tipo_manifestacao']] . ' ' . $tipos[$manifestacao['tipo_manifestacao']];
                            ?>
                        </div>
                        <span class="status-badge status-<?php echo $manifestacao['status']; ?>">
                            <?php echo $status_labels[$manifestacao['status']]; ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-3 mb-3">
                                <div class="info-label">Área Relacionada</div>
                                <div class="info-value"><?php echo ucfirst($manifestacao['area_relacionada']); ?></div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="info-label">Data de Envio</div>
                                <div class="info-value"><?php echo date('d/m/Y H:i', strtotime($manifestacao['data_criacao'])); ?></div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="info-label">Manifestação</div>
                                <div class="info-value"><?php echo $manifestacao['anonimo'] ? 'Anônima' : 'Identificada'; ?></div>
                            </div>
                            <?php if (!$manifestacao['anonimo']): ?>
                            <div class="col-md-3 mb-3">
                                <div class="info-label">Nome</div>
                                <div class="info-value"><?php echo htmlspecialchars($manifestacao['nome']); ?></div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="info-label">E-mail</div>
                                <div class="info-value"><?php echo htmlspecialchars($manifestacao['email']); ?></div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <h4 class="mb-3">Mensagem</h4>
                        <div class="manifestacao-mensagem">
                            <?php echo nl2br(htmlspecialchars($manifestacao['mensagem'])); ?>
                        </div>

                        <?php if (isset($manifestacao['imagem_path']) && !empty($manifestacao['imagem_path']) || isset($manifestacao['imagem_blob']) && !empty($manifestacao['imagem_blob'])): ?>
                        <h4 class="mb-3 mt-4">Imagem Anexada</h4>
                        <div class="mb-4">
                            <img src="exibir_imagem.php?id=<?php echo $manifestacao['id']; ?>" alt="Imagem da manifestação" class="img-fluid rounded">
                        </div>
                        <?php endif; ?>

                        <?php if (isset($manifestacao['resposta']) && !empty($manifestacao['resposta'])): ?>
                        <h4 class="mb-3 mt-4">Resposta</h4>
                        <div class="resposta-box">
                            <div class="d-flex justify-content-between mb-2">
                                <strong>Respondido por: <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></strong>
                                <span class="text-muted small"><?php echo date('d/m/Y H:i', strtotime($manifestacao['data_atualizacao'])); ?></span>
                            </div>
                            <?php echo nl2br(htmlspecialchars($manifestacao['resposta'])); ?>
                        </div>
                        <?php endif; ?>

                        <h4 class="mb-3 mt-4"><?php echo isset($manifestacao['resposta']) && !empty($manifestacao['resposta']) ? 'Atualizar Resposta' : 'Responder Manifestação'; ?></h4>
                        <form action="" method="POST">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status da Manifestação</label>
                                <select id="status" name="status" class="form-select">
                                    <option value="pendente" <?php echo $manifestacao['status'] === 'pendente' ? 'selected' : ''; ?>>Pendente</option>
                                    <option value="em_analise" <?php echo $manifestacao['status'] === 'em_analise' ? 'selected' : ''; ?>>Em Análise</option>
                                    <option value="respondida" <?php echo $manifestacao['status'] === 'respondida' ? 'selected' : ''; ?>>Respondida</option>
                                    <option value="arquivada" <?php echo $manifestacao['status'] === 'arquivada' ? 'selected' : ''; ?>>Arquivada</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="resposta" class="form-label">Resposta</label>
                                <textarea id="resposta" name="resposta" class="form-control" rows="6"><?php echo htmlspecialchars($manifestacao['resposta']); ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> <?php echo isset($manifestacao['resposta']) && !empty($manifestacao['resposta']) ? 'Atualizar' : 'Enviar Resposta'; ?>
                            </button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
          
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                    overlay.classList.toggle('show');
                });
            }
            
           
            if (overlay) {
                overlay.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    overlay.classList.remove('show');
                });
            }
            
           
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
        });
    </script>
</body>
</html>