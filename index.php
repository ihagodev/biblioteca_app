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

    default: ?>

<!-- ══════════════════════ HOME ══════════════════════ -->
<div class="stagger space-y-8">

  <!-- Hero -->
  <div class="relative overflow-hidden rounded-2xl hero-mesh p-8 sm:p-10 text-white shadow-2xl">
    <!-- Decorative rings -->
    <div class="ring-decoration" style="width:260px;height:260px;right:-60px;top:-80px;"></div>
    <div class="ring-decoration" style="width:160px;height:160px;right:40px;top:20px;"></div>
    <div class="ring-decoration" style="width:80px;height:80px;right:100px;top:80px;"></div>

    <div class="relative z-10 max-w-xl">
      <span class="inline-flex items-center gap-1.5 mb-3">
        <span class="w-1.5 h-1.5 rounded-full bg-indigo-300 inline-block"></span>
        <span class="text-indigo-200 text-xs font-semibold uppercase tracking-widest">Painel Principal</span>
      </span>
      <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight mb-3 leading-tight">
        Sistema de<br/>Biblioteca
      </h1>
      <p class="text-indigo-200/90 text-base leading-relaxed max-w-sm">
        Gerencie alunos, acervo e empréstimos em um único lugar com facilidade e eficiência.
      </p>
    </div>
  </div>

  <!-- Section label -->
  <div>
    <span class="text-xs font-semibold text-gray-400 uppercase tracking-widest">Acesso rápido</span>
    <h2 class="text-lg font-bold text-gray-800 mt-0.5">Módulos do sistema</h2>
  </div>

  <!-- Shortcut grid -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

    <?php
      $shortcuts = [
        ['page'=>'emprestimo','icon'=>'📚','label'=>'Principal',   'title'=>'Empréstimos', 'desc'=>'Registrar e devolver empréstimos de livros',      'color'=>'from-amber-500 to-orange-500',  'bg'=>'bg-amber-50', 'text'=>'text-amber-600'],
        ['page'=>'aluno',     'icon'=>'🎓','label'=>'Cadastro',    'title'=>'Alunos',      'desc'=>'Cadastrar e gerenciar dados dos alunos',           'color'=>'from-emerald-500 to-teal-500',  'bg'=>'bg-emerald-50','text'=>'text-emerald-600'],
        ['page'=>'livro',     'icon'=>'📖','label'=>'Acervo',      'title'=>'Livros',      'desc'=>'Cadastrar livros, vincular autores e editoras',    'color'=>'from-blue-500 to-indigo-500',   'bg'=>'bg-blue-50',   'text'=>'text-blue-600'],
        ['page'=>'autor',     'icon'=>'✍️','label'=>'Referência',  'title'=>'Autores',     'desc'=>'Manter catálogo de autores do acervo',             'color'=>'from-violet-500 to-purple-600', 'bg'=>'bg-violet-50', 'text'=>'text-violet-600'],
        ['page'=>'editora',   'icon'=>'🏛️','label'=>'Referência',  'title'=>'Editoras',    'desc'=>'Gerenciar editoras vinculadas ao acervo',          'color'=>'from-rose-500 to-pink-500',     'bg'=>'bg-rose-50',   'text'=>'text-rose-600'],
      ];
      foreach ($shortcuts as $s):
    ?>
    <a href="?page=<?= $s['page'] ?>"
       class="group bg-white rounded-2xl p-6 shadow-sm border border-gray-100 card-lift flex flex-col">
      <!-- Icon -->
      <div class="w-12 h-12 <?= $s['bg'] ?> rounded-xl flex items-center justify-center text-2xl mb-4
                  transition-transform duration-200 group-hover:scale-110">
        <?= $s['icon'] ?>
      </div>
      <!-- Labels -->
      <span class="text-xs font-semibold <?= $s['text'] ?> uppercase tracking-wider mb-1"><?= $s['label'] ?></span>
      <h3 class="text-gray-900 font-bold text-lg leading-tight"><?= $s['title'] ?></h3>
      <p class="text-gray-500 text-sm mt-1.5 leading-relaxed flex-1"><?= $s['desc'] ?></p>
      <!-- Arrow -->
      <div class="mt-4 flex items-center gap-1 text-sm font-semibold <?= $s['text'] ?>
                  opacity-0 group-hover:opacity-100 transition-all duration-200 -translate-x-1 group-hover:translate-x-0">
        Acessar <span class="text-base">→</span>
      </div>
    </a>
    <?php endforeach; ?>

  </div>
</div>

<?php break; } ?>

<?php require_once __DIR__ . "/view/layout/footer.php"; ?>
