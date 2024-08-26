<?php

declare(strict_types=1);

use App\Domain\User\UserRepository;
use App\Infrastructure\Persistence\User\DatabaseUserRepository;
use App\Domain\User\ExerciseRepository;
use App\Infrastructure\Persistence\User\DatabaseExerciseRepository;
use DI\ContainerBuilder;

return function (ContainerBuilder $containerBuilder)
{
    // Here we map our interfaces to their implementation.
    $containerBuilder->addDefinitions(
    [
        UserRepository::class => \DI\autowire(DatabaseUserRepository::class),
        ExerciseRepository::class => \DI\autowire(DatabaseExerciseRepository::class)
    ]);
};
