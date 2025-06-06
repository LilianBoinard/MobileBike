<?php

namespace MobileBike\App\Model\User;

class User
{
    public int $id_user;
    public string $username;
    public string $password;
    public string $email;
    public string $created;
    public string $profileImage;

    public function __set($name, $value)
    {
        if ($name == 'profile_image') {
            $this->profileImage = $value;
        }
    }
}