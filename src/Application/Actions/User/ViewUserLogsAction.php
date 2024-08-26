<?php

declare(strict_types=1);

namespace App\Application\Actions\User;

use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotImplementedException;

class ViewUserLogsAction extends UserAction
{
    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        // Get user if user id is valid.
        $user_id = (int) $this->resolveArg('id');
        $user = $this->userRepository->findUserOfId($user_id);
        if (!isset($user))
        {
            throw new UserNotFoundException();
        }

        // Get query params and validate dates.
        $params = $this->request->getQueryParams();

        $from_date = null;
        if(array_key_exists('from', $params))
        {
            $from_date = $params['from'];
            $test_date = date_parse_from_format('Y-m-d', $from_date);
            if ($test_date['warning_count'] || $test_date['error_count'])
            {
                throw new HttpBadRequestException($this->request, 'from date is invalid (expected yyyy-mm-dd)');
            }
        }

        $to_date = null;
        if(array_key_exists('to', $params))
        {
            $to_date = $params['to'];
            $test_date = date_parse_from_format('Y-m-d', $to_date);
            if ($test_date['warning_count'] || $test_date['error_count'])
            {
                throw new HttpBadRequestException($this->request, 'to date is invalid (expected yyyy-mm-dd)');
            }
        }

        $limit = array_key_exists('limit', $params) ? (int) $params['limit'] : 0;

        // Get the log.
        $log = $this->exerciseRepository->findAllById($user_id, $from_date, $to_date, $limit);

        // Format response.
        $response = [
            '_id' => (string) $user_id,
            'username' => $user->username
        ];

        if(isset($from_date)) $response['from'] = date_format(date_create($from_date), 'D M d Y');
        if(isset($to_date))   $response['to'] = date_format(date_create($to_date), 'D M d Y');

        $response['count'] = count($log);
        $response['log'] = $log;

        return $this->respondWithData($response);
    }
}
