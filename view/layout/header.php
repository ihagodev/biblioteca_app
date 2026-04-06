<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Biblioteca</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="stylesheet" href="app/assets/style.css"/>
  <script src="app/assets/app.js" defer></script>
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
    body { background: #f7f3ef; margin: 0; }

    /* ── Navbar ── */
    nav.site-nav {
      position: fixed; top: 0; left: 0; right: 0; z-index: 50;
      background: #1c1917;
      border-bottom: 1px solid rgba(255,255,255,0.05);
      box-shadow: 0 4px 24px rgba(0,0,0,0.4);
    }
    .nav-inner {
      max-width: 1280px; margin: 0 auto;
      padding: 0 1rem; height: 56px;
      display: flex; align-items: center; justify-content: space-between;
    }
    .nav-brand {
      display: flex; align-items: center; gap: 9px;
      text-decoration: none; flex-shrink: 0;
    }
    .nav-brand-icon {
      width: 30px; height: 30px; border-radius: 7px;
      background: #d97706;
      display: flex; align-items: center; justify-content: center;
      color: #fff; font-weight: 700; font-size: 13px;
    }
    .nav-brand-text { color: #fafaf9; font-weight: 600; font-size: 15px; letter-spacing: -0.3px; }
    .nav-brand-text span { color: #d97706; }

    /* Desktop links */
    .nav-links {
      display: flex; align-items: center; gap: 2px;
    }
    .nav-link {
      padding: 6px 12px; border-radius: 8px;
      font-size: 13px; font-weight: 500;
      color: #a8a29e; text-decoration: none;
      transition: background 0.15s, color 0.15s;
      white-space: nowrap;
    }
    .nav-link:hover { background: rgba(255,255,255,0.06); color: #fafaf9; }
    .nav-link.active { background: #d97706; color: #fff; box-shadow: 0 4px 12px rgba(217,119,6,0.3); }

    /* Hamburger button */
    .nav-burger {
      display: none;
      flex-direction: column; justify-content: center; gap: 5px;
      width: 36px; height: 36px; padding: 6px;
      background: transparent; border: none; cursor: pointer;
      border-radius: 8px; flex-shrink: 0;
      transition: background .15s;
    }
    .nav-burger:hover { background: rgba(255,255,255,0.08); }
    .nav-burger span {
      display: block; height: 2px; border-radius: 2px;
      background: #a8a29e; transition: all .25s ease;
    }
    .nav-burger.open span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
    .nav-burger.open span:nth-child(2) { opacity: 0; transform: scaleX(0); }
    .nav-burger.open span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }

    /* Mobile drawer */
    .nav-drawer {
      display: none;
      position: fixed; top: 56px; left: 0; right: 0; z-index: 49;
      background: #1c1917;
      border-bottom: 1px solid rgba(255,255,255,0.07);
      padding: 8px 16px 12px;
      flex-direction: column; gap: 2px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.4);
    }
    .nav-drawer.open { display: flex; }
    .nav-drawer .nav-link {
      padding: 10px 14px; font-size: 14px; border-radius: 10px;
    }

    /* Page layout */
    .page-wrap { padding-top: 56px; min-height: 100vh; }
    .page-inner { max-width: 1280px; margin: 0 auto; padding: 1.5rem 1rem; }

    @media (min-width: 640px) {
      .nav-inner { padding: 0 1.5rem; height: 60px; }
      .page-wrap { padding-top: 60px; }
      .page-inner { padding: 2rem 1.5rem; }
    }
    @media (min-width: 900px) {
      .page-inner { padding: 2.5rem 1.5rem; }
    }

    /* Switch between burger and desktop links */
    @media (max-width: 699px) {
      .nav-links { display: none; }
      .nav-burger { display: flex; }
    }
    @media (min-width: 700px) {
      .nav-drawer { display: none !important; }
    }
  </style>
</head>
<body class="text-gray-900 antialiased" style="background:#f7f3ef;">

<?php $page = $_GET['page'] ?? 'home'; ?>

<!-- ══ NAVBAR ══ -->
<nav class="site-nav">
  <div class="nav-inner">

    <!-- Logo -->
    <a href="index.php" class="nav-brand">
      <div class="nav-brand-icon">B</div>
      <span class="nav-brand-text">Biblio<span>teca</span></span>
    </a>

    <!-- Desktop links -->
    <div class="nav-links">
      <?php
        $navItems = [
          'aluno'      => 'Alunos',
          'livro'      => 'Livros',
          'autor'      => 'Autores',
          'editora'    => 'Editoras',
          'emprestimo' => 'Empréstimos',
        ];
        foreach ($navItems as $key => $label):
          $cls = 'nav-link' . ($page === $key ? ' active' : '');
      ?>
        <a href="?page=<?= $key ?>" class="<?= $cls ?>"><?= $label ?></a>
      <?php endforeach; ?>
    </div>

    <!-- Hamburger -->
    <button class="nav-burger" id="nav-burger" aria-label="Menu" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>

  </div>
</nav>

<!-- Mobile drawer -->
<div class="nav-drawer" id="nav-drawer" aria-hidden="true">
  <?php foreach ($navItems as $key => $label):
    $cls = 'nav-link' . ($page === $key ? ' active' : '');
  ?>
    <a href="?page=<?= $key ?>" class="<?= $cls ?>"><?= $label ?></a>
  <?php endforeach; ?>
</div>

<script>
(function () {
  var burger = document.getElementById('nav-burger');
  var drawer = document.getElementById('nav-drawer');
  burger.addEventListener('click', function () {
    var open = drawer.classList.toggle('open');
    burger.classList.toggle('open', open);
    burger.setAttribute('aria-expanded', open);
    drawer.setAttribute('aria-hidden', !open);
  });
  // Close on outside tap
  document.addEventListener('click', function (e) {
    if (!burger.contains(e.target) && !drawer.contains(e.target)) {
      drawer.classList.remove('open');
      burger.classList.remove('open');
      burger.setAttribute('aria-expanded', 'false');
      drawer.setAttribute('aria-hidden', 'true');
    }
  });
})();
</script>

<!-- Main wrapper -->
<div class="page-wrap" style="background:#f7f3ef;">
  <div class="page-inner animate-fade-in">
