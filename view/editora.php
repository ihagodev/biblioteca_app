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
        if ($e->getCode() === '23000' || strpos($e->getMessage(), '23000') !== false) {
            $mensagem = "Erro: Esta editora está vinculada a livros e não pode ser removida.";
        } else {
            $mensagem = "Erro: " . $e->getMessage();
        }
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
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="h-1" style="background: linear-gradient(90deg, #f43f5e, #f97316);"></div>
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
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
      <div>
        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Registros</span>
        <h2 class="text-lg font-bold text-gray-900 mt-0.5">Lista de Editoras</h2>
      </div>
      <span class="bg-gray-100 text-gray-500 text-xs font-bold px-2.5 py-1 rounded-full">
        <?= count($editoras) ?> <?= count($editoras) === 1 ? 'registro' : 'registros' ?>
      </span>
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
          <tbody class="divide-y divide-gray-50">
            <?php foreach ($editoras as $e): ?>
              <tr class="hover:bg-indigo-50/30 group">
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
