<?php
/*
  Entidades envolvidas:
  [emprestimo]      id_emprestimo (pk,ai), registro_aluno (fk)
  [emprestimo_livro] id_emprestimo_livro (pk,ai), id_emprestimo (fk),
                     id_livro (fk), dt_emprestimo, dt_devolucao_prevista, dt_devolucao_real
  [aluno]           registro_aluno (pk), nm_aluno, curso
  [livro]           id_livro (pk), nm_livro, isbn
*/

require_once __DIR__ . "/../controller/emprestimoController.php";
require_once __DIR__ . "/../dal/dal.php";
require_once __DIR__ . "/../app/helpers/faker.php";

// DEVOLUÇÃO → usa id_emprestimo_livro (pk de emprestimo_livro)
if (isset($_GET['devolver'])) {
    try {
        devolverEmprestimoLivro($_GET['devolver']);
        header("Location: ?page=emprestimo"); exit;
    } catch (Exception $e) {
        echo "Erro ao devolver: " . htmlspecialchars($e->getMessage());
    }
}

$mensagem = "";

// CADASTRO
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        criarEmprestimo(
            $_POST['registro_aluno'],
            $_POST['livros'] ?? [],
            $_POST['dt_devolucao_prevista']
        );
        $mensagem = "Empréstimo realizado com sucesso!";
    } catch (Exception $e) {
        $mensagem = "Erro: " . $e->getMessage();
    }
}

$alunos = listar('aluno');
$livros = listar('livro');
$dados  = listarEmprestimosCompleto();

$randomEmp = [];
if (isset($_GET['random']) && !empty($alunos) && !empty($livros)) {
    $aRand = $alunos[array_rand($alunos)];
    $lRand = $livros[array_rand($livros)];
    $randomEmp = [
        'registro_aluno'       => $aRand['registro_aluno'],
        'livros'               => [$lRand['id_livro']],
        'dt_devolucao_prevista'=> gerarDtDevolucaoPrevista(),
    ];
}
?>

<!-- ══════════════════════ EMPRÉSTIMO ══════════════════════ -->
<div class="stagger space-y-6">

  <!-- Page header -->
  <div>
    <span class="text-xs font-semibold text-amber-600 uppercase tracking-widest">Circulação</span>
    <h1 class="text-2xl font-extrabold text-gray-900 mt-0.5 tracking-tight">Empréstimos</h1>
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

  <!-- ── FORM CARD ── -->
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="h-1" style="background: linear-gradient(90deg, #f59e0b, #f97316);"></div>
    <div class="p-6 sm:p-7">
      <div class="mb-6">
        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Novo registro</span>
        <h2 class="text-xl font-bold text-gray-900 mt-0.5">Realizar Empréstimo</h2>
      </div>

      <form method="POST" action="?page=emprestimo" class="space-y-5">

        <!-- registro_aluno -->
        <div class="space-y-1.5">
          <label class="block text-sm font-semibold text-gray-700" for="registro_aluno">Aluno</label>
          <select class="input-field" id="registro_aluno" name="registro_aluno" required>
            <option value="">— Selecione o aluno —</option>
            <?php foreach ($alunos as $a): ?>
              <option value="<?= htmlspecialchars($a['registro_aluno']) ?>"
                <?= ($randomEmp['registro_aluno'] ?? null) == $a['registro_aluno'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($a['nm_aluno']) ?>
                — <?= htmlspecialchars($a['curso']) ?>
                (Reg. <?= htmlspecialchars($a['registro_aluno']) ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- livros[] -->
        <div class="space-y-1.5">
          <label class="block text-sm font-semibold text-gray-700" for="livros">
            Livros
            <span class="text-xs font-normal text-gray-400 ml-1">(Ctrl para múltiplos)</span>
          </label>
          <select class="input-field" id="livros" name="livros[]" multiple required>
            <?php foreach ($livros as $l): ?>
              <option value="<?= htmlspecialchars($l['id_livro']) ?>"
                <?= in_array($l['id_livro'], $randomEmp['livros'] ?? []) ? 'selected' : '' ?>>
                <?= htmlspecialchars($l['nm_livro']) ?>
                · Ed. <?= htmlspecialchars($l['numero_edicao']) ?>
                (<?= htmlspecialchars($l['ano_publicacao']) ?>)
                · ISBN <?= htmlspecialchars($l['isbn']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- dt_devolucao_prevista -->
        <div class="space-y-1.5 max-w-xs">
          <label class="block text-sm font-semibold text-gray-700" for="dt_devolucao_prevista">
            Data de Devolução Prevista
          </label>
          <input class="input-field" type="datetime-local"
                 id="dt_devolucao_prevista" name="dt_devolucao_prevista" required
                 value="<?= htmlspecialchars($randomEmp['dt_devolucao_prevista'] ?? '') ?>"/>
        </div>

        <div class="flex items-center gap-3 flex-wrap pt-2">
          <button type="submit" class="btn-primary">+ Registrar Empréstimo</button>
          <a href="?page=emprestimo&random=1" class="btn-ghost" title="Preencher com dados aleatórios">🎲 Aleatório</a>
        </div>

      </form>
    </div>
  </div>

  <!-- ── TABLE CARD ── -->
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
      <div>
        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Consulta</span>
        <h2 class="text-lg font-bold text-gray-900 mt-0.5">Todos os Empréstimos</h2>
      </div>
      <span class="bg-gray-100 text-gray-500 text-xs font-bold px-2.5 py-1 rounded-full">
        <?= count($dados) ?> <?= count($dados) === 1 ? 'registro' : 'registros' ?>
      </span>
    </div>

    <?php if (empty($dados)): ?>
      <div class="flex flex-col items-center justify-center py-16 text-center px-6">
        <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center text-3xl mb-4">📚</div>
        <h3 class="text-gray-700 font-semibold mb-1">Nenhum empréstimo registrado</h3>
        <p class="text-gray-400 text-sm">Os registros aparecerão aqui após o primeiro empréstimo.</p>
      </div>
    <?php else: ?>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-gray-100 bg-gray-50/70">
              <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider whitespace-nowrap">ID Emp.</th>
              <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">Aluno</th>
              <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">Curso</th>
              <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">Livro</th>
              <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider whitespace-nowrap">Retirada</th>
              <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider whitespace-nowrap">Prev. Devolução</th>
              <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider whitespace-nowrap">Devolvido em</th>
              <th class="text-left px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
              <th class="px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">Ação</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <?php foreach ($dados as $emp): ?>
              <?php
                $hoje      = new DateTime();
                $prevista  = new DateTime($emp['dt_devolucao_prevista']);
                $devolvido = !empty($emp['dt_devolucao_real']);
                $atrasado  = !$devolvido && $prevista < $hoje;
              ?>
              <tr class="hover:bg-amber-50/20 group">

                <!-- ID -->
                <td class="px-5 py-4 text-gray-400 font-mono text-xs font-semibold whitespace-nowrap">
                  #<?= htmlspecialchars($emp['id_emprestimo']) ?>
                </td>

                <!-- Aluno -->
                <td class="px-5 py-4">
                  <div class="font-semibold text-gray-900 whitespace-nowrap"><?= htmlspecialchars($emp['nm_aluno']) ?></div>
                  <div class="text-xs text-gray-400 mt-0.5">Reg. <?= htmlspecialchars($emp['registro_aluno']) ?></div>
                </td>

                <!-- Curso -->
                <td class="px-5 py-4 text-gray-500 text-xs whitespace-nowrap">
                  <?= htmlspecialchars($emp['curso']) ?>
                </td>

                <!-- Livro -->
                <td class="px-5 py-4" style="max-width:200px;">
                  <div class="font-semibold text-gray-900 text-xs"><?= htmlspecialchars($emp['nm_livro']) ?></div>
                  <div class="text-xs text-gray-400 mt-0.5 font-mono">ISBN <?= htmlspecialchars($emp['isbn']) ?></div>
                </td>

                <!-- Retirada -->
                <td class="px-5 py-4 text-gray-500 text-xs whitespace-nowrap">
                  <?= (new DateTime($emp['dt_emprestimo']))->format('d/m/Y H:i') ?>
                </td>

                <!-- Prev. Devolução -->
                <td class="px-5 py-4 whitespace-nowrap">
                  <span class="text-xs font-<?= $atrasado ? 'bold text-red-600' : 'medium text-gray-600' ?>">
                    <?= $prevista->format('d/m/Y H:i') ?>
                  </span>
                  <?php if ($atrasado): ?>
                    <div class="text-xs text-red-500 mt-0.5">⚠ Atrasado</div>
                  <?php endif; ?>
                </td>

                <!-- Devolvido em -->
                <td class="px-5 py-4 whitespace-nowrap">
                  <?php if ($devolvido): ?>
                    <span class="text-xs text-emerald-600 font-medium">
                      <?= (new DateTime($emp['dt_devolucao_real']))->format('d/m/Y H:i') ?>
                    </span>
                  <?php else: ?>
                    <span class="text-gray-300 text-xs">—</span>
                  <?php endif; ?>
                </td>

                <!-- Status badge -->
                <td class="px-5 py-4">
                  <?php if ($devolvido): ?>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700 whitespace-nowrap">
                      Devolvido
                    </span>
                  <?php elseif ($atrasado): ?>
                    <span class="badge-pulse inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700 whitespace-nowrap">
                      Atrasado
                    </span>
                  <?php else: ?>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 whitespace-nowrap">
                      <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>
                      Em aberto
                    </span>
                  <?php endif; ?>
                </td>

                <!-- Ação -->
                <td class="px-5 py-4">
                  <?php if (!$devolvido): ?>
                    <a href="?page=emprestimo&devolver=<?= urlencode($emp['id_emprestimo_livro']) ?>"
                       class="btn-devolver"
                       onclick="return confirm('Confirmar devolução?')">
                      Devolver
                    </a>
                  <?php else: ?>
                    <span class="text-gray-300 text-xs">—</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

</div>
