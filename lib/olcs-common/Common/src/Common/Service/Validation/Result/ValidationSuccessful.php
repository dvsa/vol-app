<?php

namespace Common\Service\Validation\Result;

use Common\Service\Validation\CommandInterface;

/**
 * Class ValidationSuccessful
 * @package Common\Service\Validation\Result
 */
class ValidationSuccessful extends Validation
{
    /**
     * @param $command
     * @param $result
     * @param $context
     * @param mixed[] $result
     * @param mixed[] $context
     */
    public function __construct(CommandInterface $command, protected $result, protected $context = [])
    {
        parent::__construct($command);
    }

    /**
     * @return array
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }
}
