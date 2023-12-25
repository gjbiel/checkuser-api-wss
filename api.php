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
    $url = isset($_GET['url']) ? $_GET['url'] : '';
    $urlParts = parse_url($url);
    $clientIp = isset($urlParts['host']) ? $urlParts['host'] : '';

    $allowedIps = ler_arquivo_json();

    if ($requestMethod === 'GET' || $requestMethod === 'POST') {

        if ($requestMethod === 'POST') {
            // Ler dados POST diretamente da solicitação
            $postData = file_get_contents('php://input');
            if ($postData !== false) {
                echo file_get_contents($url, false, stream_context_create([
                    'http' => [
                        'method' => 'POST',
                        'header' => 'Content-Type: application/json',
                        'content' => $postData,
                    ],
                ]));
            } else {
                echo "Erro ao ler dados POST.";
            }
        } elseif ($requestMethod === 'GET') {
            echo file_get_contents($url);
        }

    } else {
        echo "Método não suportado.";
    }
}

processar_requisicao();
?>
