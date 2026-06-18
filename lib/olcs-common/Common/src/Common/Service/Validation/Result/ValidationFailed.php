<?php

namespace Common\Service\Validation\Result;

use Common\Service\Validation\CommandInterface;

/**
 * Class ValidationFailed
 * @package Common\Service\Validation\Result
 */
class ValidationFailed extends Validation
{
    /**
     * @param $command
     * @param $messages
     * @param mixed[] $messages
     */
    public function __construct(CommandInterface $command, protected $messages)
    {
        parent::__construct($command);
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
