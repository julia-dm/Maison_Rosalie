<?php
// model/manager/RatingManager.php
declare(strict_types=1);
namespace model\manager;
use PDO;
use model\abstract\AbstractManager;

class RatingManager extends AbstractManager
{
    // une seule note par utilisateur et par recette : si elle existe déjà, on la met à jour
    public function saveRating(int $userId, int $recipeId, int $rating): bool
    {
        $sql = "INSERT INTO ratings (user_id, recipe_id, rating)
                VALUES (:user_id, :recipe_id, :rating)
                ON DUPLICATE KEY UPDATE rating = :new_rating";
        $stmt = $this->connect->prepare($sql);
        return $stmt->execute([
            'user_id' => $userId,
            'recipe_id' => $recipeId,
            'rating' => $rating,
            'new_rating' => $rating,
        ]);
    }

    // moyenne (arrondie à 0,1) et nombre de notes d'une recette
    public function getSummary(int $recipeId): array
    {
        $sql = "SELECT ROUND(AVG(rating), 1) AS average, COUNT(*) AS total
                FROM ratings
                WHERE recipe_id = :recipe_id";
        $stmt = $this->connect->prepare($sql);
        $stmt->execute(['recipe_id' => $recipeId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'average' => $row['average'] === null ? 0.0 : (float) $row['average'],
            'total' => (int) $row['total'],
        ];
    }
}
