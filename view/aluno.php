<?php
/*
  Entidade: [aluno]
  PKs/campos: registro_aluno (pk,ai), nm_aluno, cpf, email, telefone, curso
*/

require_once __DIR__ . "/../controller/alunoController.php";
require_once __DIR__ . "/../app/helpers/faker.php";

$mensagem = "";
$editando = null;
$random   = [];

if (isset($_GET['random'])) {
    $nome = gerarNome();
    $random = [
        'nm_aluno'  => $nome,
        'cpf'       => gerarCPF(),
        'email'     => gerarEmail($nome),
        'telefone'  => gerarTelefone(),
        'curso'     => gerarCurso(),
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!empty($_POST['registro_aluno'])) {
            editarAluno($_POST['registro_aluno'], $_POST);
            $mensagem = "Aluno atualizado com sucesso!";
        } else {
            salvarAluno($_POST);
            $mensagem = "Aluno cadastrado com sucesso!";
        }
    } catch (Exception $e) {
        $mensagem = "Erro: " . $e->getMessage();
    }
}

if (isset($_GET['delete'])) {
    try {
        removerAluno($_GET['delete']);
        header("Location: ?page=aluno"); exit;
    } catch (Exception $e) {
        $mensagem = "Erro: " . $e->getMessage();
    }
}

if (isset($_GET['edit'])) {
    $editando = buscarAlunoPorId($_GET['edit']);
}

$alunos = listarAlunos();
?>

<!-- ══════════════════════ ALUNO ══════════════════════ -->
<div class="stagger space-y-6">

  <!-- Page header -->
  <div>
    <span class="text-xs font-semibold text-emerald-600 uppercase tracking-widest">Cadastro</span>
    <h1 class="text-2xl font-extrabold text-gray-900 mt-0.5 tracking-tight">Alunos</h1>
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
        ✏️ Editando: <strong><?= htmlspecialchars($editando['nm_aluno']) ?></strong>
      </span>
      <a href="?page=aluno" class="text-amber-600 hover:text-amber-700 font-semibold transition-colors">Cancelar</a>
    </div>
  <?php endif; ?>

  <!-- ── FORM CARD ── -->
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <!-- Top accent bar -->
    <div class="h-1" style="background: linear-gradient(90deg, #10b981, #0d9488);"></div>
    <div class="p-6 sm:p-7">
      <div class="mb-6">
        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">
          <?= $editando ? 'Editar registro' : 'Novo registro' ?>
        </span>
        <h2 class="text-xl font-bold text-gray-900 mt-0.5">
          <?= $editando ? 'Atualizar Aluno' : 'Cadastrar Aluno' ?>
        </h2>
      </div>

      <form method="POST" action="?page=aluno" class="space-y-5">

        <?php if ($editando): ?>
          <input type="hidden" name="registro_aluno"
                 value="<?= htmlspecialchars($editando['registro_aluno']) ?>"/>
        <?php endif; ?>

        <!-- nm_aluno + cpf -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="space-y-1.5">
            <label class="block text-sm font-semibold text-gray-700" for="nm_aluno">Nome completo</label>
            <input class="input-field" type="text" id="nm_aluno" name="nm_aluno" required
                   maxlength="150" placeholder="Ex.: João da Silva"
                   value="<?= htmlspecialchars($random['nm_aluno'] ?? $editando['nm_aluno'] ?? '') ?>"/>
          </div>
          <div class="space-y-1.5">
            <label class="block text-sm font-semibold text-gray-700" for="cpf">CPF</label>
            <input class="input-field" type="text" id="cpf" name="cpf" required
                   maxlength="11" placeholder="Somente 11 dígitos"
                   value="<?= htmlspecialchars($random['cpf'] ?? $editando['cpf'] ?? '') ?>"/>
            <p class="text-xs text-gray-400">11 dígitos, sem pontuação</p>
          </div>
        </div>

        <!-- email + telefone -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="space-y-1.5">
            <label class="block text-sm font-semibold text-gray-700" for="email">E-mail</label>
            <input class="input-field" type="email" id="email" name="email" required
                   maxlength="100" placeholder="exemplo@email.com"
                   value="<?= htmlspecialchars($random['email'] ?? $editando['email'] ?? '') ?>"/>
          </div>
          <div class="space-y-1.5">
            <label class="block text-sm font-semibold text-gray-700" for="telefone">Telefone</label>
            <input class="input-field" type="text" id="telefone" name="telefone" required
                   maxlength="20" placeholder="(00) 00000-0000"
                   value="<?= htmlspecialchars($random['telefone'] ?? $editando['telefone'] ?? '') ?>"/>
          </div>
        </div>

        <!-- curso -->
        <div class="space-y-1.5 max-w-md">
          <label class="block text-sm font-semibold text-gray-700" for="curso">Curso</label>
          <input class="input-field" type="text" id="curso" name="curso" required
                 maxlength="100" placeholder="Ex.: Engenharia de Software"
                 value="<?= htmlspecialchars($random['curso'] ?? $editando['curso'] ?? '') ?>"/>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-3 flex-wrap pt-2">
          <button type="submit" class="btn-primary">
            <?= $editando ? '💾 Salvar alterações' : '+ Cadastrar aluno' ?>
          </button>
          <?php if (!$editando): ?>
            <a href="?page=aluno&random=1" class="btn-ghost" title="Preencher com dados aleatórios">🎲 Aleatório</a>
          <?php else: ?>
            <a href="?page=aluno" class="btn-ghost">Cancelar</a>
          <?php endif; ?>
        </div>

      </form>
    </div>
  </div>

  <!-- ── TABLE CARD ── -->
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <!-- Header -->
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
      <div>
        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Registros</span>
        <h2 class="text-lg font-bold text-gray-900 mt-0.5">Lista de Alunos</h2>
      </div>
      <span class="bg-gray-100 text-gray-500 text-xs font-bold px-2.5 py-1 rounded-full">
        <?= count($alunos) ?> <?= count($alunos) === 1 ? 'registro' : 'registros' ?>
      </span>
    </div>

    <?php if (empty($alunos)): ?>
      <!-- Empty state -->
      <div class="flex flex-col items-center justify-center py-16 text-center px-6">
        <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center text-3xl mb-4">🎓</div>
        <h3 class="text-gray-700 font-semibold mb-1">Nenhum aluno cadastrado</h3>
        <p class="text-gray-400 text-sm">Os registros aparecerão aqui após o primeiro cadastro.</p>
      </div>
    <?php else: ?>
      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-gray-100 bg-gray-50/70">
              <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider whitespace-nowrap">Reg.</th>
              <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">Nome</th>
              <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">CPF</th>
              <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">E-mail</th>
              <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">Telefone</th>
              <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">Curso</th>
              <th class="px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">Ações</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <?php foreach ($alunos as $a): ?>
              <tr class="hover:bg-indigo-50/30 group">
                <td class="px-5 py-4 text-gray-400 font-mono text-xs font-semibold whitespace-nowrap">
                  #<?= htmlspecialchars($a['registro_aluno']) ?>
                </td>
                <td class="px-5 py-4">
                  <span class="font-semibold text-gray-900"><?= htmlspecialchars($a['nm_aluno']) ?></span>
                </td>
                <td class="px-5 py-4 font-mono text-xs text-gray-500 whitespace-nowrap">
                  <?= htmlspecialchars($a['cpf']) ?>
                </td>
                <td class="px-5 py-4 text-gray-600 text-xs whitespace-nowrap">
                  <?= htmlspecialchars($a['email']) ?>
                </td>
                <td class="px-5 py-4 text-gray-500 text-xs whitespace-nowrap">
                  <?= htmlspecialchars($a['telefone']) ?>
                </td>
                <td class="px-5 py-4">
                  <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 whitespace-nowrap">
                    <?= htmlspecialchars($a['curso']) ?>
                  </span>
                </td>
                <td class="px-5 py-4">
                  <div class="flex items-center gap-2">
                    <a href="?page=aluno&edit=<?= urlencode($a['registro_aluno']) ?>" class="btn-edit">Editar</a>
                    <a href="?page=aluno&delete=<?= urlencode($a['registro_aluno']) ?>"
                       class="btn-delete"
                       onclick="return confirm('Remover aluno <?= htmlspecialchars(addslashes($a['nm_aluno'])) ?>?')">
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
