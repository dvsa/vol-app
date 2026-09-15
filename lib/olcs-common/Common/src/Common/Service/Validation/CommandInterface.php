<?php

namespace Common\Service\Validation;

/**
 * Interface CommandInterface
 * @package Common\Service\Validation
 */
interface CommandInterface
{
    /**
     * @return array
     */
    public function getArrayCopy();

    /**
     * @return mixed
     */
    public function getValue();
}
