<?php

require_once __DIR__ . "/../dal/dal.php";

/**
 * Cria um empréstimo com um ou mais livros.
 *
 * Antes usávamos uma transação PDO para garantir atomicidade.
 * A REST API não suporta transações nativas, então fazemos:
 *   1. POST em 'emprestimo' → obtemos o id_emprestimo gerado
 *   2. POST em 'emprestimo_livro' para cada livro selecionado
 *
 * Se o passo 2 falhar, a exceção sobe para a view que exibe o erro.
 */
function criarEmprestimo($registro_aluno, $livros, $dt_prevista) {

    // 1. Cria o empréstimo e obtém o ID gerado pelo banco
    $novoEmprestimo = inserir('emprestimo', [
        'registro_aluno' => $registro_aluno,
    ]);

    $id_emprestimo = $novoEmprestimo['id_emprestimo'];

    // 2. Vincula cada livro ao empréstimo
    $dataAtual = date('c'); // ISO 8601, ex: 2025-06-01T14:30:00-03:00

    foreach ($livros as $id_livro) {
        sbRequest('POST', 'emprestimo_livro', [
            'body' => [
                'id_emprestimo'          => $id_emprestimo,
                'id_livro'               => (int)$id_livro,
                'dt_emprestimo'          => $dataAtual,
                'dt_devolucao_prevista'  => $dt_prevista,
            ],
        ]);
    }

    return true;
}

function devolverEmprestimoLivro($id_emprestimo_livro) {
    return devolverLivroEmprestado($id_emprestimo_livro);
}
