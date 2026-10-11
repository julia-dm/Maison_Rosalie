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

    // ================= ADMINISTRATION =================

    public const STATUSES = ['pending', 'published', 'hidden'];

    // tous les avis (ou ceux d'un statut), avec auteur, recette et note
    public function getAllForAdmin(?string $status = null, int $limit = 200): array
    {
        $sql = "SELECT c.id, c.message, c.created_at, c.status, c.author_id, c.recipe_id,
                       u.username, r.title AS recipe_title, r.slug AS recipe_slug, rt.rating
                FROM comments AS c
                JOIN users AS u ON u.id = c.author_id
                JOIN recipes AS r ON r.id = c.recipe_id
                LEFT JOIN ratings AS rt ON rt.user_id = c.author_id AND rt.recipe_id = c.recipe_id";
        $params = [];
        if ($status !== null) {
            $sql .= " WHERE c.status = :status";
            $params['status'] = $status;
        }
        $sql .= " ORDER BY c.created_at DESC, c.id DESC LIMIT " . max(1, $limit);
        $stmt = $this->connect->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countByStatus(): array
    {
        $counts = ['all' => 0, 'pending' => 0, 'published' => 0, 'hidden' => 0];
        foreach ($this->connect->query("SELECT status, COUNT(*) AS n FROM comments GROUP BY status") as $row) {
            $counts[$row['status']] = (int) $row['n'];
            $counts['all'] += (int) $row['n'];
        }
        return $counts;
    }

    public function setStatus(int $id, string $status): bool
    {
        if (!in_array($status, self::STATUSES, true)) {
            return false;
        }
        return $this->connect->prepare("UPDATE comments SET status = :status WHERE id = :id")
            ->execute(['status' => $status, 'id' => $id]);
    }

    // supprime l'avis ; la note de l'auteur sur cette recette part aussi
    // s'il n'a plus aucun autre commentaire dessus
    public function deleteComment(int $id): bool
    {
        $stmt = $this->connect->prepare("SELECT author_id, recipe_id FROM comments WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return false;
        }
        $this->connect->beginTransaction();
        try {
            $this->connect->prepare("DELETE FROM comments WHERE id = :id")->execute(['id' => $id]);
            $left = $this->connect->prepare(
                "SELECT COUNT(*) FROM comments WHERE author_id = :a AND recipe_id = :r"
            );
            $left->execute(['a' => $row['author_id'], 'r' => $row['recipe_id']]);
            if ((int) $left->fetchColumn() === 0) {
                $this->connect->prepare("DELETE FROM ratings WHERE user_id = :a AND recipe_id = :r")
                    ->execute(['a' => $row['author_id'], 'r' => $row['recipe_id']]);
            }
            $this->connect->commit();
            return true;
        } catch (\Throwable $e) {
            $this->connect->rollBack();
            throw $e;
        }
    }
}
