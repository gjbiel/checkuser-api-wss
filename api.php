<?php
header("Access-Control-Allow-Origin: *");
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Função para ler o arquivo JSON de IPs autorizados
function ler_arquivo_json() {
    $filename = 'allowedIps.json';
    return file_exists($filename) ? json_decode(file_get_contents($filename), true) : [];
}

// Função para verificar se o IP do cliente está autorizado
function ip_autorizado($clientIp, $allowedIps) {
    return in_array($clientIp, $allowedIps);
}

// Função principal que processa a requisição
function processar_requisicao() {
    $requestMethod = $_SERVER['REQUEST_METHOD'];
    $urlPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $urlParts = explode('/API.PHP/', $urlPath);

    // A parte da URL após /API.PHP/
    $url = isset($urlParts[1]) ? $urlParts[1] : '';

    var_dump($urlPath);  // Adicionado para depurar
    var_dump($urlParts); // Adicionado para depurar

    if (empty($url)) {
        http_response_code(400);
        die("URL não especificada.");
    }

    $clientIp = $_SERVER['REMOTE_ADDR'];

    $allowedIps = ler_arquivo_json();

    if (!ip_autorizado($clientIp, $allowedIps)) {
        http_response_code(403);
        die("Acesso não autorizado.");
    }

    try {
        if ($requestMethod === 'POST') {
            // Ler dados POST diretamente da solicitação
            $postData = file_get_contents('php://input');

            if ($postData === false) {
                throw new Exception("Erro ao ler dados POST.");
            }

            $options = [
                'http' => [
                    'method' => 'POST',
                    'header' => 'Content-Type: application/json',
                    'content' => $postData,
                ],
            ];

            $context = stream_context_create($options);
            $response = file_get_contents($url, false, $context);

            if ($response === false) {
                throw new Exception("Erro ao obter resposta da URL.");
            }

            echo $response;
        } elseif ($requestMethod === 'GET') {
            $response = file_get_contents($url);

            if ($response === false) {
                throw new Exception("Erro ao obter resposta da URL.");
            }

            echo $response;
        } else {
            http_response_code(40
