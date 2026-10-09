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
}
