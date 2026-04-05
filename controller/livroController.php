<?php

require_once __DIR__ . "/../dal/dal.php";

$camposLivro = ['nm_livro', 'isbn', 'ano_publicacao', 'numero_edicao', 'id_editora'];

function salvarLivro($dados, $autores) {
    global $camposLivro;

    if (empty($dados['nm_livro'])) {
        throw new Exception("Nome do livro obrigatório");
    }

    if (empty($dados['isbn'])) {
        throw new Exception("ISBN obrigatório");
    }

    if (empty($dados['id_editora'])) {
        throw new Exception("Editora obrigatória");
    }

    return inserirLivro(array_intersect_key($dados, array_flip($camposLivro)), $autores);
}

function editarLivro($id, $dados, $autores) {
    global $camposLivro;

    if (empty($dados['nm_livro'])) {
        throw new Exception("Nome do livro obrigatório");
    }

    if (empty($dados['isbn'])) {
        throw new Exception("ISBN obrigatório");
    }

    if (empty($dados['id_editora'])) {
        throw new Exception("Editora obrigatória");
    }

    return atualizarLivro($id, array_intersect_key($dados, array_flip($camposLivro)), $autores);
}

function removerLivro($id) {
    return deletarLivro($id);
}

function listarLivrosController() {
    return listarLivroCompleto();
}

function buscarLivroController($id) {
    return buscarLivroComAutores($id);
}