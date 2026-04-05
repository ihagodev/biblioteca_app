<?php

/*
 * ╔══════════════════════════════════════════════════════════════╗
 * ║              CLIENTE HTTP — SUPABASE REST API               ║
 * ╚══════════════════════════════════════════════════════════════╝
 *
 * Por que este arquivo existe?
 * ----------------------------
 * O PHP não consegue abrir conexões TCP diretas ao Supabase em
 * ambientes de produção (Cloudflare, Railway, etc.), pois a porta
 * 5432 está bloqueada por firewall.
 *
 * O Supabase expõe todos os seus dados via HTTP (REST API).
 * Usamos curl — biblioteca padrão do PHP — para fazer as
 * requisições em vez de PDO.
 *
 * Como funciona:
 * --------------
 *  GET    /rest/v1/tabela              → SELECT *
 *  GET    /rest/v1/tabela?campo=eq.5  → SELECT WHERE campo = 5
 *  POST   /rest/v1/tabela             → INSERT
 *  PATCH  /rest/v1/tabela?campo=eq.5  → UPDATE WHERE campo = 5
 *  DELETE /rest/v1/tabela?campo=eq.5  → DELETE WHERE campo = 5
 *
 * Credenciais:
 * ------------
 * SUPABASE_URL  → URL do projeto (sem /rest/v1)
 * SUPABASE_KEY  → service_role key (Settings > API no dashboard)
 *                 Use a service_role, não a anon — ela tem acesso
 *                 total e é adequada para código servidor.
 *
 * Como encontrar a chave:
 *   Supabase Dashboard → Seu Projeto → Settings → API
 *   → "Project API keys" → copie "service_role"
 */

define('SUPABASE_URL',    'https://yutkzoqqpeqiowpoeckh.supabase.co');
define('SUPABASE_KEY',    getenv('SUPABASE_KEY') ?: 'COLE_SUA_SERVICE_ROLE_KEY_AQUI');
define('SUPABASE_SCHEMA', 'lib');


/**
 * Faz uma requisição à REST API do Supabase.
 *
 * @param string      $method  Verbo HTTP: GET, POST, PATCH, DELETE
 * @param string      $table   Nome da tabela (ex: 'aluno', 'livro')
 * @param array       $options Opções:
 *                               'query'  → query string  (ex: 'id=eq.5&select=*')
 *                               'body'   → array de dados para POST/PATCH
 *                               'prefer' → cabeçalho Prefer (ex: 'return=representation')
 * @return array               Linhas retornadas (array de arrays associativos)
 * @throws Exception           Se a API retornar erro HTTP >= 400
 */
function sbRequest(string $method, string $table, array $options = []): array {

    $url = SUPABASE_URL . '/rest/v1/' . $table;

    if (!empty($options['query'])) {
        $url .= '?' . $options['query'];
    }

    // Cabeçalhos obrigatórios em toda requisição
    $headers = [
        'apikey: '               . SUPABASE_KEY,
        'Authorization: Bearer ' . SUPABASE_KEY,
        'Content-Type: application/json',
        // Informa ao Supabase qual schema usar (nosso schema é 'lib')
        'Accept-Profile: '       . SUPABASE_SCHEMA,
    ];

    // Content-Profile é necessário em operações de escrita
    if (in_array($method, ['POST', 'PATCH', 'PUT', 'DELETE'])) {
        $headers[] = 'Content-Profile: ' . SUPABASE_SCHEMA;
    }

    // Prefer: return=representation → faz o POST retornar a linha inserida
    // (precisamos disso para obter o ID gerado pelo banco)
    if (!empty($options['prefer'])) {
        $headers[] = 'Prefer: ' . $options['prefer'];
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
    ]);

    if (isset($options['body'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($options['body']));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        $curlError = curl_error($ch);
        curl_close($ch);
        throw new Exception("Erro de rede (curl): $curlError");
    }

    curl_close($ch);

    // HTTP 4xx / 5xx → lança exceção com a mensagem do Supabase
    if ($httpCode >= 400) {
        $error = json_decode($response, true);
        $msg   = $error['message'] ?? $error['hint'] ?? "HTTP $httpCode";
        throw new Exception("Supabase API: $msg");
    }

    if (empty($response) || $response === 'null') return [];

    $decoded = json_decode($response, true);
    return is_array($decoded) ? $decoded : [];
}
