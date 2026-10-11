// public/script/admin.js : petites aides de l'administration (tout marche aussi sans JavaScript)
(function () {
  'use strict';

  // ---------- fenêtre de confirmation ----------
  var dialog = document.getElementById('confirm-dialog');
  var dialogText = document.getElementById('confirm-text');
  var dialogTitle = document.getElementById('confirm-title');
  var dialogOk = document.getElementById('confirm-ok');
  var onConfirm = null;
  var onCancel = null;

  function ask(options) {
    if (!dialog || typeof dialog.showModal !== 'function') {
      // vieux navigateur : confirm() natif
      if (window.confirm(options.text)) { options.ok(); } else if (options.cancel) { options.cancel(); }
      return;
    }
    dialogTitle.textContent = options.title || 'Êtes-vous sûr ?';
    dialogText.textContent = options.text;
    dialogOk.textContent = options.okLabel || 'Supprimer';
    dialogOk.className = 'admin-btn ' + (options.danger === false ? '' : 'admin-btn--danger');
    dialog.classList.toggle('is-neutral', options.danger === false);
    onConfirm = options.ok;
    onCancel = options.cancel || null;
    dialog.returnValue = '';
    dialog.showModal();
  }

  if (dialog) {
    dialog.addEventListener('close', function () {
      var ok = dialog.returnValue === 'ok';
      var callback = ok ? onConfirm : onCancel;
      onConfirm = onCancel = null;
      if (callback) { callback(); }
    });
    // clic sur le fond = annuler
    dialog.addEventListener('click', function (e) {
      if (e.target === dialog) { dialog.close('cancel'); }
    });
  }

  document.addEventListener('submit', function (e) {
    var form = e.target;
    if (!form.matches('form[data-confirm]') || form.dataset.confirmed === '1') { return; }
    e.preventDefault();
    var submitter = e.submitter || null;
    ask({
      text: form.dataset.confirm,
      ok: function () {
        form.dataset.confirmed = '1';
        if (submitter && submitter.name) {
          // garde la valeur du bouton cliqué (ex. do=delete)
          var hidden = document.createElement('input');
          hidden.type = 'hidden';
          hidden.name = submitter.name;
          hidden.value = submitter.value;
          form.appendChild(hidden);
        }
        form.submit();
      }
    });
  });

  // ---------- changement de rôle ----------
  document.querySelectorAll('form[data-confirm-role] select').forEach(function (select) {
    var previous = select.value;
    select.addEventListener('change', function () {
      var form = select.form;
      var name = form.dataset.confirmRole;
      var toAdmin = select.value === 'admin';
      ask({
        title: toAdmin ? 'Nommer administrateur ?' : 'Retirer les droits ?',
        text: toAdmin
          ? name + ' pourra gérer les recettes, les avis, les messages et les comptes.'
          : name + ' n\'aura plus accès à l\'administration.',
        okLabel: 'Confirmer',
        danger: !toAdmin,
        ok: function () { form.submit(); },
        cancel: function () { select.value = previous; }
      });
    });
  });

  // ---------- message flash ----------
  var toast = document.querySelector('.admin-toast');
  if (toast) {
    var hide = function () { toast.classList.add('is-hidden'); };
    toast.querySelector('.admin-toast-close').addEventListener('click', hide);
    if (toast.classList.contains('admin-toast--success')) { setTimeout(hide, 6000); }
  }

  // ---------- lignes d'ingrédients et d'étapes ----------
  function renumber(list) {
    list.querySelectorAll('.admin-step-num').forEach(function (n, i) { n.textContent = i + 1; });
  }

  function resetRow(row) {
    row.querySelectorAll('input, textarea, select').forEach(function (field) {
      if (field.tagName === 'SELECT') {
        field.selectedIndex = 0;
      } else {
        field.value = ''; // y compris la photo actuelle (step_image_old[])
      }
    });
    var img = row.querySelector('.admin-upload-img');
    if (img) { img.innerHTML = '<span>Aucune photo</span>'; }
    var file = row.querySelector('input[type="file"]');
    var label = file && file.closest('.admin-field').querySelector('label');
    if (label) { label.textContent = 'Photo'; }
  }

  document.querySelectorAll('[data-add-row]').forEach(function (button) {
    var list = document.getElementById(button.dataset.addRow);
    button.addEventListener('click', function () {
      var rows = list.querySelectorAll('.admin-row');
      var row = rows[rows.length - 1].cloneNode(true);
      resetRow(row);
      list.appendChild(row);
      renumber(list);
      var first = row.querySelector('select, input:not([type="hidden"]), textarea');
      if (first) { first.focus(); }
    });
  });

  document.addEventListener('click', function (e) {
    var remove = e.target.closest('[data-remove-row]');
    if (remove) {
      var row = remove.closest('.admin-row');
      var list = row.parentElement;
      if (list.querySelectorAll('.admin-row').length > 1) {
        row.remove();
      } else {
        resetRow(row); // on garde toujours une ligne vide
      }
      renumber(list);
      return;
    }
    var move = e.target.closest('[data-move]');
    if (move) {
      var item = move.closest('.admin-row');
      if (move.dataset.move === '-1' && item.previousElementSibling) {
        item.parentElement.insertBefore(item, item.previousElementSibling);
      } else if (move.dataset.move === '1' && item.nextElementSibling) {
        item.parentElement.insertBefore(item.nextElementSibling, item);
      }
      renumber(item.parentElement);
      move.focus();
    }
  });

  document.querySelectorAll('.admin-steps').forEach(renumber);

  // ---------- aperçu des photos choisies ----------
  document.addEventListener('change', function (e) {
    var input = e.target;
    if (input.type !== 'file') { return; }
    var box = input.closest('[data-preview]');
    var holder = box && box.querySelector('.admin-upload-img');
    if (!holder || !input.files || !input.files[0]) { return; }
    var file = input.files[0];
    if (file.size > 3 * 1024 * 1024) {
      holder.innerHTML = '<span class="admin-upload-error">Trop lourde (3 Mo max)</span>';
      input.value = '';
      return;
    }
    var img = document.createElement('img');
    img.alt = 'Nouvelle photo';
    img.src = URL.createObjectURL(file);
    holder.innerHTML = '';
    holder.appendChild(img);
  });

  // ---------- slug proposé à partir du titre (nouvelle recette) ----------
  var title = document.getElementById('title');
  var slug = document.getElementById('slug');
  if (title && slug && slug.value === '') {
    var touched = false;
    slug.addEventListener('input', function () { touched = slug.value !== ''; });
    title.addEventListener('input', function () {
      if (touched) { return; }
      slug.value = title.value.normalize('NFD').replace(/[̀-ͯ]/g, '')
        .toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').slice(0, 170);
    });
  }
})();
