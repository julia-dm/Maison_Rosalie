<?php
// model/mapping/ContactMapping.php
declare(strict_types=1);
namespace model\mapping;
use model\abstract\AbstractMapping;

class ContactMapping extends AbstractMapping
{
    private ?int $id = null;
    private string $name = '';
    private string $email = '';
    private string $subject = '';
    private string $message = '';
    private ?string $created_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }
    public function setId(int|string|null $id): void
    {
        $this->id = $id === null ? null : (int) $id;
    }

    public function getName(): string
    {
        return $this->name;
    }
    public function setName(string $name): void
    {
        $this->name = trim($name);
    }

    public function getEmail(): string
    {
        return $this->email;
    }
    public function setEmail(string $email): void
    {
        $this->email = trim($email);
    }

    public function getSubject(): string
    {
        return $this->subject;
    }
    public function setSubject(string $subject): void
    {
        $this->subject = trim($subject);
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
}
