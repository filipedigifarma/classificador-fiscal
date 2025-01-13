<?php
$mysqli = new mysqli("localhost", "usuario", "senha", "banco");
if ($mysqli->connect_error) {
    die("Erro de conexão: " . $mysqli->connect_error);
}

// Receber o token do cabeçalho
$headers = getallheaders();
$token = $headers['Authorization'] ?? null;

if (!$token) {
    http_response_code(401);
    echo json_encode(['error' => 'Token ausente']);
    exit;
}

// Consultar no banco
$stmt = $mysqli->prepare("SELECT cnpj FROM parceiros WHERE token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(401);
    echo json_encode(['error' => 'Token inválido']);
    exit;
}

// Token válido
$parceiro = $result->fetch_assoc();
echo "Token válido para o CNPJ: " . $parceiro['cnpj'];

$stmt->close();
$mysqli->close();
?>
