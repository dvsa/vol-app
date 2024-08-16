<?php

namespace Dvsa\Olcs\Api\Domain\Exception;

/**
 * Nysiis Exception
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class NysiisException extends Exception
{
    protected $retryAfter = 900;

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
