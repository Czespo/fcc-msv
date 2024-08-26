<?php

declare(strict_types=1);

namespace App\Domain\User;

use App\Domain\User\Exercise;

interface ExerciseRepository
{
    /**
     * @param string $description
     * @param int $duration
     * @param string $date
     * @param int $user_id
     * @return Exercise
     */
    public function add(string $description, int $duration, string $date, User $user): ?Exercise;

    /**
     * @return Exercise[]
     */
    public function findAll(): array;

    /**
     * @param int $user_id
     * @return array
     */
    public function findAllById(int $user_id): array;
}
