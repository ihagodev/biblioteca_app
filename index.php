<?php
ob_start();

$page = $_GET['page'] ?? 'home';

$allowedPages = ['home','aluno','livro','autor','editora','emprestimo'];
if (!in_array($page, $allowedPages)) $page = 'home';

require_once __DIR__ . "/view/layout/header.php";

switch ($page) {
    case 'aluno':      require_once __DIR__ . "/view/aluno.php";      break;
    case 'livro':      require_once __DIR__ . "/view/livro.php";      break;
    case 'autor':      require_once __DIR__ . "/view/autor.php";      break;
    case 'editora':    require_once __DIR__ . "/view/editora.php";    break;
    case 'emprestimo': require_once __DIR__ . "/view/emprestimo.php"; break;

    default:

    require_once __DIR__ . "/dal/dal.php";
    $totalLivros  = count(listar('livro'));
    $totalAlunos  = count(listar('aluno'));
    $emprestimos  = listarEmprestimosCompleto();
    $totalAtivos  = count(array_filter($emprestimos, fn($e) => empty($e['dt_devolucao_real'])));
    $totalAtrasados = count(array_filter($emprestimos, function($e) {
        return empty($e['dt_devolucao_real']) && new DateTime($e['dt_devolucao_prevista']) < new DateTime();
    }));
?>

<!-- ══════════════════════ HOME ══════════════════════ -->
<div class="stagger space-y-6">

  <!-- Hero -->
  <div class="relative overflow-hidden rounded-2xl shadow-xl" style="background:#1c1917; min-height:240px;">

    <!-- Lombadas decorativas -->
    <div class="absolute right-0 top-0 bottom-0 flex gap-1.5 p-5 opacity-40" style="width:200px;">
      <div class="flex-1 rounded-lg" style="background:linear-gradient(180deg,#ef4444,#b91c1c); margin-top:10px;"></div>
      <div class="flex-1 rounded-lg" style="background:linear-gradient(180deg,#3b82f6,#1d4ed8); margin-top:30px;"></div>
      <div class="flex-1 rounded-lg" style="background:linear-gradient(180deg,#f59e0b,#b45309); margin-top:5px;"></div>
      <div class="flex-1 rounded-lg" style="background:linear-gradient(180deg,#10b981,#065f46); margin-top:20px;"></div>
      <div class="flex-1 rounded-lg" style="background:linear-gradient(180deg,#8b5cf6,#5b21b6); margin-top:0px;"></div>
      <div class="flex-1 rounded-lg" style="background:linear-gradient(180deg,#ec4899,#9d174d); margin-top:15px;"></div>
    </div>

    <div class="relative z-10 p-8 sm:p-10" style="max-width:580px;">
      <span class="inline-block text-xs font-bold uppercase tracking-widest mb-4" style="color:#a8a29e; letter-spacing:.12em;">
        Biblioteca Escolar
      </span>
      <h1 class="font-extrabold leading-tight mb-3" style="font-size:clamp(1.6rem,4vw,2.4rem); color:#fafaf9;">
        Seu acervo,<br/>organizado.
      </h1>
      <p style="color:#a8a29e; font-size:.95rem; line-height:1.65; max-width:380px;">
        Gerencie alunos, livros e empréstimos em um único painel — simples e eficiente.
      </p>

      <!-- Stats -->
      <div class="flex flex-wrap gap-8 mt-8">
        <?php
          $stats = [
            ['val' => $totalLivros,   'label' => 'Livros no acervo',    'color' => '#3b82f6'],
            ['val' => $totalAlunos,   'label' => 'Alunos cadastrados',  'color' => '#10b981'],
            ['val' => $totalAtivos,   'label' => 'Empréstimos ativos',  'color' => '#f59e0b'],
            ['val' => $totalAtrasados,'label' => 'Em atraso',           'color' => '#ef4444'],
          ];
          foreach ($stats as $st):
        ?>
        <div>
          <div class="font-extrabold" style="font-size:1.75rem; color:<?= $st['color'] ?>; line-height:1;">
            <?= $st['val'] ?>
          </div>
          <div style="color:#78716c; font-size:.75rem; margin-top:.2rem;"><?= $st['label'] ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Módulos -->
  <div>
    <p class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-4">Acesso rápido</p>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
    <?php
      $modulos = [
        ['page'=>'emprestimo','icon'=>'📚','title'=>'Empréstimos','desc'=>'Registrar retiradas e devoluções','spine'=>'#f59e0b'],
        ['page'=>'livro',     'icon'=>'📖','title'=>'Livros',     'desc'=>'Acervo, autores e editoras',      'spine'=>'#3b82f6'],
        ['page'=>'aluno',     'icon'=>'🎓','title'=>'Alunos',     'desc'=>'Cadastro de estudantes',          'spine'=>'#10b981'],
        ['page'=>'autor',     'icon'=>'✍️','title'=>'Autores',    'desc'=>'Catálogo de autores',             'spine'=>'#8b5cf6'],
        ['page'=>'editora',   'icon'=>'🏛️','title'=>'Editoras',   'desc'=>'Editoras do acervo',             'spine'=>'#ec4899'],
      ];
      foreach ($modulos as $m):
    ?>
    <a href="?page=<?= $m['page'] ?>"
       class="group card-lift flex items-center gap-4 bg-white rounded-xl border border-gray-100 shadow-sm"
       style="padding:1.1rem 1.25rem; text-decoration:none;">
      <!-- Spine -->
      <div class="shrink-0 rounded-lg flex items-center justify-center text-xl"
           style="width:44px;height:56px;background:<?= $m['spine'] ?>18;border-left:4px solid <?= $m['spine'] ?>;">
        <?= $m['icon'] ?>
      </div>
      <div class="flex-1 min-w-0">
        <div class="font-bold text-gray-900 text-sm"><?= $m['title'] ?></div>
        <div class="text-gray-400 text-xs mt-0.5 truncate"><?= $m['desc'] ?></div>
      </div>
      <div class="text-gray-300 group-hover:text-gray-500 transition-colors text-lg shrink-0">→</div>
    </a>
    <?php endforeach; ?>
    </div>
  </div>

</div>

<?php break; } ?>

<?php require_once __DIR__ . "/view/layout/footer.php"; ?>
