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
 * Traduz erros da API do Supabase/PostgreSQL para mensagens amigáveis em português.
 *
 * O Supabase retorna um JSON com:
 *   code    → código de erro PostgreSQL (ex: "23503", "23505")
 *   message → mensagem técnica em inglês
 *   detail  → detalhe adicional (ex: "Key (id_aluno)=(5) is not present in table...")
 *   hint    → sugestão do banco
 */
function sbTraduzirErro(array $error): string {
    $code   = $error['code']    ?? '';
    $msg    = strtolower($error['message'] ?? '');
    $detail = strtolower($error['detail']  ?? '');

    // 23503 — FK violation: tentativa de referenciar registro inexistente
    //         ou de remover/editar registro referenciado por outro
    if ($code === '23503' || str_contains($msg, 'foreign key constraint')) {
        // Remoção/edição bloqueada (registro é referenciado por outro)
        if (str_contains($msg, 'update or delete') || str_contains($msg, 'still referenced')) {
            if (str_contains($msg, '"aluno"'))   return 'Este aluno possui empréstimos vinculados e não pode ser removido ou alterado.';
            if (str_contains($msg, '"livro"'))   return 'Este livro possui empréstimos vinculados e não pode ser removido ou alterado.';
            if (str_contains($msg, '"editora"')) return 'Esta editora está vinculada a livros e não pode ser removida ou alterada.';
            if (str_contains($msg, '"autor"'))   return 'Este autor está vinculado a livros e não pode ser removido ou alterado.';
            if (str_contains($msg, '"emprestimo"')) return 'Este empréstimo possui itens vinculados e não pode ser removido.';
            return 'Este registro está vinculado a outros dados e não pode ser removido ou alterado.';
        }
        // Inserção/edição com FK inexistente (ex: id_editora que não existe)
        if (str_contains($msg, 'is not present in table') || str_contains($detail, 'is not present in table')) {
            if (str_contains($msg . $detail, '"editora"')) return 'A editora selecionada não existe.';
            if (str_contains($msg . $detail, '"autor"'))   return 'Um dos autores selecionados não existe.';
            if (str_contains($msg . $detail, '"aluno"'))   return 'O aluno selecionado não existe.';
            if (str_contains($msg . $detail, '"livro"'))   return 'Um dos livros selecionados não existe.';
            return 'Referência inválida: o registro relacionado não foi encontrado.';
        }
        return 'Violação de integridade referencial.';
    }

    // 23505 — Unique violation: valor duplicado em campo único
    if ($code === '23505' || str_contains($msg, 'duplicate key')) {
        if (str_contains($msg . $detail, 'isbn'))            return 'Já existe um livro cadastrado com este ISBN.';
        if (str_contains($msg . $detail, 'registro_aluno'))  return 'Este número de registro já está em uso.';
        if (str_contains($msg . $detail, 'cpf'))             return 'Já existe um aluno cadastrado com este CPF.';
        if (str_contains($msg . $detail, 'email'))           return 'Já existe um cadastro com este e-mail.';
        return 'Já existe um registro com esses dados.';
    }

    // 23502 — Not-null violation: campo obrigatório não preenchido
    if ($code === '23502' || str_contains($msg, 'null value in column')) {
        return 'Um campo obrigatório não foi preenchido.';
    }

    // 22P02 — Invalid text representation (tipo errado de dado)
    if ($code === '22P02') {
        return 'Valor inválido em um dos campos.';
    }

    // 42501 — Insufficient privilege
    if ($code === '42501') {
        return 'Sem permissão para realizar esta operação.';
    }

    // Fallback: usa a mensagem original do Supabase, sem o prefixo técnico
    $raw = $error['message'] ?? $error['hint'] ?? 'Erro desconhecido.';
    return $raw;
}

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
        throw new Exception("Falha na conexão com o banco de dados. Tente novamente.");
    }

    curl_close($ch);

    // HTTP 4xx / 5xx → lança exceção com mensagem amigável
    if ($httpCode >= 400) {
        $error = json_decode($response, true) ?? [];
        throw new Exception(sbTraduzirErro($error));
    }

    if (empty($response) || $response === 'null') return [];

    $decoded = json_decode($response, true);
    return is_array($decoded) ? $decoded : [];
}
