<?php
// path: model/manager/UserManager.php
// typage strict
declare(strict_types=1);

namespace model\manager;
use PDO;
use model\interface\ManagerInterface;
use model\mapping\UserMapping;

class UserManager implements ManagerInterface
{
    protected PDO $connect;

    public function __construct(PDO $connect)
    {
        $this->connect = $connect;
    }
public function emailOrUsernameExists(string $email, string $username): bool{
$sql="SELECT email,username FROM users WHERE email=:email OR username=:username";
$stmt = $this->connect->prepare($sql);
$stmt->execute([
    'email' => $email,
    'username' => $username
]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row !== false;
}


public function createUser(UserMapping $user): bool
{
 $sql="INSERT INTO users (`username`, `email`,`password_hash`,`generated_key`)
    VALUES (:username, :email, :password_hash, :generated_key)";
            $stmt = $this->connect->prepare($sql);
            $stmt->bindValue(':username',$user->getUsername());
            $stmt->bindValue(':email',$user->getEmail());
            $stmt->bindValue(':password_hash',$user->getPasswordHash());
            $stmt->bindValue(':generated_key',$user->getGeneratedKey());
            return $stmt->execute();
}
public function getUserByEmail(string $email): array|false
{
    $sql = "SELECT id, username, email, password_hash, role
            FROM users
            WHERE email = :email
            LIMIT 1";

    $stmt = $this->connect->prepare($sql);

    $stmt->execute([
        'email' => $email
    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// ================= ADMINISTRATION =================

public function countAll(): int
{
    return (int) $this->connect->query("SELECT COUNT(*) FROM users")->fetchColumn();
}

public function countAdmins(): int
{
    return (int) $this->connect->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
}

// liste admin : chaque compte avec son nombre d'avis et de recettes créées
public function getAllWithStats(): array
{
    $sql = "SELECT u.id, u.username, u.email, u.role, u.created_at,
                   (SELECT COUNT(*) FROM comments c WHERE c.author_id = u.id) AS comments_count,
                   (SELECT COUNT(*) FROM recipes r WHERE r.author_id = u.id) AS recipes_count
            FROM users AS u
            ORDER BY u.role = 'admin' DESC, u.created_at DESC";
    return $this->connect->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

public function getById(int $id): ?array
{
    $stmt = $this->connect->prepare(
        "SELECT id, username, email, role,
                (SELECT COUNT(*) FROM recipes r WHERE r.author_id = users.id) AS recipes_count
         FROM users WHERE id = :id"
    );
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row === false ? null : $row;
}

public function setRole(int $id, string $role): bool
{
    if (!in_array($role, ['user', 'admin'], true)) {
        return false;
    }
    return $this->connect->prepare("UPDATE users SET role = :role WHERE id = :id")
        ->execute(['role' => $role, 'id' => $id]);
}

// avis et notes partent avec (ON DELETE CASCADE) ; un auteur de recettes est refusé par la base
public function deleteUser(int $id): bool
{
    return $this->connect->prepare("DELETE FROM users WHERE id = :id")->execute(['id' => $id]);
}
}
