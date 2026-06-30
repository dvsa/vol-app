<?php

declare(strict_types=1);

namespace Common\Service\Cqrs\Exception;

use Common\Exception\Exception;
use Throwable;
use Common\Service\Cqrs\Response;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

class BadCommandResponseException extends Exception
{
    /**
     * @var CommandInterface
     */
    protected $command;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @param Throwable|null $previous
     */
    public function __construct(string $message, CommandInterface $command, Response $response, Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->command = $command;
        $this->response = $response;
    }
}
