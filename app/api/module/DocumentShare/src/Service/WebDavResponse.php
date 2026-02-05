<?php

namespace Dvsa\Olcs\DocumentShare\Service;

use Laminas\Http\Response;

/**
 * Class WebDavResponse
 *
 * @package Dvsa\Olcs\DocumentShare\Service
 */
class WebDavResponse extends Response
{
    /**
     * @var bool response
     */
    private $response;

    /**
     * @return bool
     */
    #[\Override]
    public function isSuccess(): bool
    {
        return $this->response;
    }

    public function setResponse(bool $response)
    {
        $this->response = $response;
        $this->setStatusCode($response);
    }

    /**
     * @param int $code
     *
     * @return void|Response
     */
    #[\Override]
    public function setStatusCode($code)
    {
        if ($code) {
            parent::setStatusCode(200);
        } else {
            parent::setStatusCode(500);
        }
    }
}
