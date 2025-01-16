<?php
include(C_CONECTA);
header("Content-Type: application/json");

// Função para validar CNPJ
function validarCNPJ($cnpj)
{
    return preg_match('/^\d{14}$/', $cnpj);
}

// Função para gerar token seguro com HMAC
function gerarToken($cnpj, $senha, $chaveSecreta)
{
    $dados = "$cnpj|$senha";
    $hash = hash_hmac('sha256', $dados, $chaveSecreta); // Gera um hash HMAC
    return base64_encode("$dados|$hash"); // Codifica os dados e o hash em Base64
}

// Receber dados do POST
$data = json_decode(file_get_contents("php://input"), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['error' => 'Formato de JSON inválido']);
    exit;
}

$cnpj = trim($data['cnpj'] ?? '');
$nome = trim($data['nome'] ?? '');
$senhaJ = trim($data['senha'] ?? '');

if (empty($cnpj) || empty($nome) || empty($senhaJ) || !validarCNPJ($cnpj)) {
    http_response_code(400);
    echo json_encode(['error' => 'CNPJ inválido ou dados ausentes']);
    exit;
}

// Carregar chave secreta
$config = include '/opt/classificador-fiscal/config.php';

if (!isset($config['secret_key'])) {
    http_response_code(500);
    echo json_encode(['error' => 'Chave secreta não encontrada']);
    exit;
}
$chaveSecreta = $config['secret_key'];

// Gerar token único
$token = gerarToken($cnpj, $senhaJ, $chaveSecreta);

if (!$token) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao gerar o token']);
    exit;
}

// Conectar ao banco
try {
    $mysqli = conectar("digifarma");
    $mysqli->select_db("api");
    if ($mysqli->connect_error) {
        throw new Exception("Erro interno ao processar a solicitação.");
    }

    // Inserir ou atualizar token no banco
    $senhaHash = password_hash($senhaJ, PASSWORD_BCRYPT); // Hash seguro para a senha
    $stmt = $mysqli->prepare("
        INSERT INTO integrador_fiscal (cnpj, nome, senha, token)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE token = VALUES(token), senha = VALUES(senha), nome = VALUES(nome)
    ");
    $stmt->bind_param("ssss", $cnpj, $nome, $senhaHash, $token);

    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(['success' => 'Token gerado com sucesso', 'token' => $token]);
    } else {
        throw new Exception("Erro ao salvar o token no banco de dados.");
    }
} catch (Exception $e) {
    http_response_code(500);
    error_log($e->getMessage()); // Logar o erro para debug
    echo json_encode(['error' => 'Ocorreu um erro ao processar a solicitação.']);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($mysqli) && $mysqli->ping()) {
        $mysqli->close();
    }
}
?>