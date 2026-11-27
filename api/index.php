<?php
// Define que a resposta será no formato JSON e aceita caracteres especiais
header("Content-Type: application/json; charset=UTF-8");
// Permite que outros domínios/portas acessem esta API (CORS)
header("Access-Control-Allow-Origin: *");

// Verifica se o método da requisição é GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    
    // Simulação dos dados que viriam de um banco de dados
    $tarefas = [
        [
            "id" => 1,
            "texto" => "Configurar o ambiente PHP",
            "concluida" => true
        ],
        [
            "id" => 2,
            "texto" => "Criar a API REST com retorno JSON",
            "concluida" => true
        ],
        [
            "id" => 3,
            "texto" => "Integrar o site PHP com a API via GET",
            "concluida" => false
        ],
        [
            "id" => 4,
            "texto" => "Subir o código para o GitHub",
            "concluida" => false
        ]
    ];

    // Retorna código HTTP 200 (Sucesso) e imprime o JSON
    http_response_code(200);
    echo json_encode($tarefas, JSON_UNESCAPED_UNICODE);

} else {
    // Retorna erro caso tentem usar POST, PUT ou DELETE
    http_response_code(405);
    echo json_encode(["erro" => "Método não permitido. Use GET."]);
}
