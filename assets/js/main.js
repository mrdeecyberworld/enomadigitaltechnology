/* Enoma Digital Technologies — site scripts (vanilla JS, no dependencies). */
(function () {
  'use strict';

  var doc = document.documentElement;
  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---------- Scroll reveal ---------- */
  function initReveal() {
    var items = Array.prototype.slice.call(document.querySelectorAll('.reveal'));
    if (reduceMotion || !('IntersectionObserver' in window)) {
      return;
    }
    // Anything already on screen stays visible (no flash).
    items.forEach(function (el) {
      var r = el.getBoundingClientRect();
      if (r.top < window.innerHeight && r.bottom > 0) el.classList.add('is-visible');
    });
    doc.classList.add('js');
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          io.unobserve(entry.target);
        }
      });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });
    items.forEach(function (el) { if (!el.classList.contains('is-visible')) io.observe(el); });
  }

  /* ---------- Sticky header state ---------- */
  function initHeader() {
    var header = document.querySelector('[data-header]');
    if (!header) return;
    var ticking = false;
    function update() {
      header.classList.toggle('is-scrolled', window.scrollY > 8);
      ticking = false;
    }
    window.addEventListener('scroll', function () {
      if (!ticking) { window.requestAnimationFrame(update); ticking = true; }
    }, { passive: true });
    update();
  }

  /* ---------- Desktop services menu ---------- */
  function initMenus() {
    document.querySelectorAll('[data-menu]').forEach(function (item) {
      var toggle = item.querySelector('[data-menu-toggle]');
      var panel = item.querySelector('[data-menu-panel]');
      var closeTimer;

      function open() {
        clearTimeout(closeTimer);
        item.classList.add('is-open');
        toggle.setAttribute('aria-expanded', 'true');
      }
      function close() {
        item.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
      }

      toggle.addEventListener('click', function () {
        if (item.classList.contains('is-open')) { close(); } else { open(); }
      });
      if (window.matchMedia('(hover: hover)').matches) {
        item.addEventListener('mouseenter', open);
        item.addEventListener('mouseleave', function () { closeTimer = setTimeout(close, 140); });
      }
      item.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && item.classList.contains('is-open')) {
          close();
          toggle.focus();
        }
      });
      item.addEventListener('focusout', function (e) {
        if (!item.contains(e.relatedTarget)) close();
      });
      document.addEventListener('click', function (e) {
        if (!item.contains(e.target)) close();
      });
      panel.addEventListener('click', function (e) {
        if (e.target.closest('a')) close();
      });
    });
  }

  /* ---------- Mobile navigation ---------- */
  function initMobileNav() {
    var toggle = document.querySelector('[data-nav-toggle]');
    var panel = document.querySelector('[data-mobile-nav]');
    if (!toggle || !panel) return;
    var label = toggle.querySelector('.sr-only');

    function focusables() {
      return Array.prototype.slice.call(panel.querySelectorAll('a[href], button, summary')).filter(function (el) {
        return el.offsetParent !== null;
      });
    }
    function open() {
      panel.hidden = false;
      toggle.setAttribute('aria-expanded', 'true');
      if (label) label.textContent = 'Close menu';
      document.body.classList.add('nav-open');
      var first = focusables()[0];
      if (first) first.focus();
    }
    function close(returnFocus) {
      panel.hidden = true;
      toggle.setAttribute('aria-expanded', 'false');
      if (label) label.textContent = 'Open menu';
      document.body.classList.remove('nav-open');
      if (returnFocus) toggle.focus();
    }

    toggle.addEventListener('click', function () {
      if (panel.hidden) { open(); } else { close(false); }
    });
    document.addEventListener('keydown', function (e) {
      if (panel.hidden) return;
      if (e.key === 'Escape') { close(true); return; }
      if (e.key === 'Tab') {
        // Keep focus inside the header (toggle + menu) while open.
        var items = [toggle].concat(focusables());
        var first = items[0];
        var last = items[items.length - 1];
        if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
        else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
      }
    });
    panel.addEventListener('click', function (e) {
      if (e.target.closest('a')) close(false);
    });
    window.addEventListener('resize', function () {
      if (window.innerWidth >= 1024 && !panel.hidden) close(false);
    });
  }

  /* ---------- Remote image fallback ---------- */
  function initImages() {
    document.querySelectorAll('.media-frame img, .post-card__media img, .scene-photo img').forEach(function (img) {
      function fail() {
        var backup = img.getAttribute('data-fallback');
        if (backup) {
          // Main photo unavailable: use the built-in backup photo once.
          img.removeAttribute('data-fallback');
          img.removeAttribute('srcset');
          img.alt = img.getAttribute('data-fallback-alt') || img.alt;
          if (img.getAttribute('data-fallback-class')) img.classList.add(img.getAttribute('data-fallback-class'));
          img.src = backup;
          return;
        }
        img.parentNode.classList.add('img-failed');
      }
      if (img.complete && img.naturalWidth === 0) fail();
      img.addEventListener('error', fail);
    });
  }

  /* ---------- Homepage slider ----------
     Slides cross-fade on their own. Autoplay is driven by the progress bar's CSS
     animation on the active dot: when it ends, the next slide shows. The pause
     button (and scrolling the slider off screen) just pauses that animation. */
  function initSlider() {
    document.querySelectorAll('[data-slider]').forEach(function (slider) {
      var track = slider.querySelector('[data-slider-track]');
      var slides = Array.prototype.slice.call(slider.querySelectorAll('[data-slide]'));
      var dots = Array.prototype.slice.call(slider.querySelectorAll('[data-slider-dot]'));
      var section = slider.closest('section') || slider;
      var pauseBtn = slider.querySelector('[data-slider-pause]');
      var index = 0;
      if (!track || slides.length < 2) {
        if (pauseBtn) pauseBtn.hidden = true;
        return;
      }

      function go(i) {
        index = (i + slides.length) % slides.length;
        slides.forEach(function (s, n) {
          var on = n === index;
          s.classList.toggle('is-active', on);
          if (on) { s.removeAttribute('inert'); s.removeAttribute('aria-hidden'); }
          else { s.setAttribute('inert', ''); s.setAttribute('aria-hidden', 'true'); }
        });
        dots.forEach(function (d, n) {
          d.classList.toggle('is-active', n === index);
          if (n === index) d.setAttribute('aria-current', 'true'); else d.removeAttribute('aria-current');
        });
      }

      function setPaused(paused) {
        slider.classList.toggle('is-paused', paused);
        if (pauseBtn) pauseBtn.setAttribute('aria-label', paused ? 'Play slides' : 'Pause slides');
      }

      section.querySelectorAll('[data-slider-prev]').forEach(function (b) { b.addEventListener('click', function () { go(index - 1); }); });
      section.querySelectorAll('[data-slider-next]').forEach(function (b) { b.addEventListener('click', function () { go(index + 1); }); });
      dots.forEach(function (d, n) { d.addEventListener('click', function () { go(n); }); });
      if (pauseBtn) pauseBtn.addEventListener('click', function () { setPaused(!slider.classList.contains('is-paused')); });

      slider.addEventListener('animationend', function (e) {
        if (e.target.parentNode && e.target.parentNode.classList && e.target.parentNode.classList.contains('is-active') && e.target.parentNode.hasAttribute('data-slider-dot')) go(index + 1);
      });

      slider.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowLeft') { go(index - 1); e.preventDefault(); }
        if (e.key === 'ArrowRight') { go(index + 1); e.preventDefault(); }
      });

      // Swipe
      var startX = null, startY = 0;
      track.addEventListener('pointerdown', function (e) { if (e.pointerType !== 'mouse') { startX = e.clientX; startY = e.clientY; } });
      track.addEventListener('pointerup', function (e) {
        if (startX === null) return;
        var dx = e.clientX - startX, dy = e.clientY - startY;
        startX = null;
        if (Math.abs(dx) > 50 && Math.abs(dx) > Math.abs(dy)) go(index + (dx < 0 ? 1 : -1));
      });
      track.addEventListener('pointercancel', function () { startX = null; });

      // Only run while the slider is on screen.
      if ('IntersectionObserver' in window) {
        new IntersectionObserver(function (entries) {
          slider.classList.toggle('is-offscreen', !entries[0].isIntersecting);
        }, { threshold: 0.25 }).observe(slider);
      }

      slider.classList.add('is-ready');
      go(0);
    });
  }

  /* ---------- Form validation (server validates too) ---------- */
  function initForms() {
    document.querySelectorAll('[data-focus-on-load]').forEach(function (el) {
      el.focus({ preventScroll: false });
    });

    document.querySelectorAll('form[data-validate]').forEach(function (form) {
      var messages = {
        name: 'Please enter your name.',
        email: 'Please enter a valid email address, like name@example.com.',
        phone: 'Please enter a valid phone number, or leave this field blank.',
        service: 'Please choose the service you are interested in.',
        message: 'Please tell us a little more (at least 10 characters).'
      };

      function errorEl(field) {
        var id = field.id + '-error';
        var el = document.getElementById(id);
        if (!el) {
          el = document.createElement('p');
          el.className = 'field__error';
          el.id = id;
          field.closest('.field').appendChild(el);
        }
        return el;
      }
      function validate(field) {
        var v = field.value.trim();
        var bad = false;
        if (field.name === 'email') bad = !/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v);
        else if (field.name === 'phone') bad = v !== '' && !/^[0-9+().\-\s]{7,30}$/.test(v);
        else if (field.name === 'message') bad = v.length < 10;
        else if (field.required) bad = v === '';
        if (bad) {
          var el = errorEl(field);
          el.textContent = messages[field.name] || 'Please check this field.';
          field.setAttribute('aria-invalid', 'true');
          field.setAttribute('aria-describedby', el.id);
        } else {
          var existing = document.getElementById(field.id + '-error');
          if (existing) existing.remove();
          field.removeAttribute('aria-invalid');
          field.removeAttribute('aria-describedby');
        }
        return !bad;
      }

      var fields = Array.prototype.slice.call(form.querySelectorAll('input[name="name"], input[name="email"], input[name="phone"], select[name="service"], textarea[name="message"]'));
      fields.forEach(function (f) {
        f.addEventListener('blur', function () { if (f.value.trim() !== '' || f.hasAttribute('aria-invalid')) validate(f); });
        f.addEventListener('input', function () { if (f.hasAttribute('aria-invalid')) validate(f); });
      });

      form.addEventListener('submit', function (e) {
        var firstBad = null;
        fields.forEach(function (f) {
          if (!validate(f) && !firstBad) firstBad = f;
        });
        if (firstBad) {
          e.preventDefault();
          firstBad.focus();
          return;
        }
        var btn = form.querySelector('button[type="submit"]');
        if (btn) { btn.disabled = true; btn.setAttribute('aria-busy', 'true'); }
      });
    });
  }

  /* ---------- Text rendering helpers (no innerHTML with user/AI text) ---------- */
  function renderRichText(container, text) {
    var blocks = String(text).split(/\n{2,}/);
    blocks.forEach(function (block) {
      var lines = block.split('\n');
      var list = null;
      var para = null;
      lines.forEach(function (line) {
        var m = line.match(/^\s*[-•*]\s+(.*)$/);
        if (m) {
          if (!list) { list = document.createElement('ul'); container.appendChild(list); para = null; }
          var li = document.createElement('li');
          li.textContent = m[1];
          list.appendChild(li);
        } else if (line.trim() !== '') {
          list = null;
          if (!para) { para = document.createElement('p'); container.appendChild(para); }
          else { para.appendChild(document.createElement('br')); }
          para.appendChild(document.createTextNode(line.trim()));
        }
      });
    });
  }
  function safeLocalUrl(url) {
    return typeof url === 'string' && /^\/(?!\/)[A-Za-z0-9/_#?=&.-]*$/.test(url) ? url : null;
  }

  /* ---------- AI assistant ---------- */
  function initAssistant() {
    document.querySelectorAll('[data-assistant]').forEach(function (root) {
      var endpoint = root.getAttribute('data-endpoint');
      var log = root.querySelector('[data-assistant-log]');
      var form = root.querySelector('[data-assistant-form]');
      var input = root.querySelector('[data-assistant-input]');
      var send = root.querySelector('[data-assistant-send]');
      var chips = root.querySelector('[data-assistant-chips]');
      var reset = root.querySelector('[data-assistant-reset]');
      var initialHTML = log.innerHTML;
      var initialChips = chips.innerHTML;
      var history = [];
      var busy = false;
      var STORE = 'enoma-assistant';

      function scroll() { log.scrollTop = log.scrollHeight; }

      function addMessage(role, text, links) {
        var el = document.createElement('div');
        el.className = 'msg msg--' + role;
        if (role === 'user') {
          var p = document.createElement('p');
          p.textContent = text;
          el.appendChild(p);
        } else {
          renderRichText(el, text);
          if (links && links.length) {
            var wrap = document.createElement('div');
            wrap.className = 'msg__links';
            links.forEach(function (l) {
              var url = safeLocalUrl(l.url);
              if (!url) return;
              var a = document.createElement('a');
              a.href = url;
              a.textContent = l.label;
              wrap.appendChild(a);
            });
            el.appendChild(wrap);
          }
        }
        log.appendChild(el);
        scroll();
      }

      function setChips(list) {
        chips.innerHTML = '';
        (list || []).slice(0, 4).forEach(function (s) {
          var b = document.createElement('button');
          b.type = 'button';
          b.className = 'chip';
          b.textContent = s;
          b.setAttribute('data-assistant-suggest', '');
          chips.appendChild(b);
        });
      }

      function save() {
        try { sessionStorage.setItem(STORE, JSON.stringify(history.slice(-12))); } catch (e) { /* storage unavailable */ }
      }

      function restore() {
        var saved = null;
        try { saved = JSON.parse(sessionStorage.getItem(STORE) || 'null'); } catch (e) { saved = null; }
        if (!Array.isArray(saved) || !saved.length) return;
        history = saved;
        history.forEach(function (m) { addMessage(m.role, m.content, m.links); });
        reset.hidden = false;
        setChips([]);
      }

      function typing(on) {
        var t = log.querySelector('.msg--typing');
        if (on && !t) {
          t = document.createElement('div');
          t.className = 'msg msg--assistant msg--typing';
          t.setAttribute('aria-label', 'Assistant is typing');
          t.innerHTML = '<span></span><span></span><span></span>';
          log.appendChild(t);
          scroll();
        } else if (!on && t) {
          t.remove();
        }
      }

      function submit(text) {
        text = text.trim();
        if (!text || busy) return;
        busy = true;
        send.disabled = true;
        reset.hidden = false;
        addMessage('user', text);
        history.push({ role: 'user', content: text });
        input.value = '';
        autosize();
        setChips([]);
        typing(true);

        var payload = history.map(function (m) { return { role: m.role, content: m.content }; });
        fetch(endpoint, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
          credentials: 'same-origin',
          body: JSON.stringify({ messages: payload })
        })
          .then(function (res) { return res.json().catch(function () { return {}; }); })
          .then(function (data) {
            typing(false);
            var reply = data && data.reply ? data.reply : "Sorry, I couldn't respond just now. You can still reach us through the contact page.";
            var links = data && data.links ? data.links : [{ label: 'Contact', url: '/contact' }];
            addMessage('assistant', reply, links);
            history.push({ role: 'assistant', content: reply, links: links });
            setChips(data && data.suggestions);
            save();
          })
          .catch(function () {
            typing(false);
            addMessage('assistant', "Sorry, I'm having trouble connecting. Please try again, or use the contact page.", [{ label: 'Contact', url: '/contact' }]);
          })
          .then(function () {
            busy = false;
            send.disabled = false;
          });
      }

      function autosize() {
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 140) + 'px';
      }

      form.addEventListener('submit', function (e) {
        e.preventDefault();
        submit(input.value);
      });
      input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
          e.preventDefault();
          submit(input.value);
        }
      });
      input.addEventListener('input', autosize);
      chips.addEventListener('click', function (e) {
        var b = e.target.closest('[data-assistant-suggest]');
        if (b) submit(b.textContent);
      });
      reset.addEventListener('click', function () {
        history = [];
        try { sessionStorage.removeItem(STORE); } catch (e) { /* ignore */ }
        log.innerHTML = initialHTML;
        chips.innerHTML = initialChips;
        reset.hidden = true;
        input.focus();
      });

      restore();
    });
  }

  /* ---------- Service finder ---------- */
  function initFinder() {
    document.querySelectorAll('[data-finder]').forEach(function (root) {
      var data;
      try { data = JSON.parse(root.querySelector('[data-finder-data]').textContent); } catch (e) { return; }
      var steps = root.querySelectorAll('[data-finder-step]');
      var bar = root.querySelector('[data-finder-bar]');
      var q2 = root.querySelector('[data-finder-q2]');
      var answersEl = root.querySelector('[data-finder-answers]');
      var resultEl = root.querySelector('[data-finder-result]');
      var need = null;

      function show(n) {
        steps.forEach(function (s) { s.hidden = s.getAttribute('data-finder-step') !== String(n); });
        bar.style.width = (n / 3 * 100) + '%';
        var heading = root.querySelector('[data-finder-step="' + n + '"] .finder__question');
        if (heading) heading.focus({ preventScroll: true });
      }

      function recCard(slug, primary) {
        var s = data.services[slug];
        if (!s) return null;
        var card = document.createElement('div');
        card.className = 'finder__rec' + (primary ? ' finder__rec--primary' : '');
        var icon = document.createElement('span');
        icon.className = 'finder__rec-icon';
        icon.innerHTML = s.icon; // trusted SVG generated by the server
        var body = document.createElement('div');
        var label = document.createElement('p');
        label.className = 'finder__rec-label';
        label.textContent = primary ? 'Recommended' : 'Also consider';
        var h = document.createElement('h4');
        h.textContent = s.name;
        var p = document.createElement('p');
        p.textContent = s.summary;
        var a = document.createElement('a');
        a.href = s.path;
        a.textContent = 'Learn about ' + s.name + ' →';
        body.appendChild(label); body.appendChild(h); body.appendChild(p); body.appendChild(a);
        card.appendChild(icon); card.appendChild(body);
        return card;
      }

      root.addEventListener('click', function (e) {
        var needBtn = e.target.closest('[data-finder-need]');
        if (needBtn) {
          need = data.needs[needBtn.getAttribute('data-finder-need')];
          q2.textContent = need.question;
          answersEl.innerHTML = '';
          need.answers.forEach(function (ans, i) {
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'finder__option';
            b.setAttribute('data-finder-answer', i);
            var span = document.createElement('span');
            span.textContent = ans.label;
            b.appendChild(span);
            answersEl.appendChild(b);
          });
          show(2);
          return;
        }
        var ansBtn = e.target.closest('[data-finder-answer]');
        if (ansBtn && need) {
          var ans = need.answers[+ansBtn.getAttribute('data-finder-answer')];
          resultEl.innerHTML = '';
          var wrap = document.createElement('div');
          wrap.className = 'finder__result';
          var primary = recCard(ans.primary, true);
          if (primary) wrap.appendChild(primary);
          (ans.also || []).forEach(function (slug) {
            var c = recCard(slug, false);
            if (c) wrap.appendChild(c);
          });
          var note = document.createElement('p');
          note.className = 'finder__note';
          note.textContent = ans.note;
          wrap.appendChild(note);
          resultEl.appendChild(wrap);
          show(3);
          return;
        }
        if (e.target.closest('[data-finder-back]')) { show(1); return; }
        if (e.target.closest('[data-finder-restart]')) { need = null; show(1); }
      });
    });
  }

  /* Cards: a soft light follows the pointer (CSS reads --mx / --my). */
  function initSpotlight() {
    if (!window.matchMedia || !matchMedia('(hover: hover)').matches) return;
    var sel = '.service-card, .feature-card, .guide-card, .post-card, .detail-card, .product-card';
    document.addEventListener('pointermove', function (e) {
      var card = e.target.closest && e.target.closest(sel);
      if (!card) return;
      var r = card.getBoundingClientRect();
      card.style.setProperty('--mx', (e.clientX - r.left) + 'px');
      card.style.setProperty('--my', (e.clientY - r.top) + 'px');
    }, { passive: true });
  }

  /* Hero photo: a gentle 3D tilt that follows the pointer (CSS reads --rx / --ry / --gx). */
  function initTilt() {
    if (reduceMotion || !window.matchMedia || !matchMedia('(hover: hover)').matches) return;
    document.querySelectorAll('[data-tilt]').forEach(function (el) {
      var frame = 0;
      el.addEventListener('pointermove', function (e) {
        if (frame) return;
        frame = requestAnimationFrame(function () {
          frame = 0;
          var r = el.getBoundingClientRect();
          var x = (e.clientX - r.left) / r.width - 0.5;
          var y = (e.clientY - r.top) / r.height - 0.5;
          el.style.setProperty('--ry', (x * 8).toFixed(2) + 'deg');
          el.style.setProperty('--rx', (y * -8).toFixed(2) + 'deg');
          el.style.setProperty('--gx', ((x + 0.5) * 100).toFixed(1) + '%');
        });
      }, { passive: true });
      el.addEventListener('pointerleave', function () {
        el.style.removeProperty('--rx');
        el.style.removeProperty('--ry');
        el.style.removeProperty('--gx');
      });
    });
  }

  /* Pause ambient animation in sections that are off screen (saves battery and CPU). */
  function initAmbient() {
    if (!('IntersectionObserver' in window)) return;
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) { en.target.classList.toggle('is-offscreen', !en.isIntersecting); });
    });
    document.querySelectorAll('.hero, .page-hero, .post-hero, .ticker, .cta-band__panel').forEach(function (el) { io.observe(el); });
  }

  function init() {
    initReveal();
    initAmbient();
    initSpotlight();
    initTilt();
    initHeader();
    initMenus();
    initMobileNav();
    initImages();
    initSlider();
    initForms();
    initAssistant();
    initFinder();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
