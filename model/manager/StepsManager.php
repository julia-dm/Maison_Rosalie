<?php
//model/manager/RecipeManager.php
declare(strict_types=1);
namespace model\manager;
use PDO;
use model\abstract\AbstractManager;
use model\mapping\StepsMapping;

class StepsManager extends AbstractManager{

    public function getStepsById(int $id):array{
        $sql = "SELECT s.title, s.description, s.image, s.step_number FROM recipes AS r JOIN steps s ON s.recipe_id=r.id WHERE r.id=:id";
        $stmt = $this->connect->prepare($sql);
        $stmt->execute(['id' => $id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // var_dump($rows);
        $steps = [];
        foreach ($rows as $row) {
            $steps[] = new StepsMapping($row);
        } 
        // var_dump($steps);
    
        return $steps;
       
    }

    // ================= ADMINISTRATION =================

    public function getStepRows(int $recipeId): array
    {
        $stmt = $this->connect->prepare(
            "SELECT id, step_number, title, description, image FROM steps
             WHERE recipe_id = :id ORDER BY step_number"
        );
        $stmt->execute(['id' => $recipeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // remplace toutes les étapes, renumérotées 1, 2, 3… dans l'ordre reçu
    // (supprimer puis réinsérer évite le conflit sur l'index unique recipe_id + step_number)
    public function replaceSteps(int $recipeId, array $steps): void
    {
        $own = !$this->connect->inTransaction();
        if ($own) {
            $this->connect->beginTransaction();
        }
        try {
            $this->connect->prepare("DELETE FROM steps WHERE recipe_id = :id")->execute(['id' => $recipeId]);
            $insert = $this->connect->prepare(
                "INSERT INTO steps (recipe_id, step_number, title, description, image)
                 VALUES (:recipe, :num, :title, :description, :image)"
            );
            foreach (array_values($steps) as $i => $step) {
                $insert->execute([
                    'recipe' => $recipeId,
                    'num' => $i + 1,
                    'title' => $step['title'],
                    'description' => $step['description'],
                    'image' => $step['image'],
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
}
