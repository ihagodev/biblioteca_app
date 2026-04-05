<?php

/*
 * ╔══════════════════════════════════════════════════════════════╗
 * ║              DATA ACCESS LAYER — SUPABASE REST API          ║
 * ╚══════════════════════════════════════════════════════════════╝
 *
 * Todas as funções aqui fazem requisições HTTP para a API REST
 * do Supabase em vez de abrir conexões diretas ao banco (PDO).
 *
 * Filtros da API (query string):
 *   campo=eq.5      → WHERE campo = 5
 *   campo=neq.5     → WHERE campo != 5
 *   campo=gt.5      → WHERE campo > 5
 *   campo=is.null   → WHERE campo IS NULL
 *   order=campo.desc → ORDER BY campo DESC
 *   select=*         → SELECT *
 *
 * Relacionamentos (embedding):
 *   select=*,editora(nm_editora)   → JOIN com tabela editora
 *   O Supabase detecta automaticamente as FK definidas no banco.
 */

require_once __DIR__ . "/../app/config/supabase.php";


/* ================================================================
   BASE GENÉRICA
================================================================ */

/**
 * Lista todos os registros de uma tabela.
 * Equivalente a: SELECT * FROM $tabela
 */
function listar($tabela) {
    return sbRequest('GET', $tabela, ['query' => 'select=*']);
}

/**
 * Insere um registro e retorna a linha criada (incluindo o PK gerado).
 * Equivalente a: INSERT INTO $tabela (...) VALUES (...)
 *
 * O "return=representation" faz a API devolver a linha inserida,
 * o que nos permite pegar o ID gerado automaticamente pelo banco.
 */
function inserir($tabela, $dados) {
    $result = sbRequest('POST', $tabela, [
        'body'   => $dados,
        'prefer' => 'return=representation',
    ]);
    return $result[0] ?? null;
}

/**
 * Busca um único registro pelo valor de sua chave primária.
 * Equivalente a: SELECT * FROM $tabela WHERE $campoPk = $id
 */
function buscarPorId($tabela, $campoPk, $id) {
    $result = sbRequest('GET', $tabela, [
        'query' => "$campoPk=eq.$id&select=*",
    ]);
    return $result[0] ?? null;
}

/**
 * Atualiza um registro.
 * Equivalente a: UPDATE $tabela SET ... WHERE $pk = $id
 */
function atualizar($tabela, $dados, $pk, $id) {
    sbRequest('PATCH', $tabela, [
        'query' => "$pk=eq.$id",
        'body'  => $dados,
    ]);
    return true;
}

/**
 * Deleta um registro.
 * Equivalente a: DELETE FROM $tabela WHERE $pk = $id
 */
function deletar($tabela, $id, $pk) {
    sbRequest('DELETE', $tabela, [
        'query' => "$pk=eq.$id",
    ]);
    return true;
}


/* ================================================================
   ALUNO
================================================================ */

function listarAlunos() {
    return sbRequest('GET', 'aluno', [
        'query' => 'select=*&order=registro_aluno.desc',
    ]);
}

function buscarAlunoPorId($id) {
    return buscarPorId('aluno', 'registro_aluno', $id);
}

function inserirAluno($dados) {
    return inserir('aluno', $dados);
}

function atualizarAluno($id, $dados) {
    return atualizar('aluno', $dados, 'registro_aluno', $id);
}

function deletarAluno($id) {
    return deletar('aluno', $id, 'registro_aluno');
}


/* ================================================================
   EDITORA & AUTOR
================================================================ */

function listarEditora() {
    return sbRequest('GET', 'editora', [
        'query' => 'select=*&order=nm_editora.asc',
    ]);
}

function listarAutor() {
    return sbRequest('GET', 'autor', [
        'query' => 'select=*&order=nm_autor.asc',
    ]);
}

function inserirEditora($dados) {
    return inserir('editora', $dados);
}

function inserirAutor($dados) {
    return inserir('autor', $dados);
}


/* ================================================================
   LIVRO
================================================================ */

/**
 * Lista livros com o nome da editora.
 *
 * O "select=*,editora(nm_editora)" é o embedding do PostgREST:
 * ele segue a FK livro.id_editora → editora.id_editora e inclui
 * o campo nm_editora no resultado — sem precisar escrever JOIN.
 *
 * A resposta bruta é:
 *   { id_livro: 1, nm_livro: "...", editora: { nm_editora: "..." } }
 *
 * Achatamos para:
 *   { id_livro: 1, nm_livro: "...", nm_editora: "..." }
 */
function listarLivros() {
    $rows = sbRequest('GET', 'livro', [
        'query' => 'select=*,editora(nm_editora)&order=id_livro.desc',
    ]);

    return array_map(function ($l) {
        $l['nm_editora'] = $l['editora']['nm_editora'] ?? null;
        unset($l['editora']);
        return $l;
    }, $rows);
}

/**
 * Lista livros com editora e autores concatenados.
 *
 * O embedding aninhado "livro_autor(autor(nm_autor))" percorre:
 *   livro → livro_autor → autor
 *
 * A resposta bruta contém arrays aninhados. Achatamos para que
 * $livro['autores'] seja uma string "Autor A, Autor B".
 */
function listarLivroCompleto() {
    $rows = sbRequest('GET', 'livro', [
        'query' => 'select=id_livro,nm_livro,isbn,ano_publicacao,numero_edicao,editora(nm_editora),livro_autor(autor(nm_autor))&order=id_livro.desc',
    ]);

    return array_map(function ($l) {
        $l['nm_editora'] = $l['editora']['nm_editora'] ?? null;

        $nomes = array_map(
            fn($la) => $la['autor']['nm_autor'] ?? '',
            $l['livro_autor'] ?? []
        );
        $l['autores'] = implode(', ', array_filter($nomes));

        unset($l['editora'], $l['livro_autor']);
        return $l;
    }, $rows);
}

function buscarLivroPorId($id) {
    return buscarPorId('livro', 'id_livro', $id);
}

/**
 * Busca um livro completo para a página de detalhe:
 * título, ISBN, edição, ano, editora (nome + cidade) e autores (nome + nacionalidade).
 */
function buscarLivroDetalhe($id) {
    $rows = sbRequest('GET', 'livro', [
        'query' => "id_livro=eq.$id&select=*,editora(nm_editora,cidade),livro_autor(autor(nm_autor,nacionalidade))",
    ]);
    if (empty($rows)) return null;

    $l = $rows[0];
    $l['nm_editora'] = $l['editora']['nm_editora'] ?? null;
    $l['cidade']     = $l['editora']['cidade']     ?? null;
    $l['autores']    = array_map(
        fn($la) => $la['autor'] ?? [],
        $l['livro_autor'] ?? []
    );
    unset($l['editora'], $l['livro_autor']);
    return $l;
}

/**
 * Busca um livro e a lista de IDs dos seus autores.
 */
function buscarLivroComAutores($id) {
    $livro = buscarLivroPorId($id);
    if (!$livro) return null;

    $livro_autores = sbRequest('GET', 'livro_autor', [
        'query' => "id_livro=eq.$id&select=id_autor",
    ]);

    $livro['autores'] = array_column($livro_autores, 'id_autor');
    return $livro;
}

/**
 * Insere um livro e vincula seus autores.
 *
 * Como não temos transações via REST API, fazemos as operações
 * em sequência. Se uma falhar, a exceção sobe para o controller.
 */
function inserirLivro($dados, $autores = []) {
    $novoLivro = inserir('livro', $dados);
    $id_livro  = $novoLivro['id_livro'];

    foreach ($autores as $id_autor) {
        sbRequest('POST', 'livro_autor', [
            'body' => ['id_livro' => $id_livro, 'id_autor' => (int)$id_autor],
        ]);
    }

    return true;
}

/**
 * Atualiza um livro e recria os vínculos com autores.
 */
function atualizarLivro($id, $dados, $autores = []) {
    atualizar('livro', $dados, 'id_livro', $id);

    // Remove autores antigos e insere os novos
    sbRequest('DELETE', 'livro_autor', ['query' => "id_livro=eq.$id"]);

    foreach ($autores as $id_autor) {
        sbRequest('POST', 'livro_autor', [
            'body' => ['id_livro' => (int)$id, 'id_autor' => (int)$id_autor],
        ]);
    }

    return true;
}

function deletarLivro($id) {
    sbRequest('DELETE', 'livro_autor', ['query' => "id_livro=eq.$id"]);
    return deletar('livro', $id, 'id_livro');
}


/* ================================================================
   EMPRÉSTIMO
================================================================ */

/**
 * Lista empréstimos completos com dados de aluno e livro.
 *
 * Embedding percorre:
 *   emprestimo_livro
 *     → emprestimo → aluno     (para nm_aluno, curso, registro_aluno)
 *     → livro                  (para nm_livro, isbn)
 *
 * Achatamos o resultado para que a view acesse $emp['nm_aluno']
 * da mesma forma que antes.
 */
function listarEmprestimosCompleto() {
    $rows = sbRequest('GET', 'emprestimo_livro', [
        'query' => implode('&', [
            'select=id_emprestimo_livro,dt_emprestimo,dt_devolucao_prevista,dt_devolucao_real,emprestimo(id_emprestimo,registro_aluno,aluno(nm_aluno,curso)),livro(nm_livro,isbn)',
            'order=id_emprestimo_livro.desc',
        ]),
    ]);

    return array_map(function ($row) {
        $emp   = $row['emprestimo'] ?? [];
        $aluno = $emp['aluno']      ?? [];
        $livro = $row['livro']      ?? [];

        return [
            'id_emprestimo_livro'   => $row['id_emprestimo_livro'],
            'dt_emprestimo'         => $row['dt_emprestimo'],
            'dt_devolucao_prevista' => $row['dt_devolucao_prevista'],
            'dt_devolucao_real'     => $row['dt_devolucao_real'],
            'id_emprestimo'         => $emp['id_emprestimo']   ?? null,
            'registro_aluno'        => $emp['registro_aluno']  ?? null,
            'nm_aluno'              => $aluno['nm_aluno']      ?? null,
            'curso'                 => $aluno['curso']         ?? null,
            'nm_livro'              => $livro['nm_livro']      ?? null,
            'isbn'                  => $livro['isbn']          ?? null,
        ];
    }, $rows);
}

/**
 * Marca um exemplar como devolvido, enviando o timestamp atual.
 *
 * A REST API não executa funções SQL como NOW() diretamente,
 * por isso geramos o timestamp no PHP e enviamos como string ISO 8601.
 */
function devolverLivroEmprestado($id_emprestimo_livro) {
    sbRequest('PATCH', 'emprestimo_livro', [
        'query' => "id_emprestimo_livro=eq.$id_emprestimo_livro",
        'body'  => ['dt_devolucao_real' => date('c')],
    ]);
    return true;
}
