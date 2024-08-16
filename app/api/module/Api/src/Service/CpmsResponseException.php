<?php

/**
 * Cpms Response Exception
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Service;

/**
 * Cpms Response Exception
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class CpmsResponseException extends Exception
{
    protected $response;

    /**
     * Gets the value of response.
     *
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Sets the value of response.
     *
     * @param mixed $response the response
     *
     * @return self
     */
    public function setResponse(mixed $response)
    {
        $this->response = $response;

        return $this;
    }
}
