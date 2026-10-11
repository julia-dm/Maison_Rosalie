<?php
// model/manager/ContactManager.php
declare(strict_types=1);
namespace model\manager;
use model\abstract\AbstractManager;
use model\mapping\ContactMapping;

class ContactManager extends AbstractManager
{
    // enregistre le message du formulaire de contact dans contact_messages
    public function addMessage(ContactMapping $contact): bool
    {
        $sql = "INSERT INTO contact_messages (name, email, subject, message)
                VALUES (:name, :email, :subject, :message)";
        $stmt = $this->connect->prepare($sql);
        return $stmt->execute([
            'name' => $contact->getName(),
            'email' => $contact->getEmail(),
            'subject' => $contact->getSubject(),
            'message' => $contact->getMessage(),
        ]);
    }

    // ================= ADMINISTRATION =================

    public function getAll(): array
    {
        return $this->connect->query(
            "SELECT id, name, email, subject, message, created_at, is_read FROM contact_messages
             ORDER BY created_at DESC, id DESC"
        )->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->connect->prepare(
            "SELECT id, name, email, subject, message, created_at, is_read FROM contact_messages WHERE id = :id"
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    public function countUnread(): int
    {
        return (int) $this->connect->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0")->fetchColumn();
    }

    public function setRead(int $id, bool $read): bool
    {
        return $this->connect->prepare("UPDATE contact_messages SET is_read = :r WHERE id = :id")
            ->execute(['r' => $read ? 1 : 0, 'id' => $id]);
    }

    public function deleteMessage(int $id): bool
    {
        return $this->connect->prepare("DELETE FROM contact_messages WHERE id = :id")->execute(['id' => $id]);
    }
}
