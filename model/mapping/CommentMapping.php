<?php
// model/mapping/CommentMapping.php

declare(strict_types=1);
namespace model\mapping;
use model\abstract\AbstractMapping;

class CommentMapping extends AbstractMapping
{
    private ?int $id = null;
    private ?int $author_id = null;
    private ?int $recipe_id = null;
    private string $message = '';
    private ?string $created_at = null;
    private string $status = 'published';

    // champs venant des jointures (users, ratings)
    private string $username = '';
    private ?int $rating = null;

    public function getId(): ?int
    {
        return $this->id;
    }
    public function setId(int|string|null $id): void
    {
        $this->id = $id === null ? null : (int) $id;
    }

    public function getAuthorId(): ?int
    {
        return $this->author_id;
    }
    public function setAuthorId(int|string $author_id): void
    {
        $this->author_id = (int) $author_id;
    }

    public function getRecipeId(): ?int
    {
        return $this->recipe_id;
    }
    public function setRecipeId(int|string $recipe_id): void
    {
        $this->recipe_id = (int) $recipe_id;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
    public function setMessage(string $message): void
    {
        $this->message = trim($message);
    }

    public function getCreatedAt(): ?string
    {
        return $this->created_at;
    }
    public function setCreatedAt(?string $created_at): void
    {
        $this->created_at = $created_at;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getUsername(): string
    {
        return $this->username;
    }
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }
    public function setRating(int|string|null $rating): void
    {
        $this->rating = $rating === null ? null : (int) $rating;
    }

    // date affichée sur la carte, ex. "8 octobre 2026"
    public function formatDate(): string
    {
        if ($this->created_at === null) {
            return '';
        }
        $months = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet',
            'août', 'septembre', 'octobre', 'novembre', 'décembre'];
        $time = strtotime($this->created_at);
        if ($time === false) {
            return '';
        }
        return date('j', $time) . ' ' . $months[(int) date('n', $time) - 1] . ' ' . date('Y', $time);
    }
}
