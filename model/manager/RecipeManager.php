<?php
//model/manager/RecipeManager.php
declare(strict_types=1);
namespace model\manager;
use PDO;
use model\abstract\AbstractManager;
use model\mapping\RecipeMapping;

class RecipeManager extends AbstractManager{

    public function getAllRecipes():array{
        $sql = "SELECT * FROM recipes";
        $stmt = $this->connect->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $recipes = [];
        foreach ($rows as $row) {
            $recipes[] = new RecipeMapping($row);
        }
    
        return $recipes;
    }

    // les meilleures recettes : moyenne des notes la plus haute d'abord,
    // puis le nombre de notes ; sans note, les plus récentes passent en dernier
    public function getTopRecipes(int $limit = 3): array
    {
        $sql = "SELECT r.*, s.average_rating, COALESCE(s.ratings_count, 0) AS ratings_count
                FROM recipes AS r
                LEFT JOIN (
                    SELECT recipe_id, ROUND(AVG(rating), 1) AS average_rating, COUNT(*) AS ratings_count
                    FROM ratings
                    GROUP BY recipe_id
                ) AS s ON s.recipe_id = r.id
                ORDER BY s.average_rating IS NULL, s.average_rating DESC, ratings_count DESC, r.created_at DESC
                LIMIT :limit";
        $stmt = $this->connect->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $recipes = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $recipes[] = new RecipeMapping($row);
        }
        return $recipes;
    }

    public function getRecipeById(int $id): ?RecipeMapping
    {
        $sql = 'SELECT * FROM recipes WHERE id = :id LIMIT 1';
    
        $query = $this->connect->prepare($sql);
        $query->execute(['id' => $id]);
    
        $row = $query->fetch();
    
        if ($row === false) {
            return null;
        }
    
        return new RecipeMapping($row);
    } 

    // Nous recherchons une recette via son slug
public function getRecipeBySlug(string $slug):?RecipeMapping{

    $sql = "SELECT *
            FROM recipes
            WHERE slug = ?";

    $stmt = $this->connect->prepare($sql);
    $stmt->execute([$slug]);
    $recipe = $stmt->fetch(PDO::FETCH_ASSOC);

    if (empty($recipe)) {
        return null;
    }
    return new RecipeMapping($recipe);
}

    // ================= ADMINISTRATION =================

    public function countAll(): int
    {
        return (int) $this->connect->query("SELECT COUNT(*) FROM recipes")->fetchColumn();
    }

    // liste admin : chaque recette avec sa note moyenne et ses compteurs
    public function getAllForAdmin(): array
    {
        $sql = "SELECT r.id, r.title, r.slug, r.main_image, r.cook_time_minutes, r.difficulty,
                       s.average_rating, COALESCE(s.ratings_count, 0) AS ratings_count,
                       (SELECT COUNT(*) FROM recipe_ingredients ri WHERE ri.recipe_id = r.id) AS ingredients_count,
                       (SELECT COUNT(*) FROM steps st WHERE st.recipe_id = r.id) AS steps_count
                FROM recipes AS r
                LEFT JOIN (
                    SELECT recipe_id, ROUND(AVG(rating), 1) AS average_rating, COUNT(*) AS ratings_count
                    FROM ratings GROUP BY recipe_id
                ) AS s ON s.recipe_id = r.id
                ORDER BY r.created_at DESC, r.id DESC";
        return $this->connect->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // données brutes pour pré-remplir le formulaire
    public function getRowById(int $id): ?array
    {
        $stmt = $this->connect->prepare("SELECT * FROM recipes WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    public function slugExists(string $slug, ?int $exceptId = null): bool
    {
        $stmt = $this->connect->prepare("SELECT COUNT(*) FROM recipes WHERE slug = :slug AND id <> :id");
        $stmt->execute(['slug' => $slug, 'id' => $exceptId ?? 0]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function createRecipe(array $d, int $authorId): int
    {
        $sql = "INSERT INTO recipes (title, slug, description, main_image, prep_time_minutes,
                                     cook_time_minutes, portions, difficulty, author_id)
                VALUES (:title, :slug, :description, :main_image, :prep, :cook, :portions, :difficulty, :author)";
        $stmt = $this->connect->prepare($sql);
        $stmt->execute([
            'title' => $d['title'], 'slug' => $d['slug'], 'description' => $d['description'],
            'main_image' => $d['main_image'], 'prep' => $d['prep_time_minutes'],
            'cook' => $d['cook_time_minutes'], 'portions' => $d['portions'],
            'difficulty' => $d['difficulty'], 'author' => $authorId,
        ]);
        return (int) $this->connect->lastInsertId();
    }

    public function updateRecipe(int $id, array $d): bool
    {
        $sql = "UPDATE recipes SET title = :title, slug = :slug, description = :description,
                       main_image = :main_image, prep_time_minutes = :prep, cook_time_minutes = :cook,
                       portions = :portions, difficulty = :difficulty
                WHERE id = :id";
        return $this->connect->prepare($sql)->execute([
            'title' => $d['title'], 'slug' => $d['slug'], 'description' => $d['description'],
            'main_image' => $d['main_image'], 'prep' => $d['prep_time_minutes'],
            'cook' => $d['cook_time_minutes'], 'portions' => $d['portions'],
            'difficulty' => $d['difficulty'], 'id' => $id,
        ]);
    }

    // ingrédients, étapes, avis et notes partent avec (ON DELETE CASCADE)
    public function deleteRecipe(int $id): bool
    {
        return $this->connect->prepare("DELETE FROM recipes WHERE id = :id")->execute(['id' => $id]);
    }
}
