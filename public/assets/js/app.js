/* Cybersecurity-Academy front-end behaviour.
 * Progressive enhancement only: every page works without JavaScript.
 * No inline scripts are used, so the Content Security Policy can forbid them. */
(function () {
  'use strict';

  var doc = document.documentElement;
  doc.classList.add('js');
  var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

  /* ---------------------------------------------------------- Theme */
  function storedTheme() {
    try { return localStorage.getItem('ca-theme'); } catch (e) { return null; }
  }
  function applyTheme(theme) {
    doc.setAttribute('data-theme', theme);
  }
  /* Dark Premium is the brand default on first visit; an explicit choice (toggle) persists and wins. */
  var initial = storedTheme();
  applyTheme(initial === 'light' ? 'light' : 'dark');

  function toast(message) {
    var el = document.querySelector('[data-toast]');
    if (!el) { return; }
    el.textContent = message;
    el.hidden = false;
    clearTimeout(el._timer);
    el._timer = setTimeout(function () { el.hidden = true; }, 2600);
  }

  function copyText(text) {
    var done = function () { toast(doc.lang === 'fr' ? 'Lien copié dans le presse-papiers' : 'Link copied to clipboard'); };
    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard.writeText(text).then(done, function () { fallbackCopy(text); done(); });
    } else { fallbackCopy(text); done(); }
  }
  function fallbackCopy(text) {
    var ta = document.createElement('textarea');
    ta.value = text; ta.setAttribute('readonly', ''); ta.style.position = 'fixed'; ta.style.opacity = '0';
    document.body.appendChild(ta); ta.select();
    try { document.execCommand('copy'); } catch (e) { /* ignore */ }
    document.body.removeChild(ta);
  }

  document.addEventListener('DOMContentLoaded', function () {
    /* Theme toggle */
    document.querySelectorAll('[data-theme-toggle]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var next = doc.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
        applyTheme(next);
        try { localStorage.setItem('ca-theme', next); } catch (e) { /* storage unavailable */ }
      });
    });

    /* Header shadow on scroll */
    var header = document.querySelector('[data-header]');
    if (header) {
      var onScroll = function () { header.classList.toggle('is-scrolled', window.scrollY > 8); };
      window.addEventListener('scroll', onScroll, { passive: true }); onScroll();
    }

    /* Mobile navigation */
    var navToggle = document.querySelector('[data-nav-toggle]');
    var nav = document.querySelector('[data-nav]');
    if (navToggle && nav) {
      navToggle.addEventListener('click', function () {
        var open = nav.classList.toggle('is-open');
        navToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        document.body.style.overflow = open ? 'hidden' : '';
      });
    }

    /* Dropdowns (click + keyboard, closes on outside click / Escape) */
    var dropdownButtons = document.querySelectorAll('[data-dropdown]');
    function closeAll(except) {
      dropdownButtons.forEach(function (b) {
        if (b !== except) { b.setAttribute('aria-expanded', 'false'); b.parentElement.classList.remove('is-open'); }
      });
    }
    dropdownButtons.forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.stopPropagation();
        var open = btn.getAttribute('aria-expanded') !== 'true';
        closeAll(btn);
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        btn.parentElement.classList.toggle('is-open', open);
      });
    });
    document.addEventListener('click', function (e) { if (!e.target.closest('.has-dropdown')) { closeAll(); } });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        var openBtn = document.querySelector('[data-dropdown][aria-expanded="true"]');
        closeAll(); if (openBtn) { openBtn.focus(); }
      }
    });

    /* Flash dismiss */
    document.querySelectorAll('[data-dismiss]').forEach(function (b) {
      b.addEventListener('click', function () { b.closest('.flash').remove(); });
    });

    /* Progress bars, bar charts and sparklines */
    function fillBars(root) {
      (root || document).querySelectorAll('[data-progress]').forEach(function (bar) {
        var value = Math.max(0, Math.min(100, parseInt(bar.getAttribute('data-progress'), 10) || 0));
        if (bar.classList.contains('spark-chart__bar')) { bar.style.height = Math.max(value, 3) + '%'; } else { bar.style.width = value + '%'; }
      });
      (root || document).querySelectorAll('[data-score]').forEach(function (ring) {
        ring.style.setProperty('--score', parseInt(ring.getAttribute('data-score'), 10) || 0);
      });
    }

    /* Reveal on scroll + animated counters */
    var revealTargets = document.querySelectorAll('.reveal');
    function animateCount(el) {
      var target = parseInt(el.getAttribute('data-count'), 10);
      if (isNaN(target) || reduceMotion || target === 0) { return; }
      var formatted = el.textContent;
      var start = null; var duration = 1200;
      var step = function (ts) {
        if (!start) { start = ts; }
        var p = Math.min((ts - start) / duration, 1);
        el.textContent = Math.round(target * (1 - Math.pow(1 - p, 3))).toLocaleString(doc.lang);
        if (p < 1) { requestAnimationFrame(step); } else { el.textContent = formatted; }
      };
      requestAnimationFrame(step);
    }
    if ('IntersectionObserver' in window && !reduceMotion) {
      var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) { return; }
          entry.target.classList.add('is-visible');
          entry.target.querySelectorAll('[data-count]').forEach(animateCount);
          fillBars(entry.target);
          io.unobserve(entry.target);
        });
      }, { rootMargin: '0px 0px -40px 0px', threshold: 0.08 });
      revealTargets.forEach(function (el) { io.observe(el); });
      setTimeout(function () { fillBars(); }, 150);
    } else {
      revealTargets.forEach(function (el) { el.classList.add('is-visible'); });
      fillBars();
    }

    /* Copy-to-clipboard buttons */
    document.querySelectorAll('[data-copy]').forEach(function (btn) {
      btn.addEventListener('click', function () { copyText(btn.getAttribute('data-copy')); });
    });

    /* Social share workflow logging (owner-only, optional) */
    document.querySelectorAll('[data-share]').forEach(function (panel) {
      var canRecord = panel.getAttribute('data-can-record') === '1';
      panel.querySelectorAll('[data-network]').forEach(function (el) {
        el.addEventListener('click', function () {
          var network = el.getAttribute('data-network');
          if (network === 'tiktok' && el.getAttribute('data-copy-after')) {
            copyText(el.getAttribute('data-copy-after'));
            toast(doc.lang === 'fr' ? 'Image téléchargée et lien copié : publiez-les depuis l’application TikTok.' : 'Image downloaded and link copied — post them from the TikTok app.');
          }
          if (!canRecord || !window.fetch) { return; }
          var body = new URLSearchParams();
          body.set('_token', csrf); body.set('subject_type', panel.getAttribute('data-subject-type'));
          body.set('subject_id', panel.getAttribute('data-subject-id')); body.set('network', network);
          fetch(panel.getAttribute('data-record-url'), { method: 'POST', body: body, credentials: 'same-origin', headers: { 'Accept': 'application/json' } }).catch(function () {});
        });
      });
    });

    /* Prevent double submission; optional confirmation */
    document.querySelectorAll('form').forEach(function (form) {
      form.addEventListener('submit', function (e) {
        var submitter = e.submitter || form.querySelector('[type="submit"]');
        var confirmMsg = submitter && submitter.getAttribute('data-confirm');
        if (confirmMsg && !form._confirmed && !window.confirm(confirmMsg)) { e.preventDefault(); return; }
        if (submitter && submitter.hasAttribute('data-once')) {
          if (form._submitted) { e.preventDefault(); return; }
          form._submitted = true;
          setTimeout(function () { submitter.disabled = true; submitter.setAttribute('aria-disabled', 'true'); }, 0);
        }
      });
    });

    /* Accessible tabs (roadmap and admin translation editors) */
    function setupTabs(container, tabSelector) {
      var tabs = Array.prototype.slice.call(container.querySelectorAll(tabSelector));
      function activate(tab) {
        tabs.forEach(function (t) {
          var selected = t === tab;
          t.setAttribute('aria-selected', selected ? 'true' : 'false');
          t.tabIndex = selected ? 0 : -1;
          var panel = document.getElementById(t.getAttribute('aria-controls'));
          if (panel) { panel.hidden = !selected; if (selected) { fillBars(panel); panel.querySelectorAll('.reveal').forEach(function (r) { r.classList.add('is-visible'); }); } }
        });
      }
      tabs.forEach(function (tab, i) {
        tab.addEventListener('click', function () { activate(tab); });
        tab.addEventListener('keydown', function (e) {
          var next = null;
          if (e.key === 'ArrowRight') { next = tabs[(i + 1) % tabs.length]; }
          if (e.key === 'ArrowLeft') { next = tabs[(i - 1 + tabs.length) % tabs.length]; }
          if (next) { e.preventDefault(); next.focus(); activate(next); }
        });
      });
    }
    document.querySelectorAll('[data-roadmap]').forEach(function (c) { setupTabs(c, '[role="tab"]'); });
    document.querySelectorAll('[data-tabs]').forEach(function (c) { setupTabs(c, '[role="tab"]'); });

    /* Lesson: visible-time heartbeat and completion countdown */
    var lesson = document.querySelector('[data-lesson]');
    if (lesson && window.fetch) {
      var url = lesson.getAttribute('data-heartbeat-url');
      var visibleSeconds = 0;
      setInterval(function () {
        if (document.visibilityState !== 'visible') { return; }
        visibleSeconds += 1;
        if (visibleSeconds >= 30) {
          var body = new URLSearchParams();
          body.set('_token', csrf); body.set('seconds', String(visibleSeconds));
          visibleSeconds = 0;
          fetch(url, { method: 'POST', body: body, credentials: 'same-origin', headers: { 'Accept': 'application/json' } }).catch(function () {});
        }
      }, 1000);
    }
    var completeForm = document.querySelector('[data-complete-form]');
    if (completeForm) {
      var remaining = parseInt(completeForm.getAttribute('data-min-seconds'), 10) || 0;
      var btn = completeForm.querySelector('button');
      var hint = completeForm.querySelector('[data-complete-hint]');
      var original = hint ? hint.textContent : '';
      if (remaining > 0 && btn) {
        btn.disabled = true;
        var tick = setInterval(function () {
          remaining -= 1;
          if (hint) { hint.textContent = original.replace(/\d+/, String(Math.max(remaining, 0))); }
          if (remaining <= 0) { clearInterval(tick); btn.disabled = false; if (hint) { hint.textContent = ''; } }
        }, 1000);
      }
    }

    /* Exam timer: display only; the server enforces the deadline */
    var timer = document.querySelector('[data-exam-timer]');
    if (timer) {
      var left = parseInt(timer.getAttribute('data-remaining'), 10) || 0;
      var display = timer.querySelector('[data-timer-display]');
      var announce = document.querySelector('[data-timer-announce]');
      var form = document.getElementById(timer.getAttribute('data-form'));
      var box = timer.querySelector('.exam-bar__timer');
      var warned = false;
      var render = function () {
        var m = Math.floor(left / 60); var s = left % 60;
        display.textContent = (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
      };
      render();
      var interval = setInterval(function () {
        left -= 1;
        if (left <= 60 && !warned) {
          warned = true; box.classList.add('is-low');
          if (announce) { announce.textContent = doc.lang === 'fr' ? 'Il reste une minute.' : 'One minute remaining.'; }
        }
        if (left <= 0) {
          clearInterval(interval); left = 0; render();
          if (form && !form._submitted) { form._submitted = true; form._confirmed = true; form.submit(); }
          return;
        }
        render();
      }, 1000);
      if (form) {
        form.addEventListener('change', function () { form._dirty = true; });
        form.addEventListener('submit', function () { form._dirty = false; });
        window.addEventListener('beforeunload', function (e) { if (form._dirty) { e.preventDefault(); e.returnValue = ''; } });
      }
    }

    /* Lab log viewer: filters dataset rows client-side (data fetched as JSON) */
    document.querySelectorAll('[data-log-viewer]').forEach(function (viewer) {
      var tableBox = viewer.querySelector('[data-log-table]');
      var filter = viewer.querySelector('[data-log-filter]');
      var count = viewer.querySelector('[data-log-count]');
      var fr = doc.lang === 'fr';
      fetch(viewer.getAttribute('data-src'), { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
        .then(function (r) { if (!r.ok) { throw new Error(String(r.status)); } return r.json(); })
        .then(function (data) {
          tableBox.innerHTML = '';
          if (!data.columns || !data.columns.length) {
            var pre = document.createElement('pre'); pre.textContent = data.raw || ''; tableBox.appendChild(pre);
            if (filter) { filter.hidden = true; }
            return;
          }
          var table = document.createElement('table');
          var thead = document.createElement('thead'); var hr = document.createElement('tr');
          data.columns.forEach(function (c) { var th = document.createElement('th'); th.scope = 'col'; th.textContent = c; hr.appendChild(th); });
          thead.appendChild(hr); table.appendChild(thead);
          var tbody = document.createElement('tbody'); table.appendChild(tbody);
          tableBox.appendChild(table);
          var rows = data.rows.map(function (cells) {
            var tr = document.createElement('tr');
            cells.forEach(function (v) { var td = document.createElement('td'); td.textContent = v; tr.appendChild(td); });
            return { tr: tr, text: cells.join(' ').toLowerCase() };
          });
          var renderRows = function () {
            var terms = (filter.value || '').toLowerCase().split(/\s+/).filter(Boolean);
            var frag = document.createDocumentFragment(); var shown = 0;
            rows.forEach(function (row) {
              var match = terms.every(function (t) { return row.text.indexOf(t) !== -1; });
              if (match) { frag.appendChild(row.tr); shown++; }
            });
            tbody.innerHTML = ''; tbody.appendChild(frag);
            count.textContent = (fr ? shown + ' ligne(s) sur ' + rows.length : shown + ' of ' + rows.length + ' rows');
          };
          var debounce;
          filter.addEventListener('input', function () { clearTimeout(debounce); debounce = setTimeout(renderRows, 120); });
          renderRows();
        })
        .catch(function () { tableBox.textContent = fr ? 'Impossible de charger le jeu de données.' : 'The dataset could not be loaded.'; });
    });

    /* Animated network background (canvas) */
    document.querySelectorAll('[data-network]').forEach(function (canvas) {
      if (reduceMotion || !canvas.getContext) { return; }
      var ctx = canvas.getContext('2d');
      var nodes = []; var w = 0; var h = 0; var dpr = Math.min(window.devicePixelRatio || 1, 2); var running = true;
      function resize() {
        var rect = canvas.getBoundingClientRect();
        w = rect.width; h = rect.height;
        canvas.width = w * dpr; canvas.height = h * dpr; ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        var count = Math.min(70, Math.floor((w * h) / 16000));
        nodes = [];
        for (var i = 0; i < count; i++) {
          nodes.push({ x: Math.random() * w, y: Math.random() * h, vx: (Math.random() - 0.5) * 0.25, vy: (Math.random() - 0.5) * 0.25, r: Math.random() * 1.6 + 0.6 });
        }
      }
      function color(alpha) {
        return doc.getAttribute('data-theme') === 'light' ? 'rgba(3,105,161,' + alpha + ')' : 'rgba(56,189,248,' + alpha + ')';
      }
      function frame() {
        if (!running) { return; }
        ctx.clearRect(0, 0, w, h);
        for (var i = 0; i < nodes.length; i++) {
          var a = nodes[i];
          a.x += a.vx; a.y += a.vy;
          if (a.x < 0 || a.x > w) { a.vx *= -1; }
          if (a.y < 0 || a.y > h) { a.vy *= -1; }
          for (var j = i + 1; j < nodes.length; j++) {
            var b = nodes[j]; var dx = a.x - b.x; var dy = a.y - b.y; var d = dx * dx + dy * dy;
            if (d < 16000) { ctx.strokeStyle = color(0.18 * (1 - d / 16000)); ctx.lineWidth = 1; ctx.beginPath(); ctx.moveTo(a.x, a.y); ctx.lineTo(b.x, b.y); ctx.stroke(); }
          }
          ctx.fillStyle = color(0.7); ctx.beginPath(); ctx.arc(a.x, a.y, a.r, 0, Math.PI * 2); ctx.fill();
        }
        requestAnimationFrame(frame);
      }
      resize();
      window.addEventListener('resize', function () { clearTimeout(canvas._rt); canvas._rt = setTimeout(resize, 200); });
      if ('IntersectionObserver' in window) {
        new IntersectionObserver(function (entries) {
          var visible = entries[0].isIntersecting;
          if (visible && !running) { running = true; requestAnimationFrame(frame); } else if (!visible) { running = false; }
        }).observe(canvas);
      }
      document.addEventListener('visibilitychange', function () {
        if (document.hidden) { running = false; } else if (!running) { running = true; requestAnimationFrame(frame); }
      });
      requestAnimationFrame(frame);
    });
  });
})();
