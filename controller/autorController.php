<?php

require_once __DIR__ . "/../dal/dal.php";

function salvarAutor($dados) {

    if (empty($dados['nm_autor'])) {
        throw new Exception("Nome do autor obrigatório");
    }

    return inserir('autor', $dados);
}

function editarAutor($id, $dados) {

    if (empty($dados['nm_autor'])) {
        throw new Exception("Nome do autor obrigatório");
    }

    return atualizar('autor', $dados, 'id_autor', $id);
}

function removerAutor($id) {
    return deletar('autor', $id, 'id_autor');
}

function listarAutoresController() {
    return listar('autor');
}

function buscarAutorController($id) {
    return buscarPorId('autor', $id, 'id_autor');
}