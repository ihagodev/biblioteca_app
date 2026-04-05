<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Biblioteca</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="stylesheet" href="app/assets/style.css"/>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'] }
        }
      }
    }
  </script>
  <style>
    /* Fallback inline — garante visual mesmo sem CDN */
    body { background: #f7f3ef; margin: 0; }
    nav.site-nav {
      position: fixed; top: 0; left: 0; right: 0; z-index: 50;
      background: #1c1917;
      border-bottom: 1px solid rgba(255,255,255,0.05);
      box-shadow: 0 4px 24px rgba(0,0,0,0.4);
    }
    .nav-inner {
      max-width: 1280px; margin: 0 auto;
      padding: 0 1.5rem; height: 60px;
      display: flex; align-items: center; justify-content: space-between;
    }
    .nav-brand {
      display: flex; align-items: center; gap: 10px;
      text-decoration: none;
    }
    .nav-brand-icon {
      width: 32px; height: 32px; border-radius: 8px;
      background: #d97706;
      display: flex; align-items: center; justify-content: center;
      color: #fff; font-weight: 700; font-size: 13px;
    }
    .nav-brand-text { color: #fafaf9; font-weight: 600; font-size: 15px; letter-spacing: -0.3px; }
    .nav-brand-text span { color: #d97706; }
    .nav-links { display: flex; align-items: center; gap: 2px; }
    .nav-link {
      padding: 7px 14px; border-radius: 8px;
      font-size: 13.5px; font-weight: 500;
      color: #a8a29e; text-decoration: none;
      transition: background 0.15s, color 0.15s;
    }
    .nav-link:hover { background: rgba(255,255,255,0.06); color: #fafaf9; }
    .nav-link.active { background: #d97706; color: #fff; box-shadow: 0 4px 12px rgba(217,119,6,0.35); }
    .page-wrap { padding-top: 60px; min-height: 100vh; }
    .page-inner { max-width: 1280px; margin: 0 auto; padding: 2.5rem 1.5rem; }
  </style>
</head>
<body class="text-gray-900 antialiased" style="background:#f7f3ef;">

<?php $page = $_GET['page'] ?? 'home'; ?>

<!-- ══ NAVBAR ══ -->
<nav class="site-nav fixed top-0 left-0 right-0 z-50 bg-slate-900 backdrop-blur-md">
  <div class="nav-inner max-w-7xl mx-auto px-4 sm:px-6 flex items-center justify-between" style="height:60px;">

    <!-- Logo -->
    <a href="index.php" class="nav-brand flex items-center gap-2.5 group shrink-0">
      <div class="nav-brand-icon w-8 h-8 rounded-lg flex items-center justify-center text-white font-bold text-sm"
           style="background:#d97706;">B</div>
      <span class="nav-brand-text font-semibold text-base tracking-tight">
        Biblio<span style="color:#d97706;">teca</span>
      </span>
    </a>

    <!-- Nav links -->
    <div class="nav-links flex items-center gap-0.5">
      <?php
        $navItems = [
          'aluno'      => 'Alunos',
          'livro'      => 'Livros',
          'autor'      => 'Autores',
          'editora'    => 'Editoras',
          'emprestimo' => 'Empréstimos',
        ];
        foreach ($navItems as $key => $label):
          $isActive = $page === $key;
          $baseClass = "nav-link px-3.5 py-2 rounded-lg text-sm font-medium transition-all duration-150";
          $activeClass = $isActive
            ? ' active bg-indigo-600 text-white'
            : ' text-slate-400';
      ?>
        <a href="?page=<?= $key ?>" class="<?= $baseClass . $activeClass ?>">
          <?= $label ?>
        </a>
      <?php endforeach; ?>
    </div>

  </div>
</nav>

<!-- Main wrapper -->
<div class="page-wrap pt-[60px] min-h-screen" style="background:#f7f3ef;">
  <div class="page-inner max-w-7xl mx-auto px-4 sm:px-6 py-10 animate-fade-in">
