<?php

declare(strict_types=1);

namespace Common\Service\Cqrs\Exception;

use Common\Exception\Exception;
use Common\Service\Cqrs\Response;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Throwable;

class BadQueryResponseException extends Exception
{
    /**
     * @var QueryInterface
     */
    protected $query;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @param Throwable|null $previous
     */
    public function __construct(string $message, QueryInterface $query, Response $response, Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->query = $query;
        $this->response = $response;
    }
}
