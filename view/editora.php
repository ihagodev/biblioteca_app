<?php
/*
  Entidade: [editora]
  Campos: id_editora (pk,ai), nm_editora, cidade
*/

require_once __DIR__ . "/../dal/dal.php";
require_once __DIR__ . "/../app/helpers/faker.php";

$mensagem = "";
$editando = null;
$random   = [];

if (isset($_GET['random'])) {
    $random = [
        'nm_editora' => gerarNomeEditora(),
        'cidade'     => gerarCidade(),
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (empty($_POST['nm_editora'])) throw new Exception("Nome da editora é obrigatório.");

        if (!empty($_POST['id_editora'])) {
            atualizar('editora', $_POST, 'id_editora');
            $mensagem = "Editora atualizada!";
        } else {
            inserir('editora', $_POST);
            $mensagem = "Editora cadastrada!";
        }
    } catch (Exception $e) {
        $mensagem = "Erro: " . $e->getMessage();
    }
}

if (isset($_GET['delete'])) {
    try {
        deletar('editora', $_GET['delete'], 'id_editora');
        header("Location: ?page=editora"); exit;
    } catch (Exception $e) {
        $mensagem = "Erro: " . $e->getMessage();
    }
}

if (isset($_GET['edit'])) {
    $editando = buscarPorId('editora', $_GET['edit'], 'id_editora');
}

$editoras = listar('editora');
?>

<!-- ══════════════════════ EDITORA ══════════════════════ -->
<div class="stagger space-y-6">

  <!-- Page header -->
  <div>
    <span class="text-xs font-semibold text-rose-600 uppercase tracking-widest">Referência</span>
    <h1 class="text-2xl font-extrabold text-gray-900 mt-0.5 tracking-tight">Editoras</h1>
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
        ✏️ Editando: <strong><?= htmlspecialchars($editando['nm_editora']) ?></strong>
      </span>
      <a href="?page=editora" class="text-amber-600 hover:text-amber-700 font-semibold transition-colors">Cancelar</a>
    </div>
  <?php endif; ?>

  <!-- ── FORM CARD ── -->
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
    <div class="h-1 rounded-t-2xl" style="background: linear-gradient(90deg, #f43f5e, #f97316);"></div>
    <div class="p-6 sm:p-7">
      <div class="mb-6">
        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">
          <?= $editando ? 'Editar registro' : 'Novo registro' ?>
        </span>
        <h2 class="text-xl font-bold text-gray-900 mt-0.5">
          <?= $editando ? 'Atualizar Editora' : 'Cadastrar Editora' ?>
        </h2>
      </div>

      <form method="POST" action="?page=editora" class="space-y-5">

        <?php if ($editando): ?>
          <input type="hidden" name="id_editora"
                 value="<?= htmlspecialchars($editando['id_editora']) ?>"/>
        <?php endif; ?>

        <!-- nm_editora + cidade -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="space-y-1.5">
            <label class="block text-sm font-semibold text-gray-700" for="nm_editora">Nome da editora</label>
            <input class="input-field" type="text" id="nm_editora" name="nm_editora" required
                   maxlength="150" placeholder="Ex.: Editora Atlas"
                   value="<?= htmlspecialchars($random['nm_editora'] ?? $editando['nm_editora'] ?? '') ?>"/>
          </div>
          <div class="space-y-1.5">
            <label class="block text-sm font-semibold text-gray-700" for="cidade">Cidade</label>
            <input class="input-field" type="text" id="cidade" name="cidade" required
                   maxlength="50" placeholder="Ex.: São Paulo"
                   value="<?= htmlspecialchars($random['cidade'] ?? $editando['cidade'] ?? '') ?>"/>
          </div>
        </div>

        <div class="flex items-center gap-3 flex-wrap pt-2">
          <button type="submit" class="btn-primary">
            <?= $editando ? '💾 Salvar alterações' : '+ Cadastrar editora' ?>
          </button>
          <?php if (!$editando): ?>
            <a href="?page=editora&random=1" class="btn-ghost" title="Preencher com dados aleatórios">🎲 Aleatório</a>
          <?php else: ?>
            <a href="?page=editora" class="btn-ghost">Cancelar</a>
          <?php endif; ?>
        </div>

      </form>
    </div>
  </div>

  <!-- ── TABLE CARD ── -->
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
    <div class="px-6 py-4 border-b border-gray-100">
      <div class="flex items-center justify-between mb-3">
        <div>
          <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Registros</span>
          <h2 class="text-lg font-bold text-gray-900 mt-0.5">Lista de Editoras</h2>
        </div>
        <span class="bg-gray-100 text-gray-500 text-xs font-bold px-2.5 py-1 rounded-full shrink-0">
          <?= count($editoras) ?> <?= count($editoras) === 1 ? 'registro' : 'registros' ?>
        </span>
      </div>
      <div class="relative">
        <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 pointer-events-none">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
          </svg>
        </span>
        <input type="text" class="input-field pl-9 text-sm" placeholder="Buscar por nome ou cidade…"
          oninput="filtrarTabela(this,'editora-tbody')">
      </div>
    </div>

    <?php if (empty($editoras)): ?>
      <div class="flex flex-col items-center justify-center py-16 text-center px-6">
        <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center text-3xl mb-4">🏛️</div>
        <h3 class="text-gray-700 font-semibold mb-1">Nenhuma editora cadastrada</h3>
        <p class="text-gray-400 text-sm">Os registros aparecerão aqui após o primeiro cadastro.</p>
      </div>
    <?php else: ?>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-gray-100 bg-gray-50/70">
              <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">ID</th>
              <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">Nome</th>
              <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">Cidade</th>
              <th class="px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">Ações</th>
            </tr>
          </thead>
          <tbody id="editora-tbody" class="divide-y divide-gray-50">
            <tr id="editora-tbody-empty" style="display:none;">
              <td colspan="4" class="px-5 py-8 text-center text-sm text-gray-400">Nenhum resultado encontrado.</td>
            </tr>
            <?php foreach ($editoras as $e): ?>
              <tr class="hover:bg-indigo-50/30 group filterable">
                <td class="px-5 py-4 text-gray-400 font-mono text-xs font-semibold">#<?= htmlspecialchars($e['id_editora']) ?></td>
                <td class="px-5 py-4">
                  <span class="font-semibold text-gray-900"><?= htmlspecialchars($e['nm_editora']) ?></span>
                </td>
                <td class="px-5 py-4">
                  <span class="inline-flex items-center gap-1 text-gray-500 text-xs">
                    <span class="text-gray-400">📍</span>
                    <?= htmlspecialchars($e['cidade']) ?>
                  </span>
                </td>
                <td class="px-5 py-4">
                  <div class="flex items-center gap-2">
                    <a href="?page=editora&edit=<?= urlencode($e['id_editora']) ?>" class="btn-edit">Editar</a>
                    <a href="?page=editora&delete=<?= urlencode($e['id_editora']) ?>"
                       class="btn-delete"
                       onclick="return confirm('Remover editora <?= htmlspecialchars(addslashes($e['nm_editora'])) ?>?')">
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
