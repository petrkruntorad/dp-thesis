<?php

namespace App\Services;

use Symfony\Component\Uid\Uuid;

class UserService
{
    function generatePassword(): string
    {
        //return unique string
        return Uuid::v4()->toBase58();
    }
}
