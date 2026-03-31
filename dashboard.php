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

if ($filtro_status !== 'todos') {
    $where_conditions[] = "status = :status";
    $params[':status'] = $filtro_status;
}

if ($filtro_tipo !== 'todos') {
    $where_conditions[] = "tipo_manifestacao = :tipo";
    $params[':tipo'] = $filtro_tipo;
}

if ($filtro_area !== 'todos') {
    $where_conditions[] = "area_relacionada = :area";
    $params[':area'] = $filtro_area;
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

$sql_count = "SELECT COUNT(*) as total FROM manifestacoes $where_clause";
$stmt_count = $pdo->prepare($sql_count);

foreach ($params as $key => $value) {
    $stmt_count->bindValue($key, $value);
}

$stmt_count->execute();
$total_registros = $stmt_count->fetch()['total'];
$total_paginas = ceil($total_registros / $registros_por_pagina);

$sql = "SELECT * FROM manifestacoes $where_clause ORDER BY data_criacao DESC LIMIT :offset, :limit";
$stmt = $pdo->prepare($sql);

foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}

$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':limit', $registros_por_pagina, PDO::PARAM_INT);
$stmt->execute();
$manifestacoes = $stmt->fetchAll();

$sql_contadores = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'pendente' THEN 1 ELSE 0 END) as pendentes,
                    SUM(CASE WHEN status = 'em_analise' THEN 1 ELSE 0 END) as em_analise,
                    SUM(CASE WHEN status = 'respondida' THEN 1 ELSE 0 END) as respondidas,
                    SUM(CASE WHEN status = 'arquivada' THEN 1 ELSE 0 END) as arquivadas,
                    SUM(CASE WHEN tipo_manifestacao = 'sugestao' THEN 1 ELSE 0 END) as sugestoes,
                    SUM(CASE WHEN tipo_manifestacao = 'critica' THEN 1 ELSE 0 END) as criticas,
                    SUM(CASE WHEN tipo_manifestacao = 'elogio' THEN 1 ELSE 0 END) as elogios,
                    SUM(CASE WHEN tipo_manifestacao = 'reclamacao' THEN 1 ELSE 0 END) as reclamacoes
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
    <title>Dashboard - Ouvidoria Assego</title>
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

        .card-title {
            color: #6c757d;
            font-size: 0.875rem;
        }

        .card-value {
            font-size: 1.5rem;
            font-weight: bold;
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

        .action-btn {
            display: inline-block;
            padding: 0.375rem 0.75rem;
            border-radius: 5px;
            font-size: 0.875rem;
            text-decoration: none;
            transition: background 0.3s ease;
        }

        .view-btn {
            background-color: #e3f2fd;
            color: #0d47a1;
        }

        .view-btn:hover {
            background-color: #bbdefb;
        }

        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }

        .table {
            margin-bottom: 0;
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
                min-width: 70px;
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
                            <a class="nav-link active" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manifestacoes.php">
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
                <h2 class="mb-4"><i class="fas fa-tachometer-alt"></i> Dashboard</h2>

                <!-- Stats Cards -->
                <div class="row">
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-title">Total de Manifestações</div>
                                <div class="card-value"><?php echo $contadores['total']; ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-title">Pendentes</div>
                                <div class="card-value"><?php echo $contadores['pendentes']; ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-title">Em Análise</div>
                                <div class="card-value"><?php echo $contadores['em_analise']; ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-title">Respondidas</div>
                                <div class="card-value"><?php echo $contadores['respondidas']; ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-title">Sugestões</div>
                                <div class="card-value"><?php echo $contadores['sugestoes']; ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-title">Críticas</div>
                                <div class="card-value"><?php echo $contadores['criticas']; ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-title">Elogios</div>
                                <div class="card-value"><?php echo $contadores['elogios']; ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="card-title">Reclamações</div>
                                <div class="card-value"><?php echo $contadores['reclamacoes']; ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form action="" method="GET" id="filtroForm">
                            <div class="row">
                                <div class="col-12 col-md-4 mb-3">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select" onchange="document.getElementById('filtroForm').submit()">
                                        <option value="todos" <?php echo $filtro_status === 'todos' ? 'selected' : ''; ?>>Todos os Status</option>
                                        <option value="pendente" <?php echo $filtro_status === 'pendente' ? 'selected' : ''; ?>>Pendente</option>
                                        <option value="em_analise" <?php echo $filtro_status === 'em_analise' ? 'selected' : ''; ?>>Em Análise</option>
                                        <option value="respondida" <?php echo $filtro_status === 'respondida' ? 'selected' : ''; ?>>Respondida</option>
                                        <option value="arquivada" <?php echo $filtro_status === 'arquivada' ? 'selected' : ''; ?>>Arquivada</option>
                                    </select>
                                </div>

                                <div class="col-12 col-md-4 mb-3">
                                    <label class="form-label">Tipo</label>
                                    <select name="tipo" class="form-select" onchange="document.getElementById('filtroForm').submit()">
                                        <option value="todos" <?php echo $filtro_tipo === 'todos' ? 'selected' : ''; ?>>Todos os Tipos</option>
                                        <option value="sugestao" <?php echo $filtro_tipo === 'sugestao' ? 'selected' : ''; ?>>Sugestão</option>
                                        <option value="critica" <?php echo $filtro_tipo === 'critica' ? 'selected' : ''; ?>>Crítica</option>
                                        <option value="elogio" <?php echo $filtro_tipo === 'elogio' ? 'selected' : ''; ?>>Elogio</option>
                                        <option value="reclamacao" <?php echo $filtro_tipo === 'reclamacao' ? 'selected' : ''; ?>>Reclamação</option>
                                    </select>
                                </div>

                                <div class="col-12 col-md-4 mb-3">
                                    <label class="form-label">Área</label>
                                    <select name="area" class="form-select" onchange="document.getElementById('filtroForm').submit()">
                                        <option value="todos" <?php echo $filtro_area === 'todos' ? 'selected' : ''; ?>>Todas as Áreas</option>
                                        <option value="administracao" <?php echo $filtro_area === 'administracao' ? 'selected' : ''; ?>>Administração</option>
                                        <option value="academia" <?php echo $filtro_area === 'academia' ? 'selected' : ''; ?>>Academia</option>
                                        <option value="assegomaissaude" <?php echo $filtro_area === 'assegomaissaude' ? 'selected' : ''; ?>>Assego mais saúde</option>
                                        <option value="atendimento" <?php echo $filtro_area === 'atendimento' ? 'selected' : ''; ?>>Atendimento</option>
                                        <option value="comercial" <?php echo $filtro_area === 'comercial' ? 'selected' : ''; ?>>Comercial</option>
                                        <option value="comunicacao" <?php echo $filtro_area === 'comunicacao' ? 'selected' : ''; ?>>Comunicação</option>
                                        <option value="convenio" <?php echo $filtro_area === 'convenio' ? 'selected' : ''; ?>>Convênio</option>
                                        <option value="eventos" <?php echo $filtro_area === 'eventos' ? 'selected' : ''; ?>>Eventos</option>
                                        <option value="financeiro" <?php echo $filtro_area === 'financeiro' ? 'selected' : ''; ?>>Financeiro</option>
                                        <option value="hotel" <?php echo $filtro_area === 'hotel' ? 'selected' : ''; ?>>Hotel</option>
                                        <option value="juridico" <?php echo $filtro_area === 'juridico' ? 'selected' : ''; ?>>Jurídico</option>
                                        <option value="limpeza" <?php echo $filtro_area === 'limpeza' ? 'selected' : ''; ?>>Limpeza</option>
                                        <option value="lazer" <?php echo $filtro_area === 'lazer' ? 'selected' : ''; ?>>Área de Lazer</option>
                                        <option value="manutencao" <?php echo $filtro_area === 'manutencao' ? 'selected' : ''; ?>>Manutenção</option>
                                        <option value="outros" <?php echo $filtro_area === 'outros' ? 'selected' : ''; ?>>Outros</option>
                                        <option value="parque" <?php echo $filtro_area === 'parque' ? 'selected' : ''; ?>>Parque aquático</option>
                                        <option value="presidencia" <?php echo $filtro_area === 'presidencia' ? 'selected' : ''; ?>>Presidência</option>
                                        <option value="recursoshumanos" <?php echo $filtro_area === 'recursoshumanos' ? 'selected' : ''; ?>>Recursos Humanos (RH)</option>
                                        <option value="restaurante" <?php echo $filtro_area === 'restaurante' ? 'selected' : ''; ?>>Restaurante</option>
                                        <option value="seguranca" <?php echo $filtro_area === 'seguranca' ? 'selected' : ''; ?>>Segurança</option>
                                        <option value="estacionamento" <?php echo $filtro_area === 'estacionamento' ? 'selected' : ''; ?>>Estacionamento</option>
                                        <option value="servicossociais" <?php echo $filtro_area === 'servicossociais' ? 'selected' : ''; ?>>Serviços Sociais</option>
                                        <option value="tecnologia" <?php echo $filtro_area === 'tecnologia' ? 'selected' : ''; ?>>Tecnologia</option>
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Table -->
                <div class="card">
                    <div class="card-body">
                        <?php if (count($manifestacoes) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Tipo</th>
                                            <th class="d-none d-md-table-cell">Área</th>
                                            <th class="d-none d-sm-table-cell">Data</th>
                                            <th>Status</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($manifestacoes as $manifestacao): ?>
                                            <tr>
                                                <td>#<?php echo $manifestacao['id']; ?></td>
                                                <td>
                                                    <?php
                                                    $tipos = [
                                                        'sugestao' => '<i class="fas fa-lightbulb"></i> <span class="d-none d-md-inline">Sugestão</span>',
                                                        'critica' => '<i class="fas fa-exclamation-circle"></i> <span class="d-none d-md-inline">Crítica</span>',
                                                        'elogio' => '<i class="fas fa-thumbs-up"></i> <span class="d-none d-md-inline">Elogio</span>',
                                                        'reclamacao' => '<i class="fas fa-thumbs-down"></i> <span class="d-none d-md-inline">Reclamação</span>'
                                                    ];
                                                    echo $tipos[$manifestacao['tipo_manifestacao']];
                                                    ?>
                                                </td>
                                                <td class="d-none d-md-table-cell"><?php echo ucfirst($manifestacao['area_relacionada']); ?></td>
                                                <td class="d-none d-sm-table-cell"><?php echo date('d/m/Y H:i', strtotime($manifestacao['data_criacao'])); ?></td>
                                                <td>
                                                    <?php
                                                    $status_classes = [
                                                        'pendente' => 'status-pendente',
                                                        'em_analise' => 'status-em_analise',
                                                        'respondida' => 'status-respondida',
                                                        'arquivada' => 'status-arquivada'
                                                    ];
                                                    $status_labels = [
                                                        'pendente' => 'Pendente',
                                                        'em_analise' => 'Em Análise',
                                                        'respondida' => 'Respondida',
                                                        'arquivada' => 'Arquivada'
                                                    ];
                                                    ?>
                                                    <span class="status-badge <?php echo $status_classes[$manifestacao['status']]; ?>">
                                                        <?php echo $status_labels[$manifestacao['status']]; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="visualizar_manifestacao.php?id=<?php echo $manifestacao['id']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-eye"></i> <span class="d-none d-md-inline">Ver</span>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <?php if ($total_paginas > 1): ?>
                                <div class="d-flex justify-content-center mt-4">
                                    <nav aria-label="Paginação de manifestações">
                                        <ul class="pagination">
                                            <?php if ($pagina_atual > 1): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?pagina=<?php echo $pagina_atual - 1; ?>&status=<?php echo $filtro_status; ?>&tipo=<?php echo $filtro_tipo; ?>&area=<?php echo $filtro_area; ?>" aria-label="Anterior">
                                                        <span aria-hidden="true">&laquo;</span>
                                                    </a>
                                                </li>
                                            <?php endif; ?>

                                            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                                <li class="page-item <?php echo $i == $pagina_atual ? 'active' : ''; ?>">
                                                    <a class="page-link" href="?pagina=<?php echo $i; ?>&status=<?php echo $filtro_status; ?>&tipo=<?php echo $filtro_tipo; ?>&area=<?php echo $filtro_area; ?>">
                                                        <?php echo $i; ?>
                                                    </a>
                                                </li>
                                            <?php endfor; ?>

                                            <?php if ($pagina_atual < $total_paginas): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?pagina=<?php echo $pagina_atual + 1; ?>&status=<?php echo $filtro_status; ?>&tipo=<?php echo $filtro_tipo; ?>&area=<?php echo $filtro_area; ?>" aria-label="Próximo">
                                                        <span aria-hidden="true">&raquo;</span>
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </nav>
                                </div>
                            <?php endif; ?>

                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <h3>Nenhuma manifestação encontrada</h3>
                                <p>Não existem manifestações que correspondam aos critérios de filtro selecionados.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
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
        });
    </script>
</body>

</html>