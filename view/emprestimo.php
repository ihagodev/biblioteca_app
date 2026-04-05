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
        $mensagem = "Erro: " . $e->getMessage();
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
$livros = listarLivroCompleto();
$dados  = listarEmprestimosCompleto();

// Label inicial para o select pesquisável de aluno
$alunoValor = '';
$alunoLabel = '';

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

$alunoValor = $randomEmp['registro_aluno'] ?? '';
if ($alunoValor) {
    foreach ($alunos as $_a) {
        if ($_a['registro_aluno'] == $alunoValor) {
            $alunoLabel = $_a['nm_aluno'] . ' — ' . $_a['curso'] . ' (Reg. ' . $_a['registro_aluno'] . ')';
            break;
        }
    }
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

      <form method="POST" action="?page=emprestimo" class="space-y-5"
        onsubmit="var ok=document.querySelectorAll('#livro-lista input:checked').length>0;if(!ok){alert('Selecione ao menos um livro.');return false;}">

        <!-- registro_aluno — select pesquisável -->
        <div class="space-y-1.5">
          <label class="block text-sm font-semibold text-gray-700">Aluno</label>
          <div class="ss-wrap">
            <input type="hidden" name="registro_aluno" class="ss-value"
                   value="<?= htmlspecialchars($alunoValor) ?>" data-required>
            <div style="position:relative;">
              <input type="text" class="input-field ss-search"
                     placeholder="Buscar aluno por nome ou curso…" autocomplete="off"
                     value="<?= htmlspecialchars($alunoLabel) ?>">
              <span class="ss-chevron">▾</span>
            </div>
            <ul class="ss-dropdown">
              <li class="ss-empty">Nenhum aluno encontrado.</li>
              <?php foreach ($alunos as $a): ?>
              <li class="ss-option" data-value="<?= htmlspecialchars($a['registro_aluno']) ?>">
                <?= htmlspecialchars($a['nm_aluno']) ?> — <?= htmlspecialchars($a['curso']) ?> (Reg. <?= htmlspecialchars($a['registro_aluno']) ?>)
              </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>

        <!-- livros[] -->
        <div class="space-y-1.5">
          <label class="block text-sm font-semibold text-gray-700">
            Livros
            <span class="text-xs font-normal text-gray-400 ml-1">(selecione um ou mais)</span>
          </label>

          <!-- Busca -->
          <div class="relative">
            <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 pointer-events-none">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
              </svg>
            </span>
            <input type="text" id="livro-busca" placeholder="Buscar por título ou autor…"
              class="input-field pl-9"
              oninput="filtrarLivros(this.value)">
          </div>

          <!-- Lista de livros -->
          <div id="livro-lista"
            style="max-height:260px;overflow-y:auto;border:1px solid #e5e7eb;border-radius:10px;background:#fff;">
            <?php foreach ($livros as $l):
              $autores = $l['autores'] ?? '';
              $checked = in_array($l['id_livro'], $randomEmp['livros'] ?? []) ? 'checked' : '';
            ?>
            <label class="livro-item flex items-start gap-3 px-4 py-3 cursor-pointer hover:bg-amber-50/50 border-b border-gray-50 last:border-0"
              data-titulo="<?= strtolower(htmlspecialchars($l['nm_livro'])) ?>"
              data-autores="<?= strtolower(htmlspecialchars($autores)) ?>">
              <input type="checkbox" name="livros[]"
                value="<?= htmlspecialchars($l['id_livro']) ?>"
                <?= $checked ?>
                class="mt-0.5 shrink-0 accent-amber-500 w-4 h-4">
              <div class="min-w-0">
                <div class="text-sm font-semibold text-gray-900 leading-snug">
                  <?= htmlspecialchars($l['nm_livro']) ?>
                </div>
                <?php if ($autores): ?>
                <div class="text-xs text-gray-400 mt-0.5"><?= htmlspecialchars($autores) ?></div>
                <?php endif; ?>
                <div class="text-xs text-gray-300 mt-0.5 font-mono">
                  Ed. <?= htmlspecialchars($l['numero_edicao']) ?>
                  · <?= htmlspecialchars($l['ano_publicacao']) ?>
                  · ISBN <?= htmlspecialchars($l['isbn']) ?>
                </div>
              </div>
            </label>
            <?php endforeach; ?>
            <div id="livro-nenhum" class="hidden px-4 py-6 text-center text-sm text-gray-400">
              Nenhum livro encontrado.
            </div>
          </div>

          <!-- Contador de selecionados -->
          <p id="livro-contador" class="text-xs text-gray-400"></p>
        </div>

        <script>
        (function () {
          function atualizarContador() {
            var checked = document.querySelectorAll('#livro-lista input[type=checkbox]:checked').length;
            var el = document.getElementById('livro-contador');
            el.textContent = checked > 0 ? checked + ' livro(s) selecionado(s)' : '';
          }

          document.getElementById('livro-lista').addEventListener('change', atualizarContador);
          atualizarContador();

          window.filtrarLivros = function (q) {
            q = q.toLowerCase().trim();
            var items = document.querySelectorAll('#livro-lista .livro-item');
            var visiveis = 0;
            items.forEach(function (item) {
              var titulo  = item.dataset.titulo  || '';
              var autores = item.dataset.autores || '';
              var match   = !q || titulo.includes(q) || autores.includes(q);
              item.style.display = match ? '' : 'none';
              if (match) visiveis++;
            });
            document.getElementById('livro-nenhum').classList.toggle('hidden', visiveis > 0);
          };
        })();
        </script>

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
    <div class="px-6 py-4 border-b border-gray-100">
      <div class="flex items-center justify-between mb-3">
        <div>
          <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Consulta</span>
          <h2 class="text-lg font-bold text-gray-900 mt-0.5">Todos os Empréstimos</h2>
        </div>
        <span class="bg-gray-100 text-gray-500 text-xs font-bold px-2.5 py-1 rounded-full shrink-0">
          <?= count($dados) ?> <?= count($dados) === 1 ? 'registro' : 'registros' ?>
        </span>
      </div>
      <div class="relative">
        <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 pointer-events-none">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
          </svg>
        </span>
        <input type="text" class="input-field pl-9 text-sm" placeholder="Buscar por aluno, livro, curso ou status…"
          oninput="filtrarTabela(this,'emp-tbody')">
      </div>
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
          <tbody id="emp-tbody" class="divide-y divide-gray-50">
            <tr id="emp-tbody-empty" style="display:none;">
              <td colspan="9" class="px-5 py-8 text-center text-sm text-gray-400">Nenhum resultado encontrado.</td>
            </tr>
            <?php foreach ($dados as $emp): ?>
              <?php
                $hoje      = new DateTime();
                $prevista  = new DateTime($emp['dt_devolucao_prevista']);
                $devolvido = !empty($emp['dt_devolucao_real']);
                $atrasado  = !$devolvido && $prevista < $hoje;
              ?>
              <tr class="hover:bg-amber-50/20 group filterable">

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
