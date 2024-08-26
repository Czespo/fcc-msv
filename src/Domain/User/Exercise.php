<?php

declare(strict_types=1);

namespace App\Domain\User;

use JsonSerializable;

use App\Domain\User\User;

class Exercise implements JsonSerializable
{
    public int $exercise_id;
    public string $description;
    public int $duration;
    public string $date;

    public User $user;

    public function __construct(int $exercise_id, string $description, int $duration, string $date, User $user)
    {
        $this->exercise_id = $exercise_id;
        $this->description = $description;
        $this->duration = $duration;
        $this->date = $date;

        $this->user = $user;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize(): array
    {
        return [
            '_id' => (string) $this->user->id,
            'username' => $this->user->username,
            'description' => $this->description,
            'duration' => $this->duration,
            'date' => date_format(date_create($this->date), 'D M d Y')
        ];
    }
}
