<?php
// model/service/ImageUpload.php
// Enregistre une image envoyée par formulaire dans public/images/<dossier>/
// - vérifie le vrai type du fichier (pas son extension) et sa taille
// - réencode l'image avec GD (ce qui la « nettoie » de tout contenu caché)
// - génère un nom unique côté serveur : <base>-<6 caractères>.<ext>
declare(strict_types=1);
namespace model\service;

use RuntimeException;

class ImageUpload
{
    public const MAX_BYTES = 3 * 1024 * 1024;
    private const ALLOWED = ['image/jpeg', 'image/png', 'image/webp'];

    // un fichier a-t-il été choisi dans ce champ ?
    public static function wasSent(?array $file): bool
    {
        return is_array($file) && ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
    }

    /**
     * @param array  $file   une entrée de $_FILES
     * @param string $folder sous-dossier de public/images (cards, main-recipe-img, steps, ingridients)
     * @param string $base   début du nom de fichier (souvent le slug)
     * @param string $format 'png' (garde la transparence) ou 'jpg'
     * @return string le nom du fichier créé
     */
    // nom unique à générer avant l'enregistrement (utile pour donner le même nom dans deux dossiers)
    public static function newName(string $base, string $format = 'png'): string
    {
        return self::cleanBase($base) . '-' . bin2hex(random_bytes(3)) . '.' . ($format === 'jpg' ? 'jpg' : 'png');
    }

    public static function save(array $file, string $folder, string $base, string $format = 'png', ?string $name = null): string
    {
        $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
            throw new RuntimeException("L'image est trop lourde (3 Mo maximum).");
        }
        if ($error !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'] ?? '')) {
            throw new RuntimeException("L'image n'a pas pu être envoyée, veuillez réessayer.");
        }
        if (($file['size'] ?? 0) > self::MAX_BYTES) {
            throw new RuntimeException("L'image est trop lourde (3 Mo maximum).");
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!in_array($mime, self::ALLOWED, true)) {
            throw new RuntimeException("Format d'image non accepté : utilisez JPG, PNG ou WebP.");
        }
        if (!function_exists('imagecreatefromstring')) {
            throw new RuntimeException("L'extension PHP GD est nécessaire pour enregistrer les images.");
        }

        $image = @imagecreatefromstring((string) file_get_contents($file['tmp_name']));
        if ($image === false) {
            throw new RuntimeException("Ce fichier n'est pas une image valide.");
        }
        if (imagesx($image) > 5000 || imagesy($image) > 5000) {
            imagedestroy($image);
            throw new RuntimeException("L'image est trop grande (5000 px maximum).");
        }

        $dir = self::dir($folder);
        $ext = $format === 'jpg' ? 'jpg' : 'png';
        if ($name === null || preg_match('/^[a-z0-9-]+-[0-9a-f]{6}\.' . $ext . '$/', $name) !== 1) {
            $name = self::newName($base, $ext);
        }
        $path = $dir . '/' . $name;

        if ($ext === 'png') {
            imagealphablending($image, false);
            imagesavealpha($image, true);
            $ok = imagepng($image, $path, 6);
        } else {
            // fond blanc sous une éventuelle transparence
            $canvas = imagecreatetruecolor(imagesx($image), imagesy($image));
            imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
            imagecopy($canvas, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
            $ok = imagejpeg($canvas, $path, 85);
            imagedestroy($canvas);
        }
        imagedestroy($image);

        if (!$ok) {
            throw new RuntimeException("L'image n'a pas pu être enregistrée sur le serveur.");
        }
        return $name;
    }

    // copie une image existante d'un dossier vers un autre nom (ex. même nom dans cards/ et main-recipe-img/)
    public static function copy(string $fromFolder, string $fromName, string $toFolder, string $toName): bool
    {
        $from = self::dir($fromFolder) . '/' . basename($fromName);
        return $fromName !== '' && is_file($from) && copy($from, self::dir($toFolder) . '/' . basename($toName));
    }

    // supprime uniquement les fichiers créés par l'admin (nom généré),
    // jamais les images d'origine du dépôt (orange.png, ganache.jpg…)
    public static function deleteGenerated(string $folder, string $name): void
    {
        $name = basename(trim($name));
        if (preg_match('/-[0-9a-f]{6}\.(png|jpg)$/', $name) === 1) {
            $path = self::dir($folder) . '/' . $name;
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    private static function dir(string $folder): string
    {
        $allowed = ['cards', 'main-recipe-img', 'steps', 'ingridients'];
        if (!in_array($folder, $allowed, true)) {
            throw new RuntimeException("Dossier d'images inconnu.");
        }
        $dir = RACINE_PATH . '/public/images/' . $folder;
        if (!is_dir($dir) || !is_writable($dir)) {
            throw new RuntimeException("Le dossier images/$folder n'est pas accessible en écriture.");
        }
        return $dir;
    }

    private static function cleanBase(string $base): string
    {
        $base = strtolower(trim($base));
        $base = preg_replace('/[^a-z0-9]+/', '-', $base) ?? '';
        $base = trim($base, '-');
        return $base === '' ? 'image' : substr($base, 0, 60);
    }
}
