<?php

namespace App\Services;

class HashService
{
    public function generateHashWithLength(int $length = 20)
    {
        return md5(random_bytes($length));
    }
}
