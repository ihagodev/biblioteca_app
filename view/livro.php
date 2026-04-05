<?php
/*
  Entidade: [livro]
  Campos: id_livro (pk,ai), nm_livro, isbn (unique), ano_publicacao,
          numero_edicao, id_editora (fk)
  Relação: [livro_autor] id_livro + id_autor
*/

require_once __DIR__ . "/../controller/livroController.php";
require_once __DIR__ . "/../dal/dal.php";
require_once __DIR__ . "/../app/helpers/faker.php";

$mensagem = "";
$editando = null;
$autoresSelecionados = [];
$random = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $autores = $_POST['autores'] ?? [];

        if (!empty($_POST['id_livro'])) {
            editarLivro($_POST['id_livro'], $_POST, $autores);
            $mensagem = "Livro atualizado!";
        } else {
            salvarLivro($_POST, $autores);
            $mensagem = "Livro cadastrado!";
        }
    } catch (Exception $e) {
        $mensagem = "Erro: " . $e->getMessage();
    }
}

if (isset($_GET['delete'])) {
    try {
        removerLivro($_GET['delete']);
        header("Location: ?page=livro"); exit;
    } catch (Exception $e) {
        $mensagem = "Erro: " . $e->getMessage();
    }
}

if (isset($_GET['edit'])) {
    $livroComAutores     = buscarLivroComAutores($_GET['edit']);
    $editando            = $livroComAutores;
    $autoresSelecionados = $livroComAutores['autores'] ?? [];
}

$livros   = listarLivroCompleto();
$editoras = listar('editora');
$autores  = listar('autor');

if (isset($_GET['random']) && !empty($editoras) && !empty($autores)) {
    $edRand = $editoras[array_rand($editoras)];
    $auRand = $autores[array_rand($autores)];
    $random = [
        'nm_livro'       => gerarTituloLivro(),
        'isbn'           => gerarIsbn(),
        'ano_publicacao' => gerarAnoPublicacao(),
        'numero_edicao'  => gerarEdicao(),
        'id_editora'     => $edRand['id_editora'],
    ];
    $autoresSelecionados = [$auRand['id_autor']];
}
?>

<!-- ══════════════════════ LIVRO ══════════════════════ -->
<div class="stagger space-y-6">

  <!-- Page header -->
  <div>
    <span class="text-xs font-semibold text-blue-600 uppercase tracking-widest">Acervo</span>
    <h1 class="text-2xl font-extrabold text-gray-900 mt-0.5 tracking-tight">Livros</h1>
  </div>

  <!-- Alert -->
  <?php if ($mensagem): ?>
    <div class="alert-slide flex items-center gap-3 px-4 py-3.5 rounded-xl text-sm font-medium border
      <?= str_starts_with($mensagem,'Erro')
        ? 'bg-red-50 text-red-700 border-red-200'
        : 'bg-emerald-50 text-emerald-700 border-emerald-200' ?>">
      <span class="text-base"><?= str_starts_with($mensagem,'Erro') ? '⚠️' : '✅' ?></span>
      <?= htmlspecialchars($mensagem) ?>
    </div>
  <?php endif; ?>

  <!-- Edit banner -->
  <?php if ($editando): ?>
    <div class="flex items-center justify-between px-4 py-3 bg-amber-50 border border-amber-200 rounded-xl text-sm">
      <span class="text-amber-800 font-medium">
        ✏️ Editando: <strong><?= htmlspecialchars($editando['nm_livro']) ?></strong>
      </span>
      <a href="?page=livro" class="text-amber-600 hover:text-amber-700 font-semibold transition-colors">Cancelar</a>
    </div>
  <?php endif; ?>

  <!-- ── FORM CARD ── -->
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="h-1" style="background: linear-gradient(90deg, #3b82f6, #6366f1);"></div>
    <div class="p-6 sm:p-7">
      <div class="mb-6">
        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">
          <?= $editando ? 'Editar registro' : 'Novo registro' ?>
        </span>
        <h2 class="text-xl font-bold text-gray-900 mt-0.5">
          <?= $editando ? 'Atualizar Livro' : 'Cadastrar Livro' ?>
        </h2>
      </div>

      <form method="POST" action="?page=livro" class="space-y-5">

        <?php if ($editando): ?>
          <input type="hidden" name="id_livro"
                 value="<?= htmlspecialchars($editando['id_livro']) ?>"/>
        <?php endif; ?>

        <!-- nm_livro -->
        <div class="space-y-1.5">
          <label class="block text-sm font-semibold text-gray-700" for="nm_livro">Título do livro</label>
          <input class="input-field" type="text" id="nm_livro" name="nm_livro" required
                 maxlength="150" placeholder="Ex.: Clean Code"
                 value="<?= htmlspecialchars($random['nm_livro'] ?? $editando['nm_livro'] ?? '') ?>"/>
        </div>

        <!-- isbn + ano_publicacao + numero_edicao -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div class="space-y-1.5">
            <label class="block text-sm font-semibold text-gray-700" for="isbn">ISBN</label>
            <input class="input-field" type="text" id="isbn" name="isbn" required
                   maxlength="20" placeholder="Ex.: 978-85-..."
                   value="<?= htmlspecialchars($random['isbn'] ?? $editando['isbn'] ?? '') ?>"/>
          </div>
          <div class="space-y-1.5">
            <label class="block text-sm font-semibold text-gray-700" for="ano_publicacao">Ano de publicação</label>
            <input class="input-field" type="number" id="ano_publicacao" name="ano_publicacao" required
                   min="1000" max="<?= date('Y') ?>" placeholder="<?= date('Y') ?>"
                   value="<?= htmlspecialchars($random['ano_publicacao'] ?? $editando['ano_publicacao'] ?? '') ?>"/>
          </div>
          <div class="space-y-1.5">
            <label class="block text-sm font-semibold text-gray-700" for="numero_edicao">Nº de edição</label>
            <input class="input-field" type="number" id="numero_edicao" name="numero_edicao" required
                   min="1" placeholder="1"
                   value="<?= htmlspecialchars($random['numero_edicao'] ?? $editando['numero_edicao'] ?? '') ?>"/>
          </div>
        </div>

        <!-- id_editora -->
        <div class="space-y-1.5 max-w-sm">
          <label class="block text-sm font-semibold text-gray-700" for="id_editora">Editora</label>
          <select class="input-field" id="id_editora" name="id_editora" required>
            <option value="">— Selecione a editora —</option>
            <?php foreach ($editoras as $ed): ?>
              <option value="<?= htmlspecialchars($ed['id_editora']) ?>"
                <?= (($random['id_editora'] ?? null) == $ed['id_editora'] || (isset($editando['id_editora']) && $editando['id_editora'] == $ed['id_editora'])) ? 'selected' : '' ?>>
                <?= htmlspecialchars($ed['nm_editora']) ?> — <?= htmlspecialchars($ed['cidade']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- autores[] -->
        <div class="space-y-1.5">
          <label class="block text-sm font-semibold text-gray-700" for="autores">Autores</label>
          <select class="input-field" id="autores" name="autores[]" multiple required>
            <?php foreach ($autores as $au): ?>
              <option value="<?= htmlspecialchars($au['id_autor']) ?>"
                <?= in_array($au['id_autor'], $autoresSelecionados) ? 'selected' : '' ?>>
                <?= htmlspecialchars($au['nm_autor']) ?> — <?= htmlspecialchars($au['nacionalidade']) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <p class="text-xs text-gray-400">Segure Ctrl (ou ⌘) para selecionar múltiplos autores</p>
        </div>

        <div class="flex items-center gap-3 flex-wrap pt-2">
          <button type="submit" class="btn-primary">
            <?= $editando ? '💾 Salvar alterações' : '+ Cadastrar livro' ?>
          </button>
          <?php if (!$editando): ?>
            <a href="?page=livro&random=1" class="btn-ghost" title="Preencher com dados aleatórios">🎲 Aleatório</a>
          <?php else: ?>
            <a href="?page=livro" class="btn-ghost">Cancelar</a>
          <?php endif; ?>
        </div>

      </form>
    </div>
  </div>

  <!-- ── TABLE CARD ── -->
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
      <div>
        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Registros</span>
        <h2 class="text-lg font-bold text-gray-900 mt-0.5">Lista de Livros</h2>
      </div>
      <span class="bg-gray-100 text-gray-500 text-xs font-bold px-2.5 py-1 rounded-full">
        <?= count($livros) ?> <?= count($livros) === 1 ? 'registro' : 'registros' ?>
      </span>
    </div>

    <?php if (empty($livros)): ?>
      <div class="flex flex-col items-center justify-center py-16 text-center px-6">
        <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center text-3xl mb-4">📖</div>
        <h3 class="text-gray-700 font-semibold mb-1">Nenhum livro cadastrado</h3>
        <p class="text-gray-400 text-sm">Os registros aparecerão aqui após o primeiro cadastro.</p>
      </div>
    <?php else: ?>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-gray-100 bg-gray-50/70">
              <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">ID</th>
              <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">Título</th>
              <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">ISBN</th>
              <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider whitespace-nowrap">Edição / Ano</th>
              <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">Editora</th>
              <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">Autor(es)</th>
              <th class="px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">Ações</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <?php foreach ($livros as $l): ?>
              <tr class="hover:bg-indigo-50/30 group">
                <td class="px-5 py-4 text-gray-400 font-mono text-xs font-semibold">#<?= htmlspecialchars($l['id_livro']) ?></td>
                <td class="px-5 py-4">
                  <span class="font-semibold text-gray-900"><?= htmlspecialchars($l['nm_livro']) ?></span>
                </td>
                <td class="px-5 py-4 font-mono text-xs text-gray-500 whitespace-nowrap">
                  <?= htmlspecialchars($l['isbn']) ?>
                </td>
                <td class="px-5 py-4 text-center whitespace-nowrap">
                  <span class="font-semibold text-gray-800"><?= htmlspecialchars($l['numero_edicao']) ?>ª ed.</span>
                  <div class="text-xs text-gray-400 mt-0.5"><?= htmlspecialchars($l['ano_publicacao']) ?></div>
                </td>
                <td class="px-5 py-4 text-gray-600 text-xs whitespace-nowrap">
                  <?= htmlspecialchars($l['nm_editora'] ?? '—') ?>
                </td>
                <td class="px-5 py-4 text-gray-500 text-xs" style="max-width:200px;">
                  <?= htmlspecialchars($l['autores'] ?? $l['nm_autor'] ?? '—') ?>
                </td>
                <td class="px-5 py-4">
                  <div class="flex items-center gap-2">
                    <a href="?page=livro&edit=<?= urlencode($l['id_livro']) ?>" class="btn-edit">Editar</a>
                    <a href="?page=livro&delete=<?= urlencode($l['id_livro']) ?>"
                       class="btn-delete"
                       onclick="return confirm('Remover livro <?= htmlspecialchars(addslashes($l['nm_livro'])) ?>?')">
                      Remover
                    </a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

</div>
