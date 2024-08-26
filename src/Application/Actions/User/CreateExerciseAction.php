<?php

declare(strict_types=1);

namespace App\Application\Actions\User;

use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotImplementedException;

class CreateExerciseAction extends UserAction
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

        $body = $this->getFormData();
        if (!(isset($body['description']) && isset($body['duration'])))
        {
            throw new HttpBadRequestException($this->request, 'description and duration are required');
        }

        if (!isset($body['date']))
        {
            $date = date('Y-m-d');
        }
        else
        {
            $date = date_parse_from_format('Y-m-d', $body['date']);
            if ($date['warning_count'] || $date['error_count'])
            {
                throw new HttpBadRequestException($this->request, 'date is invalid (expected yyyy-mm-dd)');
            }
            
            $year = $date['year'];
            $month = $date['month'];
            $day = $date['day'];
            $date = "$year-$month-$day";
        }

        $exercise = $this->exerciseRepository->add($body['description'], (int) $body['duration'], $date, $user);
        if (!isset($exercise))
        {
            return new HttpInternalServerErrorException($this->request);
        }
        $this->logger->info("Exercise ('$exercise->description', $exercise->duration, '$exercise->date') was created.");

        return $this->respondWithData($exercise);
    }
}
