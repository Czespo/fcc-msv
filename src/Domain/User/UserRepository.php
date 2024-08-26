<?php

declare(strict_types=1);

namespace App\Domain\User;

use App\Domain\User\User;

interface UserRepository
{
    /**
     * @param string $username
     * @return User
     */
    public function add(string $username): ?User;

    /**
     * @return User[]
     */
    public function findAll(): array;

    /**
     * @param int $id
     * @return User
     * @throws UserNotFoundException
     */
    public function findUserOfId(int $id): User;
}
