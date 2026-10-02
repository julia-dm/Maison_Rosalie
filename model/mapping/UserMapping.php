<?php
//model/mapping/UserMapping.php

declare(strict_types=1);
namespace model\mapping;
use model\abstract\AbstractMapping;

class UserMapping extends AbstractMapping
{
    private ?int $id = null;
    private string $username = '';
    private string $email = '';
    private string $passwordHash = '';
    private string $role = 'user';
    private ?string $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }
    public function setId(int|string $v): void
    {
        $this->id = (int) $v;
    }
    public function getUsername(): string
    {
        return $this->username;
    }
    public function setUsername(string $v): void
    {
        $this->username = $v;
    }
    public function getEmail(): string
    {
        return $this->email;
    }
    public function setEmail(string $v): void
    {
        $this->email = $v;
    }
    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }
    public function setPasswordHash(string $v): void
    {
        $this->passwordHash = $v;
    }
    public function getRole(): string
    {
        return $this->role;
    }
    public function setRole(string $v): void
    {
        $this->role = $v;
    }
    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }
    public function setCreatedAt(string $v): void
    {
        $this->createdAt = $v;
    }
}
