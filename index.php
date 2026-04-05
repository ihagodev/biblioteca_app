<?php
ob_start();

$page = $_GET['page'] ?? 'home';

$allowedPages = ['home','aluno','livro','autor','editora','emprestimo','livro_detalhe'];
if (!in_array($page, $allowedPages)) $page = 'home';

require_once __DIR__ . "/view/layout/header.php";

switch ($page) {
    case 'aluno':      require_once __DIR__ . "/view/aluno.php";      break;
    case 'livro':      require_once __DIR__ . "/view/livro.php";      break;
    case 'autor':      require_once __DIR__ . "/view/autor.php";      break;
    case 'editora':    require_once __DIR__ . "/view/editora.php";    break;
    case 'emprestimo':    require_once __DIR__ . "/view/emprestimo.php";     break;
    case 'livro_detalhe': require_once __DIR__ . "/view/livro_detalhe.php"; break;

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

  <!-- Livros em destaque -->
  <?php
    $livrosDestaque = array_slice(listarLivroCompleto(), 0, 10);
    $palettes = [
      ['bg'=>'linear-gradient(145deg,#1e3a5f,#2d6a9f)','text'=>'#e0f0ff','accent'=>'rgba(255,255,255,0.07)'],
      ['bg'=>'linear-gradient(145deg,#4a1942,#9b2c7e)','text'=>'#fce4ff','accent'=>'rgba(255,255,255,0.07)'],
      ['bg'=>'linear-gradient(145deg,#7c2d12,#c2410c)','text'=>'#ffeedd','accent'=>'rgba(255,255,255,0.07)'],
      ['bg'=>'linear-gradient(145deg,#14532d,#16a34a)','text'=>'#dcfce7','accent'=>'rgba(255,255,255,0.07)'],
      ['bg'=>'linear-gradient(145deg,#1e1b4b,#4338ca)','text'=>'#e0e7ff','accent'=>'rgba(255,255,255,0.07)'],
      ['bg'=>'linear-gradient(145deg,#422006,#92400e)','text'=>'#fef3c7','accent'=>'rgba(255,255,255,0.07)'],
      ['bg'=>'linear-gradient(145deg,#0c4a6e,#0369a1)','text'=>'#e0f2fe','accent'=>'rgba(255,255,255,0.07)'],
      ['bg'=>'linear-gradient(145deg,#3b0764,#7e22ce)','text'=>'#f3e8ff','accent'=>'rgba(255,255,255,0.07)'],
      ['bg'=>'linear-gradient(145deg,#881337,#e11d48)','text'=>'#ffe4e6','accent'=>'rgba(255,255,255,0.07)'],
      ['bg'=>'linear-gradient(145deg,#134e4a,#0f766e)','text'=>'#ccfbf1','accent'=>'rgba(255,255,255,0.07)'],
    ];
  ?>
  <div>
    <div class="flex items-center justify-between mb-4">
      <p class="text-xs font-bold uppercase tracking-widest text-gray-400">Acervo em destaque</p>
      <a href="?page=livro" style="font-size:.78rem;color:#d97706;font-weight:600;text-decoration:none;">Ver todos →</a>
    </div>

    <?php if (empty($livrosDestaque)): ?>
      <div class="text-center py-12 text-gray-400 text-sm">Nenhum livro cadastrado ainda.</div>
    <?php else: ?>
    <!-- Prateleira com scroll horizontal -->
    <div style="display:flex; gap:16px; overflow-x:auto; padding-bottom:12px; scrollbar-width:thin; scrollbar-color:#d6cfc7 transparent;">
      <?php foreach ($livrosDestaque as $i => $livro):
        $p = $palettes[$i % count($palettes)];
        $titulo  = htmlspecialchars($livro['nm_livro']);
        $autor   = htmlspecialchars($livro['autores'] ?? '—');
        $editora = htmlspecialchars($livro['nm_editora'] ?? '');
      ?>
      <a href="?page=livro_detalhe&id=<?= $livro['id_livro'] ?>" title="<?= $titulo ?>" style="text-decoration:none; flex-shrink:0;">
        <!-- Capa -->
        <div style="
          width:130px; height:190px; border-radius:4px 10px 10px 4px;
          background:<?= $p['bg'] ?>;
          position:relative; overflow:hidden;
          box-shadow: 4px 4px 0 rgba(0,0,0,0.18), 8px 8px 20px rgba(0,0,0,0.22);
          transition: transform .2s ease, box-shadow .2s ease;
          display:flex; flex-direction:column; justify-content:space-between;
          padding:14px 12px 12px;
          cursor:pointer;"
          onmouseover="this.style.transform='translateY(-6px) rotate(1deg)';this.style.boxShadow='6px 10px 28px rgba(0,0,0,0.3)'"
          onmouseout="this.style.transform='';this.style.boxShadow='4px 4px 0 rgba(0,0,0,0.18), 8px 8px 20px rgba(0,0,0,0.22)'">

          <!-- Lombada esquerda -->
          <div style="position:absolute;left:0;top:0;bottom:0;width:6px;background:rgba(0,0,0,0.25);border-radius:4px 0 0 4px;"></div>

          <!-- Forma decorativa -->
          <div style="
            position:absolute; right:-18px; top:-18px;
            width:90px; height:90px; border-radius:50%;
            background:<?= $p['accent'] ?>;"></div>
          <div style="
            position:absolute; right:10px; bottom:40px;
            width:40px; height:40px; border-radius:50%;
            background:<?= $p['accent'] ?>;"></div>

          <!-- Título -->
          <div style="position:relative; z-index:1;">
            <div style="font-size:.65rem; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:<?= $p['text'] ?>; opacity:.6; margin-bottom:5px;">
              <?= $editora ?>
            </div>
            <div style="font-size:.82rem; font-weight:800; line-height:1.25; color:<?= $p['text'] ?>; word-break:break-word;">
              <?= mb_strimwidth($livro['nm_livro'], 0, 42, '…') ?>
            </div>
          </div>

          <!-- Autor -->
          <div style="position:relative; z-index:1; font-size:.68rem; color:<?= $p['text'] ?>; opacity:.7; line-height:1.3;">
            <?= mb_strimwidth($autor, 0, 30, '…') ?>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

</div>

<?php break; } ?>

<?php require_once __DIR__ . "/view/layout/footer.php"; ?>
