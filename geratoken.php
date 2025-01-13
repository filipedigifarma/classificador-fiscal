<?php
header("Content-Type: application/json");

// Validação básica do CNPJ e entrada de dados
function validarCNPJ($cnpj) {
    return preg_match('/^\d{14}$/', $cnpj);
}

// Receber dados do POST
$data = json_decode(file_get_contents("php://input"), true);
$cnpj = $data['cnpj'] ?? null;
$senhaJ = $data['senha'] ?? null;

if (!$cnpj || !$senhaJ || !validarCNPJ($cnpj)) {
    http_response_code(400);
    echo json_encode(['error' => 'CNPJ inválido ou dados ausentes']);
    exit;
}

// Gerar token único
$token = hash('sha256', $cnpj . uniqid() . microtime());

// Conectar ao banco
$mysqli = new mysqli("localhost", "root", "", "api");
if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro de conexão com o banco de dados']);
    exit;
}

// Inserir token no banco
$senha = password_hash($senhaJ, PASSWORD_BCRYPT);
$stmt = $mysqli->prepare("INSERT INTO integrador_fiscal (cnpj, senha, token) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE token = VALUES(token)");
$stmt->bind_param("sss", $cnpj, $senha, $token);

if ($stmt->execute()) {
    http_response_code(200);
    echo json_encode(['success' => 'Token gerado com sucesso', 'token' => $token]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao salvar o token no banco']);
}

$stmt->close();
$mysqli->close();
?>
