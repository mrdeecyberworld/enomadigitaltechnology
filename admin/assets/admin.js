/* Enoma admin panel scripts */
(function () {
  'use strict';

  var uid = 0;
  function newId() { uid += 1; return 'n' + Date.now().toString(36) + uid; }

  // Mobile sidebar
  var sidebar = document.querySelector('[data-sidebar]');
  var menuBtn = document.querySelector('[data-menu-btn]');
  if (menuBtn) {
    menuBtn.addEventListener('click', function () {
      var open = sidebar.classList.toggle('is-open');
      menuBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  }

  // Confirmations
  document.addEventListener('submit', function (e) {
    var msg = e.target.getAttribute('data-confirm');
    if (msg && !window.confirm(msg)) e.preventDefault();
  });
  document.addEventListener('click', function (e) {
    var b = e.target.closest('[data-confirm-click]');
    if (b && !window.confirm(b.getAttribute('data-confirm-click'))) e.preventDefault();
  });

  // Select all
  document.querySelectorAll('[data-check-all]').forEach(function (all) {
    all.addEventListener('change', function () {
      all.closest('form').querySelectorAll('input[name="ids[]"]').forEach(function (c) { c.checked = all.checked; });
    });
  });
  document.querySelectorAll('[data-select-on-focus]').forEach(function (i) {
    i.addEventListener('focus', function () { i.select(); });
  });

  // Character counters
  function initCounters(root) {
    root.querySelectorAll('[data-counter]').forEach(function (el) {
      if (el.dataset.counterReady) return;
      el.dataset.counterReady = '1';
      var max = +el.getAttribute('data-counter');
      var out = document.createElement('span');
      out.className = 'counter';
      el.insertAdjacentElement('afterend', out);
      function upd() { out.textContent = el.value.length + ' / ' + max; out.classList.toggle('is-over', el.value.length > max); }
      el.addEventListener('input', upd);
      upd();
    });
  }
  initCounters(document);

  // Icon previews
  function iconSvg(name) {
    var s = document.querySelector('[data-icon-sprite] [data-name="' + name + '"]');
    return s ? s.innerHTML : '';
  }
  document.addEventListener('change', function (e) {
    if (e.target.matches('[data-icon-select]')) {
      var p = e.target.parentNode.querySelector('[data-icon-preview]');
      if (p) p.innerHTML = iconSvg(e.target.value);
    }
  });

  // Editor: repeaters + unsaved-changes guard
  var editor = document.querySelector('[data-editor]');
  if (editor) {
    var dirty = false;
    var bar = editor.querySelector('.savebar');
    var note = editor.querySelector('[data-dirty-note]');
    function markDirty() {
      if (dirty) return;
      dirty = true;
      bar.classList.add('is-dirty');
      note.textContent = 'You have unsaved changes.';
    }
    editor.addEventListener('input', markDirty);
    editor.addEventListener('change', markDirty);
    editor.addEventListener('submit', function () { dirty = false; });
    window.addEventListener('beforeunload', function (e) { if (dirty) { e.preventDefault(); e.returnValue = ''; } });

    editor.addEventListener('click', function (e) {
      var add = e.target.closest('[data-add]');
      if (add) {
        var rep = add.closest('[data-repeater]');
        var tpl = rep.querySelector(':scope > template[data-template]');
        var html = tpl.innerHTML.split(rep.getAttribute('data-token')).join(newId());
        var items = rep.querySelector(':scope > [data-items]');
        items.insertAdjacentHTML('beforeend', html);
        var item = items.lastElementChild;
        initCounters(item);
        var first = item.querySelector('input, textarea, select');
        if (first) first.focus();
        markDirty();
        return;
      }
      var tool = e.target.closest('[data-move], [data-remove]');
      if (tool) {
        e.preventDefault();
        var it = tool.closest('[data-item]');
        if (tool.hasAttribute('data-remove')) {
          if (window.confirm('Remove this item? It is deleted when you save.')) it.remove();
        } else if (tool.getAttribute('data-move') === 'up' && it.previousElementSibling) {
          it.parentNode.insertBefore(it, it.previousElementSibling);
        } else if (tool.getAttribute('data-move') === 'down' && it.nextElementSibling) {
          it.parentNode.insertBefore(it.nextElementSibling, it);
        }
        markDirty();
      }
    });

    // Live item titles
    editor.addEventListener('input', function (e) {
      var it = e.target.closest('[data-item]');
      if (!it) return;
      var title = it.querySelector(':scope > summary [data-item-title]');
      var key = title && title.getAttribute('data-title-field');
      if (key && e.target.name && e.target.name.slice(-(key.length + 2)) === '[' + key + ']' && e.target.closest('[data-item]') === it) {
        title.textContent = e.target.value || 'New item';
      }
    });
  }

  // Media library: drag & drop / multi-file upload
  var drop = document.querySelector('[data-dropzone]');
  if (drop) {
    var dropInput = drop.querySelector('[data-drop-input]');
    var status = drop.querySelector('[data-drop-status]');
    var csrf = drop.querySelector('input[name="csrf"]').value;
    function uploadAll(files) {
      files = Array.prototype.filter.call(files, function (f) { return /^image\//.test(f.type) || /\.(heic|heif)$/i.test(f.name); });
      if (!files.length) { status.textContent = 'Please choose image files.'; return; }
      var done = 0, failed = [];
      drop.classList.add('is-busy');
      function next(i) {
        if (i >= files.length) {
          drop.classList.remove('is-busy');
          if (failed.length) {
            status.innerHTML = '';
            failed.forEach(function (msg) { var p = document.createElement('p'); p.className = 'notice notice--error'; p.textContent = msg; status.appendChild(p); });
            if (done) { var ok = document.createElement('p'); ok.textContent = done + ' uploaded. '; var a = document.createElement('a'); a.href = '/admin/media'; a.textContent = 'Refresh to see them'; ok.appendChild(a); status.appendChild(ok); }
          } else {
            status.textContent = done + (done === 1 ? ' image' : ' images') + ' uploaded and optimized. Refreshing…';
            window.location.reload();
          }
          return;
        }
        status.textContent = 'Uploading and optimizing ' + (i + 1) + ' of ' + files.length + ': ' + files[i].name + '…';
        var fd = new FormData();
        fd.append('csrf', csrf); fd.append('file', files[i]);
        fetch('/admin/media', { method: 'POST', body: fd, credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
          .then(function (r) { return r.json().catch(function () { return { error: 'The server could not process this file (it may be too large for your hosting).' }; }); })
          .then(function (res) { if (res.error) failed.push(files[i].name + ': ' + res.error); else done++; })
          .catch(function () { failed.push(files[i].name + ': upload failed. Check your connection.'); })
          .then(function () { next(i + 1); });
      }
      next(0);
    }
    dropInput.addEventListener('change', function () { uploadAll(dropInput.files); dropInput.value = ''; });
    ['dragenter', 'dragover'].forEach(function (ev) { drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.add('is-over'); }); });
    ['dragleave', 'drop'].forEach(function (ev) { drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.remove('is-over'); }); });
    drop.addEventListener('drop', function (e) { if (e.dataTransfer && e.dataTransfer.files.length) uploadAll(e.dataTransfer.files); });
  }

  // Media library: focus point
  var labelsEl = document.querySelector('[data-focus-labels]');
  var focusLabels = labelsEl ? JSON.parse(labelsEl.textContent) : {};
  document.querySelectorAll('[data-focus-picker]').forEach(function (picker) {
    picker.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-focus]');
      if (!btn) return;
      var focus = btn.getAttribute('data-focus');
      var fd = new FormData();
      fd.append('csrf', document.querySelector('input[name="csrf"]').value);
      fd.append('action', 'focus'); fd.append('name', picker.getAttribute('data-name')); fd.append('focus', focus);
      fetch('/admin/media', { method: 'POST', body: fd, credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (!res.ok) return;
          picker.querySelectorAll('[data-focus]').forEach(function (b) { b.setAttribute('aria-checked', b === btn ? 'true' : 'false'); });
          var img = picker.querySelector('[data-focus-img]');
          img.className = focus === 'center' ? '' : 'focus-' + focus;
          var lbl = picker.parentNode.querySelector('[data-focus-label]');
          if (lbl) lbl.textContent = focusLabels[focus] || focus;
        });
    });
  });

  // Email settings: presets + show only the fields for the chosen method
  var emailForm = document.querySelector('[data-email-form]');
  if (emailForm) {
    var method = emailForm.querySelector('select[name="data[method]"]');
    var smtpGroup = emailForm.querySelector('input[name="data[smtp][host]"]').closest('fieldset');
    var resendField = emailForm.querySelector('input[name="data[resend_api_key]"]').closest('.field');
    function syncMethod() {
      smtpGroup.hidden = method.value !== 'smtp';
      resendField.hidden = method.value !== 'resend';
    }
    method.addEventListener('change', syncMethod);
    syncMethod();
    emailForm.querySelectorAll('[data-preset]').forEach(function (b) {
      b.addEventListener('click', function () {
        var p = JSON.parse(b.getAttribute('data-preset'));
        method.value = p.method;
        if (p.host) {
          emailForm.querySelector('input[name="data[smtp][host]"]').value = p.host;
          emailForm.querySelector('input[name="data[smtp][port]"]').value = p.port;
          emailForm.querySelector('select[name="data[smtp][encryption]"]').value = p.encryption;
        }
        syncMethod();
        method.dispatchEvent(new Event('change', { bubbles: true }));
        var focusEl = p.method === 'resend' ? resendField.querySelector('input') : emailForm.querySelector('input[name="data[smtp][username]"]');
        if (focusEl) focusEl.focus();
      });
    });
  }

  // Appearance: show the chosen accent hex and tick "use custom" when it changes
  var accentInput = document.querySelector('[data-accent-input]');
  if (accentInput) {
    var hexOut = document.querySelector('[data-accent-hex]');
    var useCustom = document.querySelector('[data-use-custom]');
    accentInput.addEventListener('input', function () { hexOut.textContent = accentInput.value; useCustom.checked = true; });
  }

  // Media picker
  var modal = document.querySelector('[data-media-modal]');
  var targetField = null;
  function setImage(field, path, url) {
    field.querySelector('[data-image-input]').value = path;
    var thumb = field.querySelector('[data-thumb]');
    thumb.innerHTML = '';
    if (url) { var img = document.createElement('img'); img.src = url; img.alt = ''; thumb.appendChild(img); }
    field.querySelector('[data-image-input]').dispatchEvent(new Event('change', { bubbles: true }));
  }
  function loadMedia() {
    var grid = modal.querySelector('[data-modal-grid]');
    grid.innerHTML = '<p class="hint">Loading…</p>';
    fetch('/admin/media?format=json', { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (list) {
        grid.innerHTML = '';
        if (!list.length) { grid.innerHTML = '<p class="hint">No images yet. Upload one above.</p>'; return; }
        list.forEach(function (m) {
          var b = document.createElement('button');
          b.type = 'button'; b.className = 'media-pick'; b.title = m.name;
          var img = document.createElement('img'); img.src = m.url; img.alt = m.name; img.loading = 'lazy';
          b.appendChild(img);
          b.addEventListener('click', function () { setImage(targetField, m.path, m.url); closeModal(); });
          grid.appendChild(b);
        });
      })
      .catch(function () { grid.innerHTML = '<p class="hint">Could not load images. Reload the page and try again.</p>'; });
  }
  function openModal(field) { targetField = field; modal.hidden = false; loadMedia(); modal.querySelector('[data-modal-close]').focus(); }
  function closeModal() { modal.hidden = true; if (targetField) targetField.querySelector('[data-media-pick]').focus(); }
  if (modal) {
    document.addEventListener('click', function (e) {
      var pick = e.target.closest('[data-media-pick]');
      if (pick) { openModal(pick.closest('[data-image-field]')); return; }
      var clear = e.target.closest('[data-media-clear]');
      if (clear) { setImage(clear.closest('[data-image-field]'), '', ''); return; }
      if (e.target === modal || e.target.closest('[data-modal-close]')) closeModal();
    });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !modal.hidden) closeModal(); });
    var fileInput = modal.querySelector('[data-modal-file]');
    fileInput.addEventListener('change', function () {
      if (!fileInput.files.length) return;
      var fd = new FormData(modal.querySelector('[data-modal-upload]'));
      var grid = modal.querySelector('[data-modal-grid]');
      grid.innerHTML = '<p class="hint">Uploading…</p>';
      fetch('/admin/media', { method: 'POST', body: fd, credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          fileInput.value = '';
          if (res.error) { grid.innerHTML = ''; var p = document.createElement('p'); p.className = 'notice notice--error'; p.textContent = res.error; grid.appendChild(p); return; }
          setImage(targetField, res.path, res.url); closeModal();
        })
        .catch(function () { grid.innerHTML = '<p class="hint">Upload failed. Try again.</p>'; });
    });
  }

  // Long-running buttons: show progress and prevent double submits.
  document.addEventListener('submit', function (e) {
    var btn = e.target.querySelector('button[data-busy-label]');
    if (!btn) return;
    btn.textContent = btn.getAttribute('data-busy-label');
    btn.setAttribute('aria-busy', 'true');
    setTimeout(function () { btn.disabled = true; }, 0);
  });
})();
