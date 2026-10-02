<?php
//model/manager/UserManager.php
declare(strict_types=1);
namespace model\manager;
use model\abstract\AbstractManager;
use model\mapping\UserMapping;

class UserManager extends AbstractManager{

    // email ou username déjà pris
    public function emailOrUsernameExists(string $email, string $username): bool{
        // TODO
    }

    // INSERT users
    public function createUser(UserMapping $user): bool{
        // TODO
    }
}
