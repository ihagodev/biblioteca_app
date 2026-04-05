# Biblioteca App

Sistema de gerenciamento de biblioteca escolar em PHP puro, com banco de dados hospedado no Supabase (PostgreSQL) acessado via REST API.

## Funcionalidades

| Módulo | O que faz |
|---|---|
| **Alunos** | Cadastrar, editar e remover alunos |
| **Livros** | Cadastrar livros com editora e múltiplos autores, editar e remover |
| **Autores** | Manter catálogo de autores |
| **Editoras** | Manter catálogo de editoras |
| **Empréstimos** | Registrar empréstimos de múltiplos livros, controlar devolução com status (Em aberto / Atrasado / Devolvido) |

Todos os formulários possuem um botão **Aleatório** que gera dados fictícios para testes.

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
├── index.php                  # Entrada única — roteamento por ?page=
├── composer.json              # Declara requisito PHP 8.2 (usado pelo Railway)
│
├── app/
│   ├── config/
│   │   ├── supabase.php       # Cliente HTTP — função sbRequest()
│   │   └── db.php             # Conexão MySQL local (apenas dev local)
│   ├── assets/
│   │   └── style.css
│   └── helpers/
│       └── faker.php          # Gerador de dados aleatórios para testes
│
├── dal/
│   └── dal.php                # Data Access Layer — todas as queries via REST API
│
├── controller/
│   ├── alunoController.php
│   ├── livroController.php
│   ├── autorController.php
│   ├── editoraController.php
│   └── emprestimoController.php
│
└── view/
    ├── aluno.php
    ├── livro.php
    ├── autor.php
    ├── editora.php
    ├── emprestimo.php
    └── layout/
        ├── header.php
        └── footer.php
```

## Desenvolvimento local (XAMPP)

### Pré-requisitos

- XAMPP com PHP 8.2+ e Apache
- Extensão `curl` habilitada (está ativa por padrão no XAMPP)

### Configuração

1. Clone o repositório dentro de `C:\xampp\htdocs\`:

```bash
git clone <url-do-repo> C:\xampp\htdocs\biblioteca_app
```

2. Abra `app/config/supabase.php` e substitua o valor da chave:

```php
define('SUPABASE_KEY', getenv('SUPABASE_KEY') ?: 'sua_service_role_key_aqui');
```

> A chave está em: **Supabase Dashboard → seu projeto → Settings → API → service_role**

3. Inicie o Apache no XAMPP e acesse `http://localhost/biblioteca_app`.

> O arquivo `app/config/config.ini` (credenciais MySQL local) não está no repositório e não é necessário para o funcionamento online — o sistema usa exclusivamente a REST API do Supabase.

## Deploy no Railway

### 1. Criar repositório no GitHub

Faça push da pasta `biblioteca_app` como raiz do repositório:

```bash
git init
git add .
git commit -m "first commit"
git remote add origin https://github.com/seu-usuario/biblioteca-app.git
git push -u origin main
```

### 2. Criar projeto no Railway

1. Acesse [railway.app](https://railway.app) e faça login
2. **New Project → Deploy from GitHub repo**
3. Selecione o repositório criado no passo anterior
4. O Railway detecta o `composer.json` e configura PHP 8.2 automaticamente

### 3. Adicionar a variável de ambiente

No painel do Railway, acesse seu serviço e vá em:

**Variables → New Variable**

| Variável | Valor |
|---|---|
| `SUPABASE_KEY` | sua `service_role` key do Supabase |

> Sem essa variável, todas as requisições ao banco retornarão erro 401.

### 4. Deploy

O Railway faz deploy automático após cada push no branch `main`. A URL pública é gerada em **Settings → Networking → Generate Domain**.

## Variáveis de ambiente

| Variável | Onde definir | Descrição |
|---|---|---|
| `SUPABASE_KEY` | Railway → Variables | `service_role` key do projeto Supabase |

O arquivo `.env.example` na raiz do projeto documenta as variáveis necessárias.

## Schema do banco

As tabelas estão no schema `lib` do projeto Supabase. O schema é definido em `app/config/supabase.php`:

```php
define('SUPABASE_SCHEMA', 'lib');
```

Tabelas principais: `aluno`, `livro`, `autor`, `editora`, `livro_autor`, `emprestimo`, `emprestimo_livro`.
