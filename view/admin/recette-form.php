<?php
require __DIR__ . '/inc/top.php';
$editBase = '?pg=admin&section=recettes&action=edit&id=' . $id;
$tabs = ['infos' => 'Informations', 'ingredients' => 'Ingrédients', 'etapes' => 'Étapes'];
$counts = ['ingredients' => count($ingredientRows), 'etapes' => count($stepRows)];
$csrf = '<input type="hidden" name="csrf_token" value="' . h($_SESSION['csrf_token']) . '">';
$accept = 'image/jpeg,image/png,image/webp';
?>

<header class="admin-head">
  <div>
    <a class="admin-back" href="?pg=admin&section=recettes">← Recettes</a>
    <h1><?= $isNew ? 'Nouvelle recette' : h($recipe['title']) ?></h1>
  </div>
  <?php if (!$isNew): ?>
    <a class="admin-btn admin-btn--ghost" href="?page=recetteDetails&slug=<?= urlencode($recipe['slug']) ?>" target="_blank" rel="noopener">Voir sur le site</a>
  <?php endif; ?>
</header>

<nav class="admin-tabs" aria-label="Parties de la recette">
  <?php foreach ($tabs as $key => $label):
      $disabled = $isNew && $key !== 'infos'; ?>
    <?php if ($disabled): ?>
      <span class="admin-tab is-disabled" title="Enregistrez d'abord les informations"><?= $label ?></span>
    <?php else: ?>
      <a class="admin-tab<?= $tab === $key ? ' is-active' : '' ?>" href="<?= $isNew ? '#' : $editBase . '&tab=' . $key ?>"
         <?= $tab === $key ? 'aria-current="page"' : '' ?>>
        <?= $label ?><?php if (isset($counts[$key]) && !$isNew): ?> <small>(<?= $counts[$key] ?>)</small><?php endif; ?>
      </a>
    <?php endif; ?>
  <?php endforeach; ?>
</nav>

<?php if (!empty($errors)): ?>
  <div class="admin-errors" role="alert">
    <p><strong>Le formulaire contient <?= count($errors) > 1 ? count($errors) . ' erreurs' : 'une erreur' ?> :</strong></p>
    <ul>
      <?php foreach ($errors as $error): ?><li><?= h($error) ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<?php if ($tab === 'infos'): ?>
<!-- ================= INFORMATIONS ================= -->
<form class="admin-card admin-form" method="post" enctype="multipart/form-data"
      action="?pg=admin&section=recettes&action=<?= $isNew ? 'new' : 'edit&id=' . $id ?>">
  <?= $csrf ?>
  <input type="hidden" name="form_type" value="infos">

  <div class="admin-form-grid">
    <div class="admin-field admin-span-2">
      <label for="title">Titre <span aria-hidden="true">*</span></label>
      <input id="title" name="title" type="text" required minlength="3" maxlength="120" value="<?= h($form['title']) ?>" placeholder="La Praline Orangette">
    </div>
    <div class="admin-field admin-span-2">
      <label for="slug">Adresse de la page (slug)</label>
      <input id="slug" name="slug" type="text" maxlength="170" pattern="[a-z0-9]+(-[a-z0-9]+)*" value="<?= h($form['slug']) ?>" placeholder="la-praline-orangette" aria-describedby="slug-help">
      <p class="admin-help" id="slug-help">Laissez vide pour la créer à partir du titre. Minuscules, chiffres et tirets.</p>
    </div>
    <div class="admin-field admin-span-2">
      <label for="description">Description <span aria-hidden="true">*</span></label>
      <textarea id="description" name="description" rows="4" required><?= h($form['description']) ?></textarea>
    </div>
    <div class="admin-field">
      <label for="prep">Préparation (min) <span aria-hidden="true">*</span></label>
      <input id="prep" name="prep_time_minutes" type="number" min="0" max="10000" required value="<?= h($form['prep_time_minutes']) ?>">
    </div>
    <div class="admin-field">
      <label for="cook">Cuisson / repos (min) <span aria-hidden="true">*</span></label>
      <input id="cook" name="cook_time_minutes" type="number" min="0" max="10000" required value="<?= h($form['cook_time_minutes']) ?>">
    </div>
    <div class="admin-field">
      <label for="portions">Portions <span aria-hidden="true">*</span></label>
      <input id="portions" name="portions" type="number" min="1" max="100" required value="<?= h($form['portions']) ?>">
    </div>
    <fieldset class="admin-field">
      <legend>Difficulté <span aria-hidden="true">*</span></legend>
      <div class="admin-segmented">
        <?php foreach ($difficulties as $value => $label): ?>
          <label><input type="radio" name="difficulty" value="<?= $value ?>" <?= $form['difficulty'] === $value ? 'checked' : '' ?> required><span><?= $label ?></span></label>
        <?php endforeach; ?>
      </div>
    </fieldset>
  </div>

  <h2 class="admin-form-title">Photos</h2>
  <p class="admin-help">JPG, PNG ou WebP, 3 Mo maximum. Idéalement un PNG carré sur fond transparent.</p>
  <div class="admin-uploads">
    <?php foreach ([
        'image_entiere' => ['Praline entière', 'main-recipe-img', 'En haut de la page de la recette.', $isNew],
        'image_coupee' => ['Praline coupée', 'cards', 'Sur la carte de la liste des recettes. Sans photo, la praline entière est reprise.', false],
    ] as $field => [$label, $folder, $help, $required]):
        $current = $form['main_image'] !== '' ? 'images/' . $folder . '/' . $form['main_image'] : ''; ?>
      <div class="admin-upload" data-preview>
        <div class="admin-upload-img">
          <?php if ($current): ?>
            <img src="<?= h($current) ?>" alt="<?= $label ?> actuelle">
          <?php else: ?>
            <span>Aucune photo</span>
          <?php endif; ?>
        </div>
        <div>
          <label for="<?= $field ?>"><?= $label ?><?= $required ? ' <span aria-hidden="true">*</span>' : '' ?></label>
          <p class="admin-help"><?= $help ?></p>
          <input id="<?= $field ?>" name="<?= $field ?>" type="file" accept="<?= $accept ?>" <?= $required ? 'required' : '' ?>>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="admin-form-foot">
    <a class="admin-btn admin-btn--ghost" href="?pg=admin&section=recettes">Annuler</a>
    <button class="admin-btn" type="submit"><?= $isNew ? 'Créer la recette' : 'Enregistrer' ?></button>
  </div>
</form>

<?php elseif ($tab === 'ingredients'): ?>
<!-- ================= INGRÉDIENTS ================= -->
<form class="admin-card admin-form" method="post" action="<?= $editBase ?>&tab=ingredients">
  <?= $csrf ?>
  <input type="hidden" name="form_type" value="ingredients">
  <p class="admin-help">Laissez la quantité vide pour « selon le goût ». L'unité est libre : g, cl, pièce, c. à soupe…</p>

  <div class="admin-rows" id="ingredient-rows">
    <?php foreach ($ingredientRows ?: [['ingredient_id' => 0, 'quantity' => '', 'unit' => '']] as $row):
        // 200.00 → 200 ; 0.50 → 0.5
        $quantity = (string) ($row['quantity'] ?? '');
        if (str_contains($quantity, '.') && is_numeric($quantity)) {
            $quantity = rtrim(rtrim($quantity, '0'), '.');
        } ?>
      <div class="admin-row admin-row--ingredient">
        <div class="admin-field">
          <label>Ingrédient</label>
          <select name="ingredient_id[]">
            <option value="">— Choisir —</option>
            <?php foreach ($allIngredients as $ingredient): ?>
              <option value="<?= (int) $ingredient['id'] ?>" <?= (int) $row['ingredient_id'] === (int) $ingredient['id'] ? 'selected' : '' ?>><?= h($ingredient['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="admin-field">
          <label>Quantité</label>
          <input name="quantity[]" type="text" inputmode="decimal" value="<?= h($quantity) ?>" placeholder="200">
        </div>
        <div class="admin-field">
          <label>Unité</label>
          <input name="unit[]" type="text" maxlength="40" value="<?= h($row['unit'] ?? '') ?>" placeholder="g">
        </div>
        <button type="button" class="admin-icon-btn admin-icon-btn--danger" data-remove-row title="Retirer" aria-label="Retirer cet ingrédient">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
        </button>
      </div>
    <?php endforeach; ?>
  </div>
  <button type="button" class="admin-btn admin-btn--ghost admin-add" data-add-row="ingredient-rows">+ Ajouter un ingrédient</button>

  <div class="admin-form-foot">
    <button class="admin-btn" type="submit">Enregistrer les ingrédients</button>
  </div>
</form>

<details class="admin-card admin-form admin-details"<?= $formType === 'ingredient_create' ? ' open' : '' ?>>
  <summary>L'ingrédient n'est pas dans la liste ?</summary>
  <form method="post" enctype="multipart/form-data" action="<?= $editBase ?>&tab=ingredients">
    <?= $csrf ?>
    <input type="hidden" name="form_type" value="ingredient_create">
    <p class="admin-help">Il sera ajouté au catalogue commun à toutes les recettes. Pensez à enregistrer vos changements ci-dessus avant.</p>
    <div class="admin-form-grid">
      <div class="admin-field">
        <label for="new_ingredient_name">Nom <span aria-hidden="true">*</span></label>
        <input id="new_ingredient_name" name="new_ingredient_name" type="text" required minlength="3" maxlength="120" value="<?= h($formType === 'ingredient_create' ? ($_POST['new_ingredient_name'] ?? '') : '') ?>" placeholder="Chocolat noir 70 %">
      </div>
      <div class="admin-field">
        <label for="new_ingredient_image">Photo <span aria-hidden="true">*</span></label>
        <input id="new_ingredient_image" name="new_ingredient_image" type="file" accept="<?= $accept ?>" required>
      </div>
    </div>
    <div class="admin-form-foot">
      <button class="admin-btn admin-btn--ghost" type="submit">Ajouter au catalogue</button>
    </div>
  </form>
</details>

<?php else: ?>
<!-- ================= ÉTAPES ================= -->
<form class="admin-card admin-form" method="post" enctype="multipart/form-data" action="<?= $editBase ?>&tab=etapes">
  <?= $csrf ?>
  <input type="hidden" name="form_type" value="etapes">
  <p class="admin-help">Les étapes sont numérotées dans l'ordre de la liste. Chaque étape a besoin d'une photo (JPG, PNG ou WebP, 3 Mo maximum).</p>

  <ol class="admin-rows admin-steps" id="step-rows">
    <?php foreach ($stepRows ?: [['title' => '', 'description' => '', 'image' => '']] as $step): ?>
      <li class="admin-row admin-row--step">
        <div class="admin-step-num" aria-hidden="true"></div>
        <div class="admin-step-body">
          <div class="admin-field">
            <label>Titre de l'étape</label>
            <input name="step_title[]" type="text" maxlength="120" value="<?= h($step['title']) ?>" placeholder="Tempérer le chocolat">
          </div>
          <div class="admin-field">
            <label>Description</label>
            <textarea name="step_description[]" rows="3"><?= h($step['description']) ?></textarea>
          </div>
          <div class="admin-upload admin-upload--small" data-preview>
            <div class="admin-upload-img">
              <?php if ($step['image'] !== ''): ?>
                <img src="images/steps/<?= h($step['image']) ?>" alt="Photo actuelle de l'étape">
              <?php else: ?>
                <span>Aucune photo</span>
              <?php endif; ?>
            </div>
            <div class="admin-field">
              <label><?= $step['image'] !== '' ? 'Remplacer la photo' : 'Photo' ?></label>
              <input type="hidden" name="step_image_old[]" value="<?= h($step['image']) ?>">
              <input name="step_image[]" type="file" accept="<?= $accept ?>">
            </div>
          </div>
        </div>
        <div class="admin-step-tools">
          <button type="button" class="admin-icon-btn" data-move="-1" title="Monter" aria-label="Monter cette étape">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m18 15-6-6-6 6"/></svg>
          </button>
          <button type="button" class="admin-icon-btn" data-move="1" title="Descendre" aria-label="Descendre cette étape">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
          </button>
          <button type="button" class="admin-icon-btn admin-icon-btn--danger" data-remove-row title="Retirer" aria-label="Retirer cette étape">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
          </button>
        </div>
      </li>
    <?php endforeach; ?>
  </ol>
  <button type="button" class="admin-btn admin-btn--ghost admin-add" data-add-row="step-rows">+ Ajouter une étape</button>

  <div class="admin-form-foot">
    <button class="admin-btn" type="submit">Enregistrer les étapes</button>
  </div>
</form>
<?php endif; ?>

<?php require __DIR__ . '/inc/bottom.php'; ?>
