<?php

declare(strict_types=1);

namespace App\Application\Actions\User;

use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpInternalServerErrorException;

class CreateUserAction extends UserAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $body = $this->getFormData();
        if (!isset($body['username']))
        {
            throw new HttpBadRequestException($this->request, 'username is required');
        }

        $user = $this->userRepository->add($body['username']);
        if ( !isset($user) )
        {
            return new HttpInternalServerErrorException($this->request);
        }
        $this->logger->info("User '$user->username' ($user->id) was created.");

        return $this->respondWithData($user);
    }
}
