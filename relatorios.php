<?php
setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
date_default_timezone_set('America/Sao_Paulo');
date_default_timezone_set('America/Sao_Paulo');
require_once 'verificar_login.php';
require_once 'conexao.php';

$periodo = isset($_GET['periodo']) ? $_GET['periodo'] : 'mensal';
$ano = isset($_GET['ano']) ? (int)$_GET['ano'] : date('Y');
$mes = isset($_GET['mes']) ? (int)$_GET['mes'] : date('m');

// pegar anos para o filtro 
try {
    $sql_anos = "SELECT DISTINCT YEAR(data_criacao) as ano FROM manifestacoes ORDER BY ano DESC";
    $stmt_anos = $pdo->prepare($sql_anos);
    $stmt_anos->execute();
    $anos_disponiveis = $stmt_anos->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $anos_disponiveis = [date('Y')];
}

//pegar dados para o periodo seleionado
$dados_por_tipo = [];
$dados_por_area = [];
$dados_por_status = [];
$dados_por_tempo = [];

try {
    if ($periodo === 'mensal') {
        
        // dados por tipo para o mes selecionado
        $sql_tipo = "SELECT 
                        tipo_manifestacao, 
                        COUNT(*) as total  
                    FROM manifestacoes 
                    WHERE YEAR(data_criacao) = :ano AND MONTH(data_criacao) = :mes 
                    GROUP BY tipo_manifestacao";
        $stmt_tipo = $pdo->prepare($sql_tipo);
        $stmt_tipo->bindParam(':ano', $ano, PDO::PARAM_INT);
        $stmt_tipo->bindParam(':mes', $mes, PDO::PARAM_INT);
        $stmt_tipo->execute();
        $dados_por_tipo = $stmt_tipo->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // dados por area para o mes selecionado
        $sql_area = "SELECT 
                        area_relacionada, 
                        COUNT(*) as total 
                    FROM manifestacoes 
                    WHERE YEAR(data_criacao) = :ano AND MONTH(data_criacao) = :mes 
                    GROUP BY area_relacionada";
        $stmt_area = $pdo->prepare($sql_area);
        $stmt_area->bindParam(':ano', $ano, PDO::PARAM_INT);
        $stmt_area->bindParam(':mes', $mes, PDO::PARAM_INT);
        $stmt_area->execute();
        $dados_por_area = $stmt_area->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // dados por status para o mes selecionado
        $sql_status = "SELECT 
                        status, 
                        COUNT(*) as total 
                    FROM manifestacoes 
                    WHERE YEAR(data_criacao) = :ano AND MONTH(data_criacao) = :mes 
                    GROUP BY status";
        $stmt_status = $pdo->prepare($sql_status);
        $stmt_status->bindParam(':ano', $ano, PDO::PARAM_INT);
        $stmt_status->bindParam(':mes', $mes, PDO::PARAM_INT);
        $stmt_status->execute();
        $dados_por_status = $stmt_status->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // dados por dia do mes
        $sql_tempo = "SELECT 
                        DAY(data_criacao) as dia, 
                        COUNT(*) as total 
                    FROM manifestacoes 
                    WHERE YEAR(data_criacao) = :ano AND MONTH(data_criacao) = :mes 
                    GROUP BY dia
                    ORDER BY dia";
        $stmt_tempo = $pdo->prepare($sql_tempo);
        $stmt_tempo->bindParam(':ano', $ano, PDO::PARAM_INT);
        $stmt_tempo->bindParam(':mes', $mes, PDO::PARAM_INT);
        $stmt_tempo->execute();
        $dados_por_tempo = $stmt_tempo->fetchAll(PDO::FETCH_KEY_PAIR);
        
        //  total de manifestacoes no mes
        $sql_total = "SELECT COUNT(*) as total FROM manifestacoes WHERE YEAR(data_criacao) = :ano AND MONTH(data_criacao) = :mes";
        $stmt_total = $pdo->prepare($sql_total);
        $stmt_total->bindParam(':ano', $ano, PDO::PARAM_INT);
        $stmt_total->bindParam(':mes', $mes, PDO::PARAM_INT);
        $stmt_total->execute();
        $total_manifestacoes = $stmt_total->fetch()['total'];
        
        $periodo_texto = date('F Y', mktime(0, 0, 0, $mes, 1, $ano));
    } else {
        // dados por ano 
        $sql_tipo = "SELECT 
                        tipo_manifestacao, 
                        COUNT(*) as total 
                    FROM manifestacoes 
                    WHERE YEAR(data_criacao) = :ano 
                    GROUP BY tipo_manifestacao";
        $stmt_tipo = $pdo->prepare($sql_tipo);
        $stmt_tipo->bindParam(':ano', $ano, PDO::PARAM_INT);
        $stmt_tipo->execute();
        $dados_por_tipo = $stmt_tipo->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // dados por area sobre o ano selecionado 
        $sql_area = "SELECT 
                        area_relacionada, 
                        COUNT(*) as total 
                    FROM manifestacoes 
                    WHERE YEAR(data_criacao) = :ano 
                    GROUP BY area_relacionada";
        $stmt_area = $pdo->prepare($sql_area);
        $stmt_area->bindParam(':ano', $ano, PDO::PARAM_INT);
        $stmt_area->execute();
        $dados_por_area = $stmt_area->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // status pode ano selecionado
        $sql_status = "SELECT 
                        status, 
                        COUNT(*) as total 
                    FROM manifestacoes 
                    WHERE YEAR(data_criacao) = :ano 
                    GROUP BY status";
        $stmt_status = $pdo->prepare($sql_status);
        $stmt_status->bindParam(':ano', $ano, PDO::PARAM_INT);
        $stmt_status->execute();
        $dados_por_status = $stmt_status->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // mes por ano selecionado 
        $sql_tempo = "SELECT 
                        MONTH(data_criacao) as mes, 
                        COUNT(*) as total 
                    FROM manifestacoes 
                    WHERE YEAR(data_criacao) = :ano 
                    GROUP BY mes
                    ORDER BY mes";
        $stmt_tempo = $pdo->prepare($sql_tempo);
        $stmt_tempo->bindParam(':ano', $ano, PDO::PARAM_INT);
        $stmt_tempo->execute();
        $dados_por_tempo = $stmt_tempo->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // manifestacoes ano selecionado
        $sql_total = "SELECT COUNT(*) as total FROM manifestacoes WHERE YEAR(data_criacao) = :ano";
        $stmt_total = $pdo->prepare($sql_total);
        $stmt_total->bindParam(':ano', $ano, PDO::PARAM_INT);
        $stmt_total->execute();
        $total_manifestacoes = $stmt_total->fetch()['total'];
        
        $periodo_texto = $ano;
    }
} catch (PDOException $e) {
    $erro = "Erro ao buscar dados para relatórios: " . $e->getMessage();
}

// preparar dados para os graficos
$labels_tipo = [];
$data_tipo = [];
$colors_tipo = [
    'sugestao' => 'rgba(54, 162, 235, 0.8)',
    'critica' => 'rgba(255, 159, 64, 0.8)',
    'elogio' => 'rgba(75, 192, 192, 0.8)',
    'reclamacao' => 'rgba(255, 99, 132, 0.8)'
];

$labels_tipo_pt = [
    'sugestao' => 'Sugestão',
    'critica' => 'Crítica',
    'elogio' => 'Elogio',
    'reclamacao' => 'Reclamação'
];

foreach ($labels_tipo_pt as $key => $label) {
    $labels_tipo[] = $label;
    $data_tipo[] = isset($dados_por_tipo[$key]) ? $dados_por_tipo[$key] : 0;
}

$labels_status = [];
$data_status = [];
$colors_status = [
    'pendente' => 'rgba(255, 159, 64, 0.8)',
    'em_analise' => 'rgba(54, 162, 235, 0.8)',
    'respondida' => 'rgba(75, 192, 192, 0.8)',
    'arquivada' => 'rgba(201, 203, 207, 0.8)'
];

$labels_status_pt = [
    'pendente' => 'Pendente',
    'em_analise' => 'Em Análise',
    'respondida' => 'Respondida',
    'arquivada' => 'Arquivada'
];

foreach ($labels_status_pt as $key => $label) {
    $labels_status[] = $label;
    $data_status[] = isset($dados_por_status[$key]) ? $dados_por_status[$key] : 0;
}

$labels_area = [];
$data_area = [];
$background_colors = [
    'rgba(54, 162, 235, 0.8)',
    'rgba(75, 192, 192, 0.8)',
    'rgba(255, 99, 132, 0.8)',
    'rgba(255, 159, 64, 0.8)',
    'rgba(153, 102, 255, 0.8)',
    'rgba(255, 205, 86, 0.8)',
    'rgba(201, 203, 207, 0.8)',
    'rgba(255, 99, 71, 0.8)',
    'rgba(138, 43, 226, 0.8)'
];

$i = 0;
foreach ($dados_por_area as $area => $total) {
    $labels_area[] = ucfirst($area);
    $data_area[] = $total;
    $i++;
}

// grafico de tempo
$labels_tempo = [];
$data_tempo = [];

if ($periodo === 'mensal') {
    $dias_no_mes = cal_days_in_month(CAL_GREGORIAN, $mes, $ano);
    for ($dia = 1; $dia <= $dias_no_mes; $dia++) {
        $labels_tempo[] = $dia;
        $data_tempo[] = isset($dados_por_tempo[$dia]) ? $dados_por_tempo[$dia] : 0;
    }
} else {
    $meses = [
        1 => 'Jan', 2 => 'Fev', 3 => 'Mar', 4 => 'Abr', 5 => 'Mai', 6 => 'Jun',
        7 => 'Jul', 8 => 'Ago', 9 => 'Set', 10 => 'Out', 11 => 'Nov', 12 => 'Dez'
    ];
    
    for ($m = 1; $m <= 12; $m++) {
        $labels_tempo[] = $meses[$m];
        $data_tempo[] = isset($dados_por_tempo[$m]) ? $dados_por_tempo[$m] : 0;
    }
}

// JSON para java script 
$chart_data = [
    'tipo' => [
        'labels' => $labels_tipo,
        'data' => $data_tipo,
        'colors' => array_values($colors_tipo)
    ],
    'status' => [
        'labels' => $labels_status,
        'data' => $data_status,
        'colors' => array_values($colors_status)
    ],
    'area' => [
        'labels' => $labels_area,
        'data' => $data_area,
        'colors' => array_slice($background_colors, 0, count($labels_area))
    ],
    'tempo' => [
        'labels' => $labels_tempo,
        'data' => $data_tempo
    ]
];

$chart_data_json = json_encode($chart_data);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios - Ouvidoria Assego</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
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
        
        .summary-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: #000E72;
        }
        
        .summary-label {
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
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
            
            .chart-container {
                height: 250px;
            }
            
            .summary-value {
                font-size: 1.5rem;
            }
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
                        <?php if (isset($nivel_acesso) && $nivel_acesso === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="usuarios.php">
                                <i class="fas fa-users"></i> Usuários
                            </a>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link active" href="relatorios.php">
                                <i class="fas fa-chart-bar"></i> Relatórios
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-lg-10 ms-sm-auto px-md-4 py-4 content-wrapper">
                <h2 class="mb-4"><i class="fas fa-chart-bar"></i> Relatórios</h2>
                
                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form action="" method="GET" class="row g-3">
                            <div class="col-md-4 col-sm-6">
                                <label for="periodo" class="form-label">Período</label>
                                <select id="periodo" name="periodo" class="form-select">
                                    <option value="mensal" <?php echo $periodo === 'mensal' ? 'selected' : ''; ?>>Mensal</option>
                                    <option value="anual" <?php echo $periodo === 'anual' ? 'selected' : ''; ?>>Anual</option>
                                </select>
                            </div>

                            <div class="col-md-4 col-sm-6" id="mes-group" <?php echo $periodo === 'anual' ? 'style="display:none"' : ''; ?>>
                                <label for="mes" class="form-label">Mês</label>
                                <select id="mes" name="mes" class="form-select">
                                    <?php for ($m = 1; $m <= 12; $m++): ?>
                                        <?php 
$meses_pt = [
    1 => 'Janeiro',
    2 => 'Fevereiro',
    3 => 'Março', 
    4 => 'Abril',
    5 => 'Maio',
    6 => 'Junho',
    7 => 'Julho',
    8 => 'Agosto',
    9 => 'Setembro',
    10 => 'Outubro',
    11 => 'Novembro',
    12 => 'Dezembro'
];
?>

<option value="<?php echo $m; ?>" <?php echo $m == $mes ? 'selected' : ''; ?>>
    <?php echo $meses_pt[$m]; ?>
</option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div class="col-md-4 col-sm-6">
                                <label for="ano" class="form-label">Ano</label>
                                <select id="ano" name="ano" class="form-select">
                                    <?php foreach ($anos_disponiveis as $ano_disponivel): ?>
                                    <option value="<?php echo $ano_disponivel; ?>" <?php echo $ano_disponivel == $ano ? 'selected' : ''; ?>>
                                        <?php echo $ano_disponivel; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter"></i> Aplicar Filtros
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <h3 class="mb-4">Relatório de Manifestações - <?php echo $periodo === 'mensal' ? date('F Y', mktime(0, 0, 0, $mes, 1, $ano)) : $ano; ?></h3>

                <!-- Stats Cards -->
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-5 g-4 mb-4">
                    <div class="col">
                        <div class="card h-100 text-center">
                            <div class="card-body">
                                <div class="summary-value"><?php echo $total_manifestacoes; ?></div>
                                <div class="summary-label">Total de Manifestações</div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card h-100 text-center">
                            <div class="card-body">
                                <div class="summary-value"><?php echo isset($dados_por_tipo['sugestao']) ? $dados_por_tipo['sugestao'] : 0; ?></div>
                                <div class="summary-label">Sugestões</div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card h-100 text-center">
                            <div class="card-body">
                                <div class="summary-value"><?php echo isset($dados_por_tipo['critica']) ? $dados_por_tipo['critica'] : 0; ?></div>
                                <div class="summary-label">Críticas</div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card h-100 text-center">
                            <div class="card-body">
                                <div class="summary-value"><?php echo isset($dados_por_tipo['elogio']) ? $dados_por_tipo['elogio'] : 0; ?></div>
                                <div class="summary-label">Elogios</div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card h-100 text-center">
                            <div class="card-body">
                                <div class="summary-value"><?php echo isset($dados_por_tipo['reclamacao']) ? $dados_por_tipo['reclamacao'] : 0; ?></div>
                                <div class="summary-label">Reclamações</div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($total_manifestacoes > 0): ?>
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <i class="fas fa-chart-pie"></i> Manifestações por Tipo
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="chartTipo"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <i class="fas fa-chart-pie"></i> Manifestações por Status
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="chartStatus"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <i class="fas fa-chart-bar"></i> Manifestações por Área
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="chartArea"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <i class="fas fa-chart-line"></i> Evolução das Manifestações
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="chartTempo"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="card">
                    <div class="card-body">
                        <div class="empty-state">
                            <i class="fas fa-info-circle fa-3x mb-3"></i>
                            <h3>Sem dados para exibir</h3>
                            <p>Não existem manifestações registradas para o período selecionado.</p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
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
            
            // Toggle mes-group based on periodo select
            const periodoSelect = document.getElementById('periodo');
            const mesGroup = document.getElementById('mes-group');
            
            periodoSelect.addEventListener('change', function() {
                if (this.value === 'mensal') {
                    mesGroup.style.display = 'block';
                } else {
                    mesGroup.style.display = 'none';
                }
            });
            
            <?php if ($total_manifestacoes > 0): ?>
            // Chart data
            const chartData = <?php echo $chart_data_json; ?>;
            
            // Make charts responsive
            const resizeCharts = () => {
                setTimeout(() => {
                    const containers = document.querySelectorAll('.chart-container');
                    containers.forEach(container => {
                        const canvas = container.querySelector('canvas');
                        if (canvas && canvas.chart) {
                            canvas.chart.resize();
                        }
                    });
                }, 50);
            };
            
            window.addEventListener('resize', resizeCharts);
            
            // Tipo chart (pie)
            const ctxTipo = document.getElementById('chartTipo').getContext('2d');
            const chartTipo = new Chart(ctxTipo, {
                type: 'pie',
                data: {
                    labels: chartData.tipo.labels,
                    datasets: [{
                        data: chartData.tipo.data,
                        backgroundColor: chartData.tipo.colors,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                font: {
                                    size: window.innerWidth < 768 ? 10 : 12
                                }
                            }
                        }
                    }
                }
            });
            ctxTipo.canvas.chart = chartTipo;
            
            // Status chart (pie)
            const ctxStatus = document.getElementById('chartStatus').getContext('2d');
            const chartStatus = new Chart(ctxStatus, {
                type: 'pie',
                data: {
                    labels: chartData.status.labels,
                    datasets: [{
                        data: chartData.status.data,
                        backgroundColor: chartData.status.colors,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                font: {
                                    size: window.innerWidth < 768 ? 10 : 12
                                }
                            }
                        }
                    }
                }
            });
            ctxStatus.canvas.chart = chartStatus;
            
            // Area chart (bar)
            const ctxArea = document.getElementById('chartArea').getContext('2d');
            const chartArea = new Chart(ctxArea, {
                type: 'bar',
                data: {
                    labels: chartData.area.labels,
                    datasets: [{
                        label: 'Manifestações',
                        data: chartData.area.data,
                        backgroundColor: chartData.area.colors,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                font: {
                                    size: window.innerWidth < 768 ? 10 : 12
                                }
                            }
                        },
                        x: {
                            ticks: {
                                font: {
                                    size: window.innerWidth < 768 ? 10 : 12
                                }
                            }
                        }
                    }
                }
            });
            ctxArea.canvas.chart = chartArea;
            
            // Tempo chart (line)
            const ctxTempo = document.getElementById('chartTempo').getContext('2d');
            const chartTempo = new Chart(ctxTempo, {
                type: 'line',
                data: {
                    labels: chartData.tempo.labels,
                    datasets: [{
                        label: 'Manifestações',
                        data: chartData.tempo.data,
                        borderColor: '#000E72',
                        backgroundColor: 'rgba(0, 14, 114, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                font: {
                                    size: window.innerWidth < 768 ? 10 : 12
                                }
                            }
                        },
                        x: {
                            ticks: {
                                font: {
                                    size: window.innerWidth < 768 ? 10 : 12
                                }
                            }
                        }
                    }
                }
            });
            ctxTempo.canvas.chart = chartTempo;
            <?php endif; ?>
        });
    </script>
</body>
</html>