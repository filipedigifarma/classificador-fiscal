<?php
header("Content-Type: application/json");

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

// Validar token no banco
$stmt = $mysqli->prepare("SELECT cnpj FROM integrador_fiscal WHERE token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(401);
    echo json_encode(['error' => 'Token inválido']);
    exit;
}

// Prosseguir com os dados
$data = json_decode(file_get_contents("php://input"), true);
$cnpj = $data['cnpj'] ?? null;
$status = $data['status'] ?? null;

if (!$cnpj || !in_array($status, ['entrada', 'saida'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Dados inválidos']);
    exit;
}

// Inserir ou atualizar os dados
$stmt = $mysqli->prepare("INSERT INTO integrador_fiscal_lojas (cnpj, status) VALUES (?, ?) ON DUPLICATE KEY UPDATE status = VALUES(status)");
$stmt->bind_param("ss", $cnpj, $status);

if ($stmt->execute()) {
    http_response_code(200);
    echo json_encode(['success' => 'Dados salvos com sucesso']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao salvar os dados']);
}

$stmt->close();
$mysqli->close();
?>
