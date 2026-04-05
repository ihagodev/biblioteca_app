  </div><!-- /.max-w-7xl -->
</div><!-- /.pt-[60px] -->

<!-- ══ FOOTER ══ -->
<footer style="background:#1c1917;border-top:1px solid rgba(255,255,255,0.06);" class="mt-6">
  <div class="max-w-7xl mx-auto px-6 py-5 flex flex-col sm:flex-row items-center justify-between gap-3">

    <!-- Marca -->
    <div class="flex items-center gap-2.5">
      <div class="w-6 h-6 rounded-md flex items-center justify-center text-white text-xs font-bold shrink-0"
           style="background:#d97706;">B</div>
      <span style="color:#a8a29e;font-size:.82rem;">
        Sistema de Biblioteca &mdash; Gestão de Acervo
      </span>
    </div>

    <!-- Direitos + GitHub -->
    <div class="flex items-center gap-4">
      <span style="color:#57534e;font-size:.78rem;">
        &copy; <?= date('Y') ?> <strong style="color:#a8a29e;font-weight:600;">Ihago Oliveira</strong>
        &mdash; Todos os direitos reservados
      </span>
      <a href="https://github.com/ihagodev" target="_blank" rel="noopener noreferrer"
         title="GitHub de Ihago Oliveira"
         style="display:inline-flex;align-items:center;gap:6px;padding:5px 12px;border-radius:7px;
                background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.08);
                color:#a8a29e;font-size:.78rem;font-weight:600;text-decoration:none;
                transition:background .15s,color .15s;"
         onmouseover="this.style.background='rgba(217,119,6,0.15)';this.style.color='#d97706';this.style.borderColor='rgba(217,119,6,0.3)'"
         onmouseout="this.style.background='rgba(255,255,255,0.06)';this.style.color='#a8a29e';this.style.borderColor='rgba(255,255,255,0.08)'">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 24 24">
          <path d="M12 0C5.37 0 0 5.37 0 12c0 5.3 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577
            0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61-.546-1.387-1.333-1.756-1.333-1.756
            -1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304
            3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931
            0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322
            3.301 1.23A11.509 11.509 0 0 1 12 5.803c1.02.005 2.047.138 3.006.404
            2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84
            1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823
            2.222 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 21.795 24 17.295
            24 12c0-6.63-5.37-12-12-12z"/>
        </svg>
        ihagodev
      </a>
    </div>

  </div>
</footer>

<script>
  if (new URLSearchParams(window.location.search).get('random') === '1') {
    document.querySelector('form[method="POST"]')?.submit();
  }
</script>
</body>
</html>
<?php ob_end_flush(); ?>
