<?php
require_once __DIR__ . "/../dal/dal.php";

$id    = $_GET['id'] ?? null;
$livro = $id ? buscarLivroDetalhe($id) : null;

if (!$livro) {
    echo '<div class="text-center py-24 text-gray-400">Livro não encontrado.</div>';
    return;
}

// Paleta baseada no ID para manter a mesma cor da capa na home
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
$p = $palettes[$livro['id_livro'] % count($palettes)];

$autoresNomes = array_filter(array_map(fn($a) => $a['nm_autor'] ?? '', $livro['autores']));
$autoresStr   = implode(', ', $autoresNomes) ?: '—';

$sinopse = "Uma obra que atravessa o tempo e desafia as convenções do seu gênero. Ao longo de suas páginas,
o leitor é conduzido por uma narrativa envolvente que explora os limites da condição humana, mesclando
realidade e imaginação com rara habilidade. Cada capítulo revela novas camadas de significado, convidando
à reflexão sobre temas universais como identidade, pertencimento e transformação.

Escrita com precisão e sensibilidade, esta obra se destaca pela profundidade de seus personagens e pela
riqueza dos cenários descritos. A narrativa avança em ritmo equilibrado, alternando momentos de intensa
emoção com passagens contemplativas que convidam o leitor à pausa e à reflexão.

Considerada uma leitura essencial por críticos e educadores, é indicada para quem busca não apenas
entretenimento, mas uma experiência literária que permanece presente muito além da última página.";
?>

<div class="stagger space-y-6">

  <!-- Voltar -->
  <a href="index.php" style="display:inline-flex;align-items:center;gap:6px;font-size:.82rem;font-weight:600;color:#a8a29e;text-decoration:none;transition:color .15s;"
     onmouseover="this.style.color='#1c1917'" onmouseout="this.style.color='#a8a29e'">
    ← Voltar ao início
  </a>

  <!-- Hero do livro -->
  <div class="rounded-2xl overflow-hidden shadow-xl" style="background:#1c1917;">
    <div class="flex flex-col sm:flex-row gap-0">

      <!-- Capa grande -->
      <div class="flex items-center justify-center shrink-0 p-8 sm:p-10" style="background:rgba(0,0,0,0.2); min-width:220px;">
        <div style="
          width:150px; height:220px;
          background:<?= $p['bg'] ?>;
          border-radius:4px 12px 12px 4px;
          position:relative; overflow:hidden;
          box-shadow:6px 6px 0 rgba(0,0,0,0.25), 12px 12px 28px rgba(0,0,0,0.35);
          display:flex; flex-direction:column; justify-content:space-between;
          padding:16px 14px 14px;">
          <!-- Lombada -->
          <div style="position:absolute;left:0;top:0;bottom:0;width:7px;background:rgba(0,0,0,0.3);border-radius:4px 0 0 4px;"></div>
          <!-- Decorativo -->
          <div style="position:absolute;right:-20px;top:-20px;width:100px;height:100px;border-radius:50%;background:<?= $p['accent'] ?>;"></div>
          <div style="position:absolute;right:12px;bottom:48px;width:45px;height:45px;border-radius:50%;background:<?= $p['accent'] ?>;"></div>
          <!-- Editora -->
          <div style="position:relative;z-index:1;font-size:.6rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:<?= $p['text'] ?>;opacity:.6;">
            <?= htmlspecialchars($livro['nm_editora'] ?? '') ?>
          </div>
          <!-- Título -->
          <div style="position:relative;z-index:1;">
            <div style="font-size:.88rem;font-weight:800;line-height:1.25;color:<?= $p['text'] ?>;word-break:break-word;">
              <?= htmlspecialchars(mb_strimwidth($livro['nm_livro'], 0, 50, '…')) ?>
            </div>
            <div style="margin-top:8px;font-size:.68rem;color:<?= $p['text'] ?>;opacity:.7;">
              <?= htmlspecialchars(mb_strimwidth($autoresStr, 0, 32, '…')) ?>
            </div>
          </div>
        </div>
      </div>

      <!-- Info -->
      <div class="flex-1 p-8 sm:p-10" style="border-left:1px solid rgba(255,255,255,0.05);">
        <div style="font-size:.7rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#a8a29e;margin-bottom:10px;">
          <?= htmlspecialchars($livro['nm_editora'] ?? '—') ?> · <?= htmlspecialchars($livro['ano_publicacao'] ?? '') ?>
        </div>
        <h1 style="font-size:clamp(1.4rem,3vw,2rem);font-weight:800;color:#fafaf9;line-height:1.2;margin-bottom:10px;">
          <?= htmlspecialchars($livro['nm_livro']) ?>
        </h1>
        <p style="font-size:1rem;color:#a8a29e;margin-bottom:28px;">
          <?= htmlspecialchars($autoresStr) ?>
        </p>

        <!-- Badges -->
        <div style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:32px;">
          <?php
            $badges = [
              ['label'=>'Edição',  'val'=> ($livro['numero_edicao'] ?? '—') . 'ª ed.'],
              ['label'=>'Ano',     'val'=> $livro['ano_publicacao'] ?? '—'],
              ['label'=>'ISBN',    'val'=> $livro['isbn'] ?? '—'],
              ['label'=>'Cidade',  'val'=> $livro['cidade'] ?? '—'],
            ];
            foreach ($badges as $b):
          ?>
          <div style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.08);border-radius:8px;padding:8px 14px;">
            <div style="font-size:.6rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#78716c;"><?= $b['label'] ?></div>
            <div style="font-size:.85rem;font-weight:600;color:#e7e5e4;margin-top:2px;"><?= htmlspecialchars($b['val']) ?></div>
          </div>
          <?php endforeach; ?>
        </div>

        <a href="?page=emprestimo" class="btn-primary" style="display:inline-flex;align-items:center;gap:8px;">
          📚 Registrar Empréstimo
        </a>
      </div>
    </div>
  </div>

  <!-- Sinopse -->
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
    <h2 style="font-size:1rem;font-weight:700;color:#1c1917;margin-bottom:16px;padding-bottom:12px;border-bottom:2px solid #f7f3ef;">
      Sinopse
    </h2>
    <?php foreach (explode("\n\n", trim($sinopse)) as $para): ?>
      <p style="font-size:.93rem;color:#57534e;line-height:1.8;margin-bottom:14px;">
        <?= htmlspecialchars(trim($para)) ?>
      </p>
    <?php endforeach; ?>
  </div>

  <!-- Ficha técnica -->
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
    <h2 style="font-size:1rem;font-weight:700;color:#1c1917;margin-bottom:16px;padding-bottom:12px;border-bottom:2px solid #f7f3ef;">
      Ficha técnica
    </h2>
    <dl style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px;">
      <?php
        $ficha = [
          'Título'    => $livro['nm_livro'],
          'Autor(es)' => $autoresStr,
          'Editora'   => $livro['nm_editora'] ?? '—',
          'Cidade'    => $livro['cidade'] ?? '—',
          'Edição'    => ($livro['numero_edicao'] ?? '—') . 'ª edição',
          'Ano'       => $livro['ano_publicacao'] ?? '—',
          'ISBN'      => $livro['isbn'] ?? '—',
        ];
        foreach ($ficha as $k => $v):
      ?>
      <div>
        <dt style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#a8a29e;"><?= $k ?></dt>
        <dd style="font-size:.88rem;font-weight:500;color:#292524;margin-top:3px;"><?= htmlspecialchars($v) ?></dd>
      </div>
      <?php endforeach; ?>
    </dl>
  </div>

</div>
