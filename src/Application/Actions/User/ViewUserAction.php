<?php

declare(strict_types=1);

namespace App\Application\Actions\User;

use Psr\Http\Message\ResponseInterface as Response;

class ViewUserAction extends UserAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $user_id = (int) $this->resolveArg('id');
        $user = $this->userRepository->findUserOfId($user_id);
        if (!isset($user))
        {
            throw new UserNotFoundException();
        }

        $this->logger->info("User of id '$user_id' was viewed.");

        return $this->respondWithData($user);
    }
}
