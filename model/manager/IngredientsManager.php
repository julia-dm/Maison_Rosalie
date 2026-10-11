<?php
//model/manager/IngredientsManager.php
declare(strict_types=1);
namespace model\manager;
use PDO;
use model\abstract\AbstractManager;
use model\mapping\IngredientsMapping;

class IngredientsManager extends AbstractManager{

   
    public function getIngredientsByRecipeId(int $id): array
    {
        $sql = "SELECT i.*, ri.*
                FROM recipe_ingredients AS ri
                JOIN ingredients AS i ON i.id = ri.ingredient_id
                WHERE ri.recipe_id = :id";
    
        $stmt = $this->connect->prepare($sql);
        $stmt->execute(['id' => $id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $ingredients = [];
        foreach ($rows as $row) {
            $ingredients[] = new IngredientsMapping($row);
        }

        return $ingredients;
    }

    // ================= ADMINISTRATION =================

    public function getAllIngredients(): array
    {
        return $this->connect->query("SELECT id, name, img_ingredient FROM ingredients ORDER BY name")
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    // lignes recipe_ingredients d'une recette, pour le formulaire admin
    public function getRecipeIngredientRows(int $recipeId): array
    {
        $sql = "SELECT ri.ingredient_id, ri.quantity, ri.unit, i.name, i.img_ingredient
                FROM recipe_ingredients AS ri
                JOIN ingredients AS i ON i.id = ri.ingredient_id
                WHERE ri.recipe_id = :id
                ORDER BY i.name";
        $stmt = $this->connect->prepare($sql);
        $stmt->execute(['id' => $recipeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // remplace tous les ingrédients d'une recette (dans une transaction)
    public function replaceRecipeIngredients(int $recipeId, array $rows): void
    {
        $own = !$this->connect->inTransaction();
        if ($own) {
            $this->connect->beginTransaction();
        }
        try {
            $this->connect->prepare("DELETE FROM recipe_ingredients WHERE recipe_id = :id")
                ->execute(['id' => $recipeId]);
            $insert = $this->connect->prepare(
                "INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity, unit)
                 VALUES (:recipe, :ingredient, :quantity, :unit)"
            );
            foreach ($rows as $row) {
                $insert->execute([
                    'recipe' => $recipeId,
                    'ingredient' => $row['ingredient_id'],
                    'quantity' => $row['quantity'],
                    'unit' => $row['unit'],
                ]);
            }
            if ($own) {
                $this->connect->commit();
            }
        } catch (\Throwable $e) {
            if ($own) {
                $this->connect->rollBack();
            }
            throw $e;
        }
    }

    public function ingredientNameExists(string $name): bool
    {
        $stmt = $this->connect->prepare("SELECT COUNT(*) FROM ingredients WHERE name = :name");
        $stmt->execute(['name' => $name]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function createIngredient(string $name, string $image): int
    {
        $stmt = $this->connect->prepare("INSERT INTO ingredients (name, img_ingredient) VALUES (:name, :img)");
        $stmt->execute(['name' => $name, 'img' => $image]);
        return (int) $this->connect->lastInsertId();
    }
}
