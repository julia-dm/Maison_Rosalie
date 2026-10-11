-- data/migration_admin.sql
-- À lancer une fois dans phpMyAdmin (base maison_rosalie sélectionnée, onglet SQL)
-- avant d'utiliser la page d'administration.

-- Boîte de réception de l'admin : message lu / non lu
ALTER TABLE `contact_messages`
  ADD COLUMN `is_read` TINYINT(1) NOT NULL DEFAULT 0 AFTER `message`;

-- (Facultatif) modération AVANT publication : les nouveaux avis attendent
-- la validation d'un admin au lieu d'apparaître directement sur la recette.
-- ALTER TABLE `comments`
--   MODIFY `status` ENUM('pending','published','hidden') NOT NULL DEFAULT 'pending';
