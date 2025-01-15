<?php
header("Content-Type: application/json");

// Função para validar token com HMAC
function validarToken($token, $chaveSecreta)
{
    // Decodificar o token base64
    $dadosDecodificados = base64_decode($token, true); // "true" impede decodificações silenciosas
    if (!$dadosDecodificados || substr_count($dadosDecodificados, '|') !== 2) {
        error_log('Token decodificado inválido ou formato inesperado.');
        return false; // Token inválido
    }

    $partes = explode('|', $dadosDecodificados);
    if (count($partes) !== 3) {
        error_log('Formato do token inválido. Dados ou hash ausentes. Dados: ' . $dadosDecodificados);
        return false;
    }

    $cnpj = $partes[0];
    $senha = $partes[1];
    $hashRecebido = $partes[2];


    // Recalcular o hash com a chave secreta
    $dados = "$cnpj|$senha"; // Reconstruir os dados originais
    $hashCalculado = hash_hmac('sha256', $dados, $chaveSecreta);

    if (!hash_equals($hashCalculado, $hashRecebido)) {
        error_log('Hash do token inválido. dados: ' . $dados . ' hash recebido: ' . $hashRecebido . ' hash calculado: ' . $hashCalculado);
        return false; // Hash inválido
    }

    // Retornar os dados validados
    return ['cnpj' => $cnpj, 'senha' => $senha];
}

// Conexão com o banco
$mysqli = new mysqli("localhost", "root", "", "api");
if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro de conexão com o banco de dados']);
    exit;
}

// Validação do método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

// Receber token do cabeçalho
$headers = getallheaders();
$token = $headers['Authorization'] ?? null;

if (!$token) {
    http_response_code(401);
    echo json_encode(['error' => 'Token ausente']);
    exit;
}

// Chave secreta (deve ser a mesma usada para gerar o token)
$config = include dirname(__DIR__) . '/config.php';

if (!isset($config['secret_key'])) {
    http_response_code(500);
    echo json_encode(['error' => 'Chave secreta não encontrada']);
    exit;
}
$chaveSecreta = $config['secret_key'];

// Validar o token
$resultado = validarToken($token, $chaveSecreta);
if (!$resultado) {
    http_response_code(401);
    echo json_encode(['error' => 'Token inválido']);
    exit;
}

// Validação adicional do CNPJ
if (!preg_match('/^\d{14}$/', $resultado['cnpj'])) {
    http_response_code(400);
    echo json_encode(['error' => 'CNPJ inválido']);
    exit;
}

// Validar dados do token no banco
$stmt = $mysqli->prepare("SELECT senha FROM integrador_fiscal WHERE cnpj = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao preparar a consulta']);
    exit;
}

$stmt->bind_param("s", $resultado['cnpj']);
$stmt->execute();
$result = $stmt->get_result();
$dados = $result->fetch_assoc();

if (!$dados || !password_verify($resultado['senha'], $dados['senha'])) {
    http_response_code(401);
    echo json_encode(['error' => 'CNPJ ou senha inválidos']);
    exit;
}

// Prosseguir com os dados
$data = json_decode(file_get_contents("php://input"), true);
$cnpj = $data['cnpj'] ?? null;
$status = $data['status'] ?? null;

if (!$cnpj || !in_array($status, ['E', 'S'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Dados inválidos']);
    exit;
}

// Inserir ou atualizar os dados
$stmt = $mysqli->prepare("INSERT INTO integrador_fiscal_lojas (cnpj, empresa_origem, status) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE status = VALUES(status)");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao preparar a consulta de atualização']);
    exit;
}

$stmt->bind_param("sss", $cnpj, $resultado['cnpj'], $status);

if ($stmt->execute()) {
    http_response_code(200);
    echo json_encode(['success' => 'Dados salvos com sucesso']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao salvar os dados']);
}

$stmt->close();
$mysqli->close();
