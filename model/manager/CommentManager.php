<?php
// model/manager/CommentManager.php
declare(strict_types=1);
namespace model\manager;
use PDO;
use model\abstract\AbstractManager;
use model\mapping\CommentMapping;

class CommentManager extends AbstractManager
{
    // commentaires publiés d'une recette, avec le nom de l'auteur et sa note
    public function getPublishedByRecipeId(int $recipeId): array
    {
        $sql = "SELECT c.id, c.author_id, c.recipe_id, c.message, c.created_at, c.status,
                       u.username, r.rating
                FROM comments AS c
                JOIN users AS u ON u.id = c.author_id
                LEFT JOIN ratings AS r ON r.user_id = c.author_id AND r.recipe_id = c.recipe_id
                WHERE c.recipe_id = :recipe_id AND c.status = 'published'
                ORDER BY c.created_at DESC, c.id DESC";
        $stmt = $this->connect->prepare($sql);
        $stmt->execute(['recipe_id' => $recipeId]);

        $comments = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $comments[] = new CommentMapping($row);
        }
        return $comments;
    }

    public function addComment(CommentMapping $comment): bool
    {
        $sql = "INSERT INTO comments (author_id, recipe_id, message)
                VALUES (:author_id, :recipe_id, :message)";
        $stmt = $this->connect->prepare($sql);
        return $stmt->execute([
            'author_id' => $comment->getAuthorId(),
            'recipe_id' => $comment->getRecipeId(),
            'message' => $comment->getMessage(),
        ]);
    }
}
