<?php

declare(strict_types=1);

namespace App\Domain\User;

use JsonSerializable;

class User implements JsonSerializable
{
    public int $id;
    public string $username;

    public function __construct(int $id, string $username)
    {
        $this->id = $id;
        $this->username = $username;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize(): array
    {
        return [
            '_id' => (string) $this->id,
            'username' => $this->username
        ];
    }
}
