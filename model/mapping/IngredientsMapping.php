<?php
// model/mapping/IngredientsMapping.php

declare(strict_types=1);
namespace model\mapping;
use Exception;
use model\abstract\AbstractMapping;

class IngredientsMapping extends AbstractMapping
{
    private ?int $id = null;
    private string $name = '';
    private string $img_ingredient = '';
    // quantity est un DECIMAL(10,3) qui peut être NULL en base (ex. « sel : une pincée »)
    private ?float $quantity= null;
    private string $unit= "";
    // getters and setters
    public function getId(): ?int
    {
        return $this->id;
    }
    public function setId(?int $id): void
    {
        if($id<=0) throw new Exception("id doit être un entier positif");
        $this->id = $id;
    }
    public function getName(): string
    {
        return $this->name;
    }
    public function setName(string $title): void
    {
        $name = htmlspecialchars(strip_tags(trim($title)));
        if(strlen($name)<3 || strlen($name)>120){
            throw new Exception("Le titre de la recette doit faire entre 3 et 120 caractères");
        }
        $this->name = $name;
    }
        public function getImgIngredient(): string
{
    return $this->img_ingredient;
}

public function setImgIngredient (string $img_ingredient): void
{
    $this->img_ingredient = $img_ingredient;
}


public function getQuantity(): ?float
{
    return $this->quantity;
}

public function setQuantity(int|float|string|null $quantity): void
{
    $this->quantity = ($quantity === null || $quantity === '') ? null : (float) $quantity;
}

// quantité lisible : "200" au lieu de "200.000", "1,5" au lieu de "1.500", vide si NULL
public function formatQuantity(): string
{
    if ($this->quantity === null) {
        return '';
    }
    return rtrim(rtrim(number_format($this->quantity, 3, ',', ''), '0'), ',');
}

public function getUnit(): string
{
    return $this->unit;
}
public function setUnit(?string $unit): void
{
    $this->unit = trim($unit ?? '');
}

}
