<?php

declare(strict_types=1);

namespace App\Application\Actions;

use JsonSerializable;

class ActionPayload implements JsonSerializable
{
    private int $statusCode;

    /**
     * @var array|object|null
     */
    private $data;

    private ?ActionError $error;

    private $bare;

    public function __construct(int $statusCode = 200, $data = null, ?ActionError $error = null, bool $bare = true)
    {
        $this->statusCode = $statusCode;
        $this->data = $data;
        $this->error = $error;
        $this->bare = $bare;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return array|null|object
     */
    public function getData()
    {
        return $this->data;
    }

    public function getError(): ?ActionError
    {
        return $this->error;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize(): array|object
    {
        if ($this->error === null && $this->bare)
        {
            $payload = $this->data;
        }
        else
        {
            $payload = [
                'statusCode' => $this->statusCode,
            ];
    
            if ($this->data !== null)
            {
                $payload['data'] = $this->data;
            }
            else if ($this->error !== null)
            {
                $payload['error'] = $this->error;
            }
        }

        return $payload;
    }
}
