<?php
/* ═══════════════════════════════════════════
   faker.php — Gerador de dados coerentes
   Inspirado em funcoes.php do projeto CRUD
   ═══════════════════════════════════════════ */

function gerarCPF(): string {
    $n = [];
    for ($i = 0; $i < 9; $i++) $n[$i] = rand(0, 9);

    $d1 = 0;
    for ($i = 0; $i < 9; $i++) $d1 += $n[$i] * (10 - $i);
    $d1 = ($d1 % 11 < 2) ? 0 : 11 - ($d1 % 11);

    $d2 = 0;
    for ($i = 0; $i < 9; $i++) $d2 += $n[$i] * (11 - $i);
    $d2 += $d1 * 2;
    $d2 = ($d2 % 11 < 2) ? 0 : 11 - ($d2 % 11);

    return implode('', $n) . $d1 . $d2;
}

function gerarNome(): string {
    $nomes     = ['João','Maria','Pedro','Ana','Lucas','Carla','Bruno','Fernanda',
                  'Rafael','Juliana','Marcos','Patrícia','Diego','Camila','André'];
    $sobrenomes = ['Silva','Souza','Oliveira','Lima','Costa','Pereira',
                   'Ferreira','Alves','Santos','Rodrigues','Martins'];
    return $nomes[array_rand($nomes)] . ' ' . $sobrenomes[array_rand($sobrenomes)];
}

function gerarEmail(string $nome = ''): string {
    if (!$nome) $nome = 'usuario';
    $slug = strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $nome));
    $slug = preg_replace('/[^a-z]/', '', $slug);
    $dominios = ['gmail.com','hotmail.com','outlook.com','yahoo.com.br'];
    return $slug . rand(1, 999) . '@' . $dominios[array_rand($dominios)];
}

function gerarTelefone(): string {
    $ddd = ['11','21','31','41','51','61','71','81','85','91'];
    return '(' . $ddd[array_rand($ddd)] . ') 9' . rand(1000, 9999) . '-' . rand(1000, 9999);
}

function gerarCurso(): string {
    $cursos = [
        'Administração', 'Direito', 'Engenharia Civil',
        'Engenharia de Software', 'Ciência da Computação',
        'Sistemas de Informação', 'Medicina', 'Enfermagem',
        'Psicologia', 'Arquitetura e Urbanismo', 'Contabilidade',
        'Economia', 'Marketing', 'Design Gráfico', 'Farmácia',
        'Biomedicina', 'Análise e Desenvolvimento de Sistemas',
    ];
    return $cursos[array_rand($cursos)];
}

function gerarNacionalidade(): string {
    $paises = ['Brasileiro','Argentino','Chileno','Português','Italiano',
               'Americano','Francês','Espanhol','Alemão','Mexicano'];
    return $paises[array_rand($paises)];
}

function gerarCidade(): string {
    $cidades = ['São Paulo','Rio de Janeiro','Campinas','Curitiba',
                'Belo Horizonte','Fortaleza','Recife','Salvador',
                'Porto Alegre','Manaus','Florianópolis','Goiânia'];
    return $cidades[array_rand($cidades)];
}

function gerarNomeEditora(): string {
    $bases = ['Atlas','Saraiva','Moderna','Cultrix','Record',
              'Companhia das Letras','Rocco','Objetiva','Planeta','Ática'];
    return 'Editora ' . $bases[array_rand($bases)];
}

function gerarTituloLivro(): string {
    $adjetivos = ['Moderno','Avançado','Completo','Prático','Essencial','Fundamental'];
    $temas     = ['de Algoritmos','de Banco de Dados','de Redes','de Sistemas',
                  'de Programação','de Estruturas de Dados','de Arquitetura',
                  'de Inteligência Artificial','de Engenharia de Software'];
    return 'Guia ' . $adjetivos[array_rand($adjetivos)] . ' ' . $temas[array_rand($temas)];
}

function gerarIsbn(): string {
    // Prefixo ISBN-13 válido: 978-85-XXXXX-XX-X
    $grupo    = str_pad(rand(100, 99999), 5, '0', STR_PAD_LEFT);
    $editora  = str_pad(rand(10, 99), 2, '0', STR_PAD_LEFT);
    $base     = "97885{$grupo}{$editora}";

    // calcula dígito verificador
    $soma = 0;
    for ($i = 0; $i < 12; $i++) {
        $soma += (int)$base[$i] * ($i % 2 === 0 ? 1 : 3);
    }
    $check = (10 - ($soma % 10)) % 10;

    return "978-85-{$grupo}-{$editora}-{$check}";
}

function gerarAnoPublicacao(): int {
    return rand(1990, (int)date('Y'));
}

function gerarEdicao(): int {
    return rand(1, 5);
}

function gerarDtDevolucaoPrevista(): string {
    // Entre 7 e 30 dias no futuro
    return date('Y-m-d\TH:i', strtotime('+' . rand(7, 30) . ' days'));
}
