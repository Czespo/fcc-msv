<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use App\Infrastructure\Persistence\UrlShortener;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app)
{
    $app->options('/{routes:.*}', function (Request $request, Response $response)
    {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', function (Request $request, Response $response)
    {
        $response->getBody()->write('Hello world!');
        return $response;
    });

    $app->group('/users', function (Group $group)
    {
        $group->get('', ListUsersAction::class);
        $group->get('/{id}', ViewUserAction::class);
    });

    $app->get('/api/whoami', function (Request $request, Response $response)
    {
        $headers = [
            'ipaddress' => $request->getServerParams()['REMOTE_ADDR'],
            'language' => $request->getHeaderLine("Accept-Language"),
            'software' => $request->getHeaderLine("User-Agent")
        ];

        $response = $response->withHeader("Content-Type", "application/json");
        $response->getBody()->write(json_encode($headers, JSON_UNESCAPED_SLASHES));
        return $response;
    });

    $app->post('/api/shorturl', function (Request $request, Response $response)
    {
        $body = $request->getParsedBody();
        if (!empty($body['url']))
        {
            $response = $response->withHeader("Content-Type", "application/json");
            $urlshortener = new UrlShortener();
            $short = $urlshortener->add($body['url']);
            if (!empty($short))
            {
                $urls = [
                    'original_url' => $body['url'],
                    'short_url' => $short
                ];
    
                $response->getBody()->write(json_encode($urls, JSON_UNESCAPED_SLASHES));
            }
            else
            {
                $response->getBody()->write(json_encode(['error' => 'invalid url']));
                $response = $response->withStatus(400);
            }
        }
        else
        {
            $response = $response->withStatus(400);
        }

        return $response;
    });

    $app->get('/api/shorturl/{short:\d+}', function (Request $request, Response $response, array $args)
    {
        if (!empty($args['short']))
        {
            $urlshortener = new UrlShortener($_SERVER['SERVER_NAME']);
            $full = $urlshortener->get($args['short']);
            if (!empty($full))
            {
                $response = $response->withStatus(308)->withHeader('Location', $full);
            }
            else
            {
                $response = $response->withStatus(404);
            }
        }
        else
        {
            $response = $response->withStatus(400);
        }

        return $response;
    });
};
