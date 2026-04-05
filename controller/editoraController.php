<?php

require_once __DIR__ . "/../dal/dal.php";

function salvarEditora($dados) {

    if (empty($dados['nm_editora'])) {
        throw new Exception("Nome da editora obrigatório");
    }

    return inserir('editora', $dados);
}

function editarEditora($id, $dados) {

    if (empty($dados['nm_editora'])) {
        throw new Exception("Nome da editora obrigatório");
    }

    return atualizar('editora', $dados, 'id_editora', $id);
}

function removerEditora($id) {
    return deletar('editora', $id, 'id_editora');
}

function listarEditorasController() {
    return listar('editora');
}

function buscarEditoraController($id) {
    return buscarPorId('editora', $id, 'id_editora');
}