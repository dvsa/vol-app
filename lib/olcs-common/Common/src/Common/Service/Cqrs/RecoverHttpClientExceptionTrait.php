<?php

namespace Common\Service\Cqrs;

/**
 * Recover HttpClientExceptionTrait Trait
 *
 */
trait RecoverHttpClientExceptionTrait
{
    /** @var bool */
    protected $recoverHttpClientException = false;

    /**
     * Set RecoverHttpClientException
     *
     * @param bool $value value
     */
    public function setRecoverHttpClientException($value): void
    {
        $this->recoverHttpClientException = $value;
    }

    /**
     * get RecoverHttpClientException
     *
     * @return bool
     */
    public function getRecoverHttpClientException()
    {
        return $this->recoverHttpClientException;
    }
}
