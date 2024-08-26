<?php

declare(strict_types=1);

namespace App\Application\Actions\User;

use App\Application\Actions\Action;
use App\Domain\User\UserRepository;
use App\Domain\User\ExerciseRepository;
use Psr\Log\LoggerInterface;

abstract class UserAction extends Action
{
    protected UserRepository $userRepository;
    protected ExerciseRepository $exerciseRepository;

    public function __construct(LoggerInterface $logger, UserRepository $userRepository, ExerciseRepository $exerciseRepository)
    {
        parent::__construct($logger);
        $this->userRepository = $userRepository;
        $this->exerciseRepository = $exerciseRepository;
    }
}
