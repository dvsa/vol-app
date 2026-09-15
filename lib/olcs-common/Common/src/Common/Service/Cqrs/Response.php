<?php

namespace Common\Service\Cqrs;

use Laminas\Http\Response as HttpResponse;

/**
 * Response
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Response implements \Stringable
{
    /** @var  array */
    protected $result;

    /** @var HttpResponse */
    protected $httpResponse;

    /**
     * Response constructor.
     *
     * @param HttpResponse $httpResponse Native Http response
     */
    public function __construct(HttpResponse $httpResponse)
    {
        $this->httpResponse = $httpResponse;
    }

    /**
     * Is response has client error
     *
     * @return bool
     */
    public function isClientError()
    {
        return $this->httpResponse->isClientError();
    }

    /**
     * Access to resource is not permitted
     *
     * @return bool
     */
    public function isForbidden()
    {
        return $this->getStatusCode() === \Laminas\Http\Response::STATUS_CODE_403;
    }

    /**
     * Is not found
     *
     * @return bool
     */
    public function isNotFound()
    {
        return $this->httpResponse->isNotFound();
    }

    /**
     * Is Success
     *
     * @return bool
     */
    public function isOk()
    {
        return $this->httpResponse->isSuccess();
    }

    /**
     * Is response has server error
     *
     * @return bool
     */
    public function isServerError()
    {
        return $this->httpResponse->isServerError();
    }

    /**
     * Set result
     *
     * @param mixed $result Result
     *
     * @return $this
     */
    public function setResult(mixed $result)
    {
        $this->result = $result;
        return $this;
    }

    /**
     * Returns body of native response
     *
     * @return string
     */
    public function getBody()
    {
        return $this->httpResponse->getBody();
    }

    /**
     * Returns result
     *
     * @return array|mixed
     */
    public function getResult()
    {
        if ($this->result !== null) {
            return $this->result;
        }

        $body = $this->httpResponse->getBody();

        $this->result = json_decode($body, true);
        if ($this->result !== null) {
            return $this->result;
        }

        return $this->result = [];
    }

    /**
     * Get status code
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->httpResponse->getStatusCode();
    }

    /**
     * Return navite http response object
     *
     * @return HttpResponse
     */
    public function getHttpResponse()
    {
        return $this->httpResponse;
    }

    /**
     * For debugging, view a simple version of this this object
     *
     * @return string
     */
    #[\Override]
    public function __toString(): string
    {
        return sprintf(
            "Status = %s\nResponse = %s",
            $this->httpResponse->getStatusCode() . ' ' . $this->httpResponse->getReasonPhrase(),
            print_r($this->getResult(), true)
        );
    }
}
