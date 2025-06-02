<?php

namespace MobileBike\App\Model\User;

class User
{
  public int $id;
  public string $username;
  public string $password;
  public string $email;
  public \DateTime $created;
}
