# Biblioteca App

**Acesse em: [biblioteca.ihago.dev](https://biblioteca.ihago.dev)**

Sistema de gerenciamento de biblioteca escolar em PHP puro, com banco de dados hospedado no Supabase (PostgreSQL) acessado via REST API.

## Arquitetura

```mermaid
flowchart LR
    user(["Usuário"])
    cf["Cloudflare\nDNS / Proxy"]
    railway["Railway\nPHP 8.2 Built-in Server"]
    gh["GitHub\nrepositório"]
    sb["Supabase\nREST API (PostgREST)"]
    pg[("PostgreSQL\nschema: lib")]

    user -->|"biblioteca.ihago.dev"| cf
    cf -->|"proxy"| railway
    gh -->|"push → auto deploy"| railway
    railway -->|"HTTP curl\n/rest/v1/tabela"| sb
    sb -->|"query"| pg
```

## Funcionalidades

| Módulo | O que faz |
|---|---|
| **Alunos** | Cadastrar, editar e remover alunos |
| **Livros** | Cadastrar livros com editora e múltiplos autores, editar e remover |
| **Autores** | Manter catálogo de autores |
| **Editoras** | Manter catálogo de editoras |
| **Empréstimos** | Registrar empréstimos de múltiplos livros, controlar devolução com status (Em aberto / Atrasado / Devolvido) |

Todos os formulários possuem um botão **Aleatório** que gera e envia dados fictícios automaticamente.

## Stack

- **PHP 8.2** — sem frameworks, sem dependências externas
- **Supabase** — banco PostgreSQL hospedado, acessado via REST API (PostgREST)
- **cURL** — biblioteca padrão do PHP para as requisições HTTP
- **Tailwind CSS** — via CDN, sem build step

## Por que REST API em vez de conexão direta ao banco?

Ambientes de produção em cloud (Railway, Render, Cloudflare, etc.) bloqueiam conexões TCP diretas na porta 5432. O Supabase expõe os mesmos dados via HTTP na porta 443, que nunca é bloqueada. Todo acesso ao banco passa pela função `sbRequest()` em `app/config/supabase.php`.

## Estrutura do projeto

```
biblioteca_app/
├── Dockerfile                 # Imagem PHP 8.2-cli, porta 8080
├── index.php                  # Entrada única — roteamento por ?page=
│
├── app/
│   ├── config/
│   │   └── supabase.php       # Cliente HTTP — função sbRequest()
│   ├── assets/
│   │   └── style.css
│   └── helpers/
│       └── faker.php          # Gerador de dados aleatórios para testes
│
├── dal/
│   └── dal.php                # Data Access Layer — todas as queries via REST API
│
├── controller/               # Tratamento de exceções
│   ├── alunoController.php
│   ├── livroController.php
│   ├── autorController.php
│   ├── editoraController.php
│   └── emprestimoController.php
│
└── view/                  # Páginas web
    ├── aluno.php
    ├── livro.php
    ├── autor.php
    ├── editora.php
    ├── emprestimo.php
    └── layout/
        ├── header.php
        └── footer.php
```
