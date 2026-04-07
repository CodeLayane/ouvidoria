<?php
date_default_timezone_set('America/Sao_Paulo');
session_start();
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo_manifestacao = filter_input(INPUT_POST, 'tipo_manifestacao', FILTER_SANITIZE_STRING);
    $area_relacionada = filter_input(INPUT_POST, 'area_relacionada', FILTER_SANITIZE_STRING);
    $mensagem = filter_input(INPUT_POST, 'mensagem', FILTER_SANITIZE_STRING);
    $anonimo = isset($_POST['anonimo']) ? 1 : 0;
    $nome = null; $email = null;
    if (!$anonimo) {
        $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    }

    // Captura IP real
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
    } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    $ip_remetente = filter_var($ip, FILTER_VALIDATE_IP) ? $ip : $_SERVER['REMOTE_ADDR'];

    $imagem_data = null; $imagem_tipo = null;
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
        if (in_array(strtolower($ext), ['jpg','jpeg','png','gif'])) {
            if ($_FILES['imagem']['size'] <= 60*1024*1024) {
                $imagem_data = file_get_contents($_FILES['imagem']['tmp_name']);
                $imagem_tipo = $_FILES['imagem']['type'];
            } else {
                header('Location: index.html?erro='.urlencode("Arquivo deve ter no máximo 60MB.")); exit;
            }
        } else {
            header('Location: index.html?erro='.urlencode("Tipo não permitido. Use jpg, jpeg, png ou gif.")); exit;
        }
    }

    try {
        // Garante colunas
        if ($pdo->query("SHOW COLUMNS FROM manifestacoes LIKE 'imagem_blob'")->rowCount() == 0)
            $pdo->exec("ALTER TABLE manifestacoes ADD COLUMN imagem_blob LONGBLOB, ADD COLUMN imagem_tipo VARCHAR(100)");
        if ($pdo->query("SHOW COLUMNS FROM manifestacoes LIKE 'protocolo'")->rowCount() == 0)
            $pdo->exec("ALTER TABLE manifestacoes ADD COLUMN protocolo VARCHAR(50)");
        if ($pdo->query("SHOW COLUMNS FROM manifestacoes LIKE 'ip_remetente'")->rowCount() == 0)
            $pdo->exec("ALTER TABLE manifestacoes ADD COLUMN ip_remetente VARCHAR(50) DEFAULT NULL");

        $data_atual = date('Y-m-d H:i:s');
        $sql = "INSERT INTO manifestacoes (tipo_manifestacao, area_relacionada, mensagem, imagem_path, imagem_blob, imagem_tipo, anonimo, nome, email, ip_remetente, data_criacao, data_atualizacao)
                VALUES (:tipo, :area, :mensagem, NULL, :img, :img_tipo, :anonimo, :nome, :email, :ip, :criacao, :atualizacao)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':tipo', $tipo_manifestacao);
        $stmt->bindParam(':area', $area_relacionada);
        $stmt->bindParam(':mensagem', $mensagem);
        $stmt->bindParam(':img', $imagem_data, PDO::PARAM_LOB);
        $stmt->bindParam(':img_tipo', $imagem_tipo);
        $stmt->bindParam(':anonimo', $anonimo);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':ip', $ip_remetente);
        $stmt->bindParam(':criacao', $data_atual);
        $stmt->bindParam(':atualizacao', $data_atual);

        if ($stmt->execute()) {
            $id = $pdo->lastInsertId();
            $protocolo = date('Ymd').'-'.str_pad($id,4,'0',STR_PAD_LEFT).'-'.substr(md5(uniqid(rand(),true)),0,6);
            $s = $pdo->prepare("UPDATE manifestacoes SET protocolo=:p WHERE id=:id");
            $s->bindParam(':p', $protocolo); $s->bindParam(':id', $id); $s->execute();
            header('Location: confirmacao.php?protocolo='.urlencode($protocolo).'&anonimo='.$anonimo.'&email='.urlencode($email));
        } else {
            header('Location: index.html?erro='.urlencode("Erro ao enviar. Tente novamente."));
        }
    } catch (PDOException $e) {
        header('Location: index.html?erro='.urlencode("Erro: ".$e->getMessage()));
    }
    exit;
} else {
    header('Location: index.html'); exit;
}