(function () {
  'use strict';

  /* ================================================================
     SEARCHABLE SINGLE SELECT  (.ss-wrap)

     HTML structure expected:
       <div class="ss-wrap">
         <input type="hidden" name="field" class="ss-value" value="" data-required>
         <div style="position:relative">
           <input type="text" class="input-field ss-search" placeholder="Buscar...">
           <span class="ss-chevron">▾</span>
         </div>
         <ul class="ss-dropdown">
           <li class="ss-empty">Nenhum resultado.</li>
           <li class="ss-option" data-value="1">Opção</li>
           ...
         </ul>
       </div>
  ================================================================ */

  function initSS(wrap) {
    var search = wrap.querySelector('.ss-search');
    var hidden = wrap.querySelector('.ss-value');
    var drop   = wrap.querySelector('.ss-dropdown');
    var empty  = wrap.querySelector('.ss-empty');

    function opts() {
      return Array.from(wrap.querySelectorAll('.ss-option'));
    }

    function open() { drop.style.display = 'block'; }
    function close() { drop.style.display = 'none'; }

    function filter(q) {
      q = q.toLowerCase();
      var n = 0;
      opts().forEach(function (o) {
        var show = !q || o.textContent.toLowerCase().includes(q);
        o.style.display = show ? '' : 'none';
        if (show) n++;
      });
      if (empty) empty.style.display = n ? 'none' : '';
    }

    search.addEventListener('focus', open);

    search.addEventListener('input', function () {
      hidden.value = '';
      filter(this.value);
      open();
    });

    search.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') { close(); return; }
      if (e.key === 'ArrowDown') {
        e.preventDefault();
        var first = opts().find(function (o) { return o.style.display !== 'none'; });
        if (first) first.focus();
      }
    });

    opts().forEach(function (o) {
      o.setAttribute('tabindex', '-1');

      o.addEventListener('mousedown', function (e) {
        e.preventDefault();
        hidden.value  = o.dataset.value;
        search.value  = o.textContent.trim();
        search.style.borderColor = '';
        search.style.boxShadow   = '';
        close();
      });

      o.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
          hidden.value = o.dataset.value;
          search.value = o.textContent.trim();
          close();
          search.focus();
        }
        if (e.key === 'ArrowDown') {
          var next = o.nextElementSibling;
          while (next && (!next.classList.contains('ss-option') || next.style.display === 'none')) {
            next = next.nextElementSibling;
          }
          if (next) next.focus();
        }
        if (e.key === 'ArrowUp') {
          var prev = o.previousElementSibling;
          while (prev && (!prev.classList.contains('ss-option') || prev.style.display === 'none')) {
            prev = prev.previousElementSibling;
          }
          if (prev) prev.focus(); else search.focus();
        }
        if (e.key === 'Escape') { close(); search.focus(); }
      });
    });

    document.addEventListener('mousedown', function (e) {
      if (!wrap.contains(e.target)) close();
    });
  }

  document.querySelectorAll('.ss-wrap').forEach(initSS);

  /* ── Validate required ss-value fields on submit ── */
  document.querySelectorAll('form').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      var ok = true;
      form.querySelectorAll('.ss-value[data-required]').forEach(function (hidden) {
        if (!hidden.value) {
          var s = hidden.closest('.ss-wrap').querySelector('.ss-search');
          s.style.borderColor = '#ef4444';
          s.style.boxShadow   = '0 0 0 3px rgba(239,68,68,.12)';
          if (ok) s.focus();
          ok = false;
        }
      });
      if (!ok) e.preventDefault();
    });
  });

  /* ================================================================
     TABLE FILTER
     Usage: oninput="filtrarTabela(this,'tbody-id')"
  ================================================================ */
  window.filtrarTabela = function (input, tbodyId) {
    var q    = input.value.toLowerCase().trim();
    var rows = document.querySelectorAll('#' + tbodyId + ' tr.filterable');
    var n    = 0;
    rows.forEach(function (tr) {
      var show = !q || tr.textContent.toLowerCase().includes(q);
      tr.style.display = show ? '' : 'none';
      if (show) n++;
    });
    var noResult = document.getElementById(tbodyId + '-empty');
    if (noResult) noResult.style.display = (q && n === 0) ? '' : 'none';
  };

})();
