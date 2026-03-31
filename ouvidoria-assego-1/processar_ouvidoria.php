<?php
date_default_timezone_set('America/Sao_Paulo');
session_start();
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo_manifestacao = filter_input(INPUT_POST, 'tipo_manifestacao', FILTER_SANITIZE_STRING);
    $area_relacionada = filter_input(INPUT_POST, 'area_relacionada', FILTER_SANITIZE_STRING);
    $mensagem = filter_input(INPUT_POST, 'mensagem', FILTER_SANITIZE_STRING);
    $anonimo = isset($_POST['anonimo']) ? 1 : 0;
    
    $nome = null;
    $email = null;
    
    if (!$anonimo) {
        $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    }
    
    $imagem_data = null;
    $imagem_tipo = null;
    
   
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $file_extension = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array(strtolower($file_extension), $allowed_types)) {
            if ($_FILES['imagem']['size'] <= 60 * 1024 * 1024) { 
               
                $imagem_data = file_get_contents($_FILES['imagem']['tmp_name']);
                $imagem_tipo = $_FILES['imagem']['type'];
            } else {
                $error_message = "O arquivo deve ter no máximo 60MB.";
                header('Location: index.html?erro=' . urlencode($error_message));
                exit;
            }
        } else {
            $error_message = "Tipo de arquivo não permitido. Apenas jpg, jpeg, png e gif são aceitos.";
            header('Location: index.html?erro=' . urlencode($error_message));
            exit;
        }
    }
    
    try {
        
        $sql_check_imagem = "SHOW COLUMNS FROM manifestacoes LIKE 'imagem_blob'";
        $result_imagem = $pdo->query($sql_check_imagem);
        
        if ($result_imagem->rowCount() == 0) {
            $sql_alter_imagem = "ALTER TABLE manifestacoes ADD COLUMN imagem_blob LONGBLOB, ADD COLUMN imagem_tipo VARCHAR(100)";
            $pdo->exec($sql_alter_imagem);
        }
        
        
        $sql_check_protocolo = "SHOW COLUMNS FROM manifestacoes LIKE 'protocolo'";
        $result_protocolo = $pdo->query($sql_check_protocolo);
        
        if ($result_protocolo->rowCount() == 0) {
            $sql_alter_protocolo = "ALTER TABLE manifestacoes ADD COLUMN protocolo VARCHAR(50)";
            $pdo->exec($sql_alter_protocolo);
        }
        
    
        $data_atual = date('Y-m-d H:i:s');
        
        $sql = "INSERT INTO manifestacoes (tipo_manifestacao, area_relacionada, mensagem, imagem_path, imagem_blob, imagem_tipo, anonimo, nome, email, data_criacao, data_atualizacao)
                VALUES (:tipo_manifestacao, :area_relacionada, :mensagem, NULL, :imagem_blob, :imagem_tipo, :anonimo, :nome, :email, :data_criacao, :data_atualizacao)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':tipo_manifestacao', $tipo_manifestacao);
        $stmt->bindParam(':area_relacionada', $area_relacionada);
        $stmt->bindParam(':mensagem', $mensagem);
        $stmt->bindParam(':imagem_blob', $imagem_data, PDO::PARAM_LOB);
        $stmt->bindParam(':imagem_tipo', $imagem_tipo);
        $stmt->bindParam(':anonimo', $anonimo);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':data_criacao', $data_atual);
        $stmt->bindParam(':data_atualizacao', $data_atual);
        
        if ($stmt->execute()) {
            
            $id_manifestacao = $pdo->lastInsertId();
            
            
            $protocolo = date('Ymd') . '-' . str_pad($id_manifestacao, 4, '0', STR_PAD_LEFT) . '-' . substr(md5(uniqid(rand(), true)), 0, 6);
            
            
            $sql_protocolo = "UPDATE manifestacoes SET protocolo = :protocolo WHERE id = :id";
            $stmt_protocolo = $pdo->prepare($sql_protocolo);
            $stmt_protocolo->bindParam(':protocolo', $protocolo);
            $stmt_protocolo->bindParam(':id', $id_manifestacao);
            $stmt_protocolo->execute();
            
           
            header('Location: confirmacao.php?protocolo=' . urlencode($protocolo) . '&anonimo=' . $anonimo . '&email=' . urlencode($email));
        } else {
            $error_message = "Erro ao enviar manifestação. Tente novamente.";
            header('Location: index.html?erro=' . urlencode($error_message));
        }
    } catch (PDOException $e) {
        $error_message = "Erro ao processar sua solicitação: " . $e->getMessage();
        header('Location: index.html?erro=' . urlencode($error_message));
    }
    
    exit;
} else {
    header('Location: index.html');
    exit;
}