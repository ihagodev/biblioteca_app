<?php

require_once __DIR__ . "/../dal/dal.php";

function salvarAluno($dados) {

    if (empty($dados['cpf'])) {
        throw new Exception("CPF obrigatório");
    }

    if (empty($dados['nm_aluno'])) {
        throw new Exception("Nome obrigatório");
    }

    if (empty($dados['email'])) {
        throw new Exception("Email obrigatório");
    }

    if (empty($dados['curso'])) {
        throw new Exception("Curso obrigatório");
    }

    if (empty($dados['telefone'])) {
        throw new Exception("Telefone obrigatório");
    }

    return inserirAluno($dados);
}

function editarAluno($id, $dados) {
    return atualizarAluno($id, $dados);
}

function removerAluno($id) {
    return deletarAluno($id);
}