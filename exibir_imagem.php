<?php
date_default_timezone_set('America/Sao_Paulo');
require_once 'verificar_login.php';
require_once 'conexao.php';



$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if (!$id) {
    header('HTTP/1.0 404 Not Found');
    exit('Imagem não encontrada');
}

try {
   
    $sql = "SELECT imagem_blob, imagem_tipo, imagem_path FROM manifestacoes WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        header('HTTP/1.0 404 Not Found');
        exit('Imagem não encontrada');
    }
    
    $manifestacao = $stmt->fetch();
    
    
    if (!empty($manifestacao['imagem_blob'])) {
        
        $content = $manifestacao['imagem_blob'];
        $content_type = $manifestacao['imagem_tipo'] ?: 'image/jpeg';
        
        header('Content-Type: ' . $content_type);
        echo $content;
    } 
    else if (!empty($manifestacao['imagem_path']) && file_exists($manifestacao['imagem_path'])) {
       
        $content_type = mime_content_type($manifestacao['imagem_path']) ?: 'image/jpeg';
        
        header('Content-Type: ' . $content_type);
        header('Content-Length: ' . filesize($manifestacao['imagem_path']));
        readfile($manifestacao['imagem_path']);
    } 
    else {
        header('HTTP/1.0 404 Not Found');
        exit('Imagem não encontrada');
    }
    
} catch (PDOException $e) {
    header('HTTP/1.0 500 Internal Server Error');
    exit('Erro ao buscar imagem: ' . $e->getMessage());
}