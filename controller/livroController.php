<?php

require_once __DIR__ . "/../dal/dal.php";

function salvarLivro($dados, $autores) {

    if (empty($dados['nm_livro'])) {
        throw new Exception("Nome do livro obrigatório");
    }

    if (empty($dados['isbn'])) {
        throw new Exception("ISBN obrigatório");
    }

    if (empty($dados['id_editora'])) {
        throw new Exception("Editora obrigatória");
    }

    return inserirLivro($dados, $autores);
}

function editarLivro($id, $dados, $autores) {

    if (empty($dados['nm_livro'])) {
        throw new Exception("Nome do livro obrigatório");
    }

    if (empty($dados['isbn'])) {
        throw new Exception("ISBN obrigatório");
    }

    if (empty($dados['id_editora'])) {
        throw new Exception("Editora obrigatória");
    }

    return atualizarLivro($id, $dados, $autores);
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