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
        
        // Se forneceu email e não é manifestação anônima, verificar também o email
        if (!empty($email)) {
            $sql .= " AND email = :email AND anonimo = 0";
            $params[':email'] = $email;
        }
        
        $stmt = $pdo->prepare($sql);
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value);
        }
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $manifestacao = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            // Verificar se o protocolo existe independente do email
            $stmt_check = $pdo->prepare("SELECT anonimo FROM manifestacoes WHERE protocolo = :protocolo");
            $stmt_check->bindValue(':protocolo', $protocolo);
            $stmt_check->execute();
            
            if ($stmt_check->rowCount() > 0) {
                $result = $stmt_check->fetch();
                if ($result['anonimo'] == 0) {
                    $error_message = "E-mail não corresponde ao protocolo informado.";
                } else {
                    $error_message = "Protocolo não encontrado. Verifique e tente novamente.";
                }
            } else {
                $error_message = "Protocolo não encontrado. Verifique e tente novamente.";
            }
        }
    } catch (PDOException $e) {
        $error_message = "Erro ao buscar manifestação: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Manifestação - Ouvidoria Assego</title>
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
            padding-bottom: 70px; /* Espaço para o footer fixo */
            padding-top: 80px; /* Espaço para o header fixo */
            background-color: #f5f5f5;
        }
        
        .header {
            background: linear-gradient(to right, #000E72, #FFDF00);
            color: #FFFFFF;
            padding: 15px 0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        
        .header img {
            height: 50px;
        }
        
        .main-content {
            margin-top: 20px;
            margin-bottom: 30px;
        }
        
        .consulta-card {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
            border: 2px solid #FFDF00;
        }
        
        .consulta-titulo {
            color: #000E72;
            margin-bottom: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        
        .consulta-titulo i {
            margin-right: 10px;
            font-size: 24px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 30px;
            font-weight: 600;
            margin-bottom: 15px;
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
        
        .manifestacao-info {
            margin-bottom: 20px;
            border-bottom: 1px solid #eaeaea;
            padding-bottom: 20px;
        }
        
        .manifestacao-info h4 {
            color: #000E72;
            font-size: 1.1rem;
            margin-top: 15px;
            margin-bottom: 5px;
        }
        
        .manifestacao-mensagem {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .resposta-container {
            border-left: 4px solid #000E72;
            padding-left: 20px;
            margin-top: 20px;
        }
        
        .resposta-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .resposta-data {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .resposta-mensagem {
            background-color: #f0f0f0;
            padding: 15px;
            border-radius: 10px;
        }
        
        .sem-resposta {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            color: #6c757d;
        }
        
        .sem-resposta i {
            font-size: 40px;
            margin-bottom: 10px;
            color: #adb5bd;
        }
        
        .imagem-anexada {
            max-width: 100%;
            border-radius: 10px;
            margin: 10px 0;
            border: 1px solid #eaeaea;
        }
        
        .btn-primary {
            background: linear-gradient(to right, #000E72, #FFDF00);
            border: none;
            border-radius: 8px;
            font-weight: bold;
            padding: 12px 25px;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            margin-top: 20px;
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        
        .error-container {
            text-align: center;
            padding: 30px;
        }
        
        .error-icon {
            font-size: 60px;
            color: #dc3545;
            margin-bottom: 20px;
        }
        
        .footer {
            background-color: #000E72;
            color: #FFFFFF;
            text-align: center;
            padding: 10px 0;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="d-flex align-items-center">
                <img src="https://assego.com.br/wp-content/uploads/2023/11/logo.png-mini2.png" alt="Assego Logo" class="me-3">
                <h1 class="h4 mb-0">Ouvidoria Assego</h1>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container main-content">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <?php if (!empty($protocolo) && empty($error_message) && $manifestacao): ?>
                <div class="consulta-card">
                    <h2 class="consulta-titulo"><i class="fas fa-file-alt"></i> Detalhes da Manifestação</h2>
                    
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
                    
                    $tipos = [
                        'sugestao' => 'Sugestão',
                        'critica' => 'Crítica',
                        'elogio' => 'Elogio',
                        'reclamacao' => 'Reclamação'
                    ];
                    ?>
                    
                    <div class="status-container mb-3">
                        <span class="status-badge <?php echo $status_classes[$manifestacao['status'] ?? 'pendente']; ?>">
                            Status: <?php echo $status_labels[$manifestacao['status'] ?? 'pendente']; ?>
                        </span>
                    </div>
                    
                    <div class="manifestacao-info">
                        <div class="row">
                            <div class="col-md-6">
                                <h4>Protocolo</h4>
                                <p><?php echo htmlspecialchars($manifestacao['protocolo']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <h4>Data de Envio</h4>
                                <p><?php echo date('d/m/Y H:i', strtotime($manifestacao['data_criacao'])); ?></p>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h4>Tipo de Manifestação</h4>
                                <p><?php echo $tipos[$manifestacao['tipo_manifestacao']] ?? 'Não especificado'; ?></p>
                            </div>
                            <div class="col-md-6">
                                <h4>Área Relacionada</h4>
                                <p><?php echo ucfirst($manifestacao['area_relacionada']); ?></p>
                            </div>
                        </div>
                        
                        <?php if ($manifestacao['anonimo'] == 0): ?>
                        <div class="row">
                            <div class="col-md-12">
                                <h4>Identificação</h4>
                                <p>
                                    <?php echo htmlspecialchars($manifestacao['nome']); ?><br>
                                    <?php echo htmlspecialchars($manifestacao['email']); ?>
                                </p>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="row">
                            <div class="col-md-12">
                                <h4>Identificação</h4>
                                <p>Manifestação anônima</p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <h4>Sua Mensagem</h4>
                    <div class="manifestacao-mensagem">
                        <?php echo nl2br(htmlspecialchars($manifestacao['mensagem'])); ?>
                    </div>
                    
                    <?php if (!empty($manifestacao['imagem_blob'])): ?>
                    <h4>Imagem Anexada</h4>
                    <img src="exibir_imagem.php?id=<?php echo $manifestacao['id']; ?>" alt="Imagem anexada" class="imagem-anexada">
                    <?php endif; ?>
                    
                    <?php if (!empty($manifestacao['resposta'])): ?>
                    <h4>Resposta da Ouvidoria</h4>
                    <div class="resposta-container">
                        <div class="resposta-header">
                        <strong>Respondido em:</strong>
                            <span class="resposta-data"><?php echo date('d/m/Y H:i', strtotime($manifestacao['data_atualizacao'])); ?></span>
                        </div>
                        <div class="resposta-mensagem">
                            <?php echo nl2br(htmlspecialchars($manifestacao['resposta'])); ?>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="sem-resposta mt-4">
                        <i class="fas fa-clock"></i>
                        <h5>Aguardando Resposta</h5>
                        <p>Sua manifestação ainda não foi respondida pela nossa equipe. Por favor, aguarde.</p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="text-center mt-4">
                        <a href="index.html" class="btn btn-primary">
                            <i class="fas fa-home"></i> Voltar para Página Inicial
                        </a>
                    </div>
                </div>
                <?php elseif (!empty($error_message)): ?>
                <div class="consulta-card">
                    <div class="error-container">
                        <i class="fas fa-exclamation-circle error-icon"></i>
                        <h3>Não foi possível localizar a manifestação</h3>
                        <p><?php echo $error_message; ?></p>
                        <a href="index.html" class="btn btn-primary mt-3">
                            <i class="fas fa-home"></i> Voltar para Página Inicial
                        </a>
                    </div>
                </div>
                <?php else: ?>
                <div class="consulta-card">
                    <h2 class="consulta-titulo"><i class="fas fa-search"></i> Consultar Manifestação</h2>
                    
                    <form action="consultar_manifestacao.php" method="GET">
                        <div class="mb-3">
                            <label for="protocolo" class="form-label">Número de Protocolo</label>
                            <input type="text" class="form-control" id="protocolo" name="protocolo" placeholder="Digite o protocolo recebido" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">E-mail (se não for anônimo)</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Digite o e-mail utilizado">
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Consultar
                            </button>
                        </div>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p class="mb-0">Tecnologia ASSEGO</p>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>