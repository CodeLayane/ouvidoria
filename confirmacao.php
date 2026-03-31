<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmação - Ouvidoria Assego</title>
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
        
        .confirmation-card {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
            text-align: center;
            border: 2px solid #FFDF00;
        }
        
        .success-icon {
            font-size: 80px;
            color: #28a745;
            margin-bottom: 20px;
        }
        
        .confirmation-title {
            color: #000E72;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .protocol-box {
            background-color: #f8f9fa;
            border: 2px dashed #000E72;
            border-radius: 10px;
            padding: 15px;
            margin: 20px 0;
        }
        
        .protocol-number {
            font-size: 24px;
            font-weight: 700;
            color: #000E72;
            letter-spacing: 1px;
        }
        
        .protocol-copy {
            cursor: pointer;
            color: #000E72;
            font-size: 20px;
            margin-left: 10px;
            transition: all 0.2s ease;
        }
        
        .protocol-copy:hover {
            color: #FFDF00;
        }
        
        .instructions {
            margin: 20px 0;
            text-align: left;
        }
        
        .instructions li {
            margin-bottom: 10px;
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
        
        .copy-success {
            display: none;
            background-color: #28a745;
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
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
    <?php
    $protocolo = isset($_GET['protocolo']) ? $_GET['protocolo'] : '';
    $anonimo = isset($_GET['anonimo']) ? $_GET['anonimo'] : 0;
    $email = isset($_GET['email']) ? $_GET['email'] : '';
    ?>

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
                <div class="confirmation-card">
                    <i class="fas fa-check-circle success-icon"></i>
                    <h2 class="confirmation-title">Manifestação Enviada com Sucesso!</h2>
                    <p>Sua manifestação foi registrada e será analisada pela nossa equipe.</p>
                    
                    <div class="protocol-box">
                        <p class="mb-1">Seu número de protocolo é:</p>
                        <div class="d-flex justify-content-center align-items-center">
                            <span class="protocol-number" id="protocolo"><?php echo htmlspecialchars($protocolo); ?></span>
                            <i class="fas fa-copy protocol-copy" id="copyBtn" title="Copiar protocolo"></i>
                        </div>
                        <div class="copy-success" id="copySuccess">
                            Protocolo copiado para a área de transferência!
                        </div>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> <strong>Importante:</strong> Anote ou salve este número de protocolo. Você precisará dele para consultar o status da sua manifestação.
                    </div>
                    
                    <?php if ($anonimo == 0 && !empty($email)): ?>
                    <p><strong>ATENÇÃO</strong> O PROTOCOLO É A <strong>ÚNICA</strong>  FORMA SO SENHOR(A) VER SUAS MANIFESTAÇÕES</p>
                    <?php else: ?>
                    <p><strong>ATENÇÃO</strong> O PROTOCOLO É A <strong>ÚNICA</strong>  FORMA DO SENHOR(A) VER SUAS MANIFESTAÇÕES</p>
                    <?php endif; ?>
                    
                    <div class="instructions">
                        <h4>Como acompanhar sua manifestação:</h4>
                        <ol>
                            <li>Acesse a página inicial da Ouvidoria Assego</li>
                            <li>Clique no botão "Consultar Minhas Manifestações"</li>
                            <li>Informe o número de protocolo <?php echo $anonimo == 0 ? 'e seu e-mail' : ''; ?></li>
                            <li>Você poderá visualizar o status atual e a resposta, quando disponível</li>
                        </ol>
                    </div>
                    
                    <a href="index.html" class="btn btn-primary">
                        <i class="fas fa-home"></i> Voltar para Página Inicial
                    </a>
                </div>
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
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const copyBtn = document.getElementById('copyBtn');
            const protocolo = document.getElementById('protocolo');
            const copySuccess = document.getElementById('copySuccess');
            
            copyBtn.addEventListener('click', function() {
                // Cria um elemento de texto temporário
                const tempInput = document.createElement('textarea');
                tempInput.value = protocolo.textContent;
                document.body.appendChild(tempInput);
                
                // Seleciona e copia o texto
                tempInput.select();
                document.execCommand('copy');
                
                // Remove o elemento temporário
                document.body.removeChild(tempInput);
                
                // Mostra mensagem de sucesso
                copySuccess.style.display = 'block';
                
                // Esconde a mensagem depois de 3 segundos
                setTimeout(function() {
                    copySuccess.style.display = 'none';
                }, 3000);
            });
        });
    </script>
</body>
</html>