<?php

/**
 * Not Ready Exception
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Domain\Exception;

/**
 * Not Ready Exception
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class NotReadyException extends Exception
{
    protected $retryAfter;

    /**
     * Gets the value of retryAfter.
     *
     * @return mixed
     */
    public function getRetryAfter()
    {
        return $this->retryAfter;
    }

    /**
     * Sets the value of retryAfter.
     *
     * @param mixed $retryAfter the retry after
     *
     * @return self
     */
    public function setRetryAfter(mixed $retryAfter)
    {
        $this->retryAfter = $retryAfter;

        return $this;
    }
}
