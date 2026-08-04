<?php

namespace Common\Util;

use Laminas\Http\Response;

/**
 * Response Helper
 *
 * Handle responses from the rest service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ResponseHelper
{
    public $body;
    public $response;

    public $method;

    protected $responseData;

    private $data;

    private $expectedCodes = [
        'GET' => [
            Response::STATUS_CODE_200,
            Response::STATUS_CODE_404
        ],
        'POST' => [
            Response::STATUS_CODE_201,
            Response::STATUS_CODE_202,
            Response::STATUS_CODE_400
        ],
        'PUT' => [
            Response::STATUS_CODE_200,
            Response::STATUS_CODE_400,
            Response::STATUS_CODE_404,
            Response::STATUS_CODE_409
        ],
        'PATCH' => [
            Response::STATUS_CODE_200,
            Response::STATUS_CODE_400,
            Response::STATUS_CODE_404,
            Response::STATUS_CODE_409
        ],
        'DELETE' => [
            Response::STATUS_CODE_200,
            Response::STATUS_CODE_404
        ]
    ];

    public function setResponse(Response $response): void
    {
        $this->response = $response;
    }

    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @psalm-param 'POST'|'blah' $method
     */
    public function setMethod(string $method): void
    {
        $this->method = $method;
    }

    public function setParams(array $params): void
    {
    }

    public function getData()
    {
        return $this->data;
    }

    /**
     * @return bool|null
     */
    public function handleResponse()
    {
        $this->body = $this->response->getBody();

        $this->checkForValidResponseBody($this->body);

        $this->checkForInternalServerError($this->body);

        $this->checkForUnexpectedResponseCode($this->body);

        return $this->processResponse();
    }

    public function checkForValidResponseBody($body): void
    {
        if (!is_string($body)) {
            throw new \Exception('Invalid response body, expected string' . $body);
        }

        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid response body, expected json: ' . $body);
        }

        $this->responseData = ($data['Response'] ?? $data);
    }

    public function checkForInternalServerError($body): void
    {
        if ($this->response->getStatusCode() == Response::STATUS_CODE_500) {
            $data = json_decode($body, true);

            if (json_last_error() == JSON_ERROR_NONE) {
                $body = "\n" . print_r($data, true);
            }

            throw new \Exception('Internal server error: ' . $body);
        }
    }

    public function checkForUnexpectedResponseCode($body): void
    {
        if (!in_array($this->response->getStatusCode(), $this->expectedCodes[$this->method])) {
            $data = json_decode($body, true);

            if (json_last_error() == JSON_ERROR_NONE) {
                $body = "\n" . print_r($data, true);
            }

            // TO-DO: Replace with a different exception
            throw new \Exception('Unexpected status code: ' . $this->response->getStatusCode());
        }
    }

    /**
     * @return ?bool
     */
    protected function processResponse()
    {
        switch ($this->method) {
            case 'GET':
                if ($this->response->getStatusCode() === Response::STATUS_CODE_200) {
                    return $this->responseData['Data'] ?? $this->responseData;
                }

                return false;
            case 'POST':
                if (
                    $this->response->getStatusCode() === Response::STATUS_CODE_201 ||
                    $this->response->getStatusCode() === Response::STATUS_CODE_202
                ) {
                    return $this->responseData['Data'] ?? null;
                }

                return false;
            // These currently do the same thing
            case 'PUT':
            case 'PATCH':
                if ($this->response->getStatusCode() === Response::STATUS_CODE_200) {
                    return $this->responseData['Data'] ?? null;
                }

                return $this->response->getStatusCode();
            case 'DELETE':
                if ($this->response->getStatusCode() === Response::STATUS_CODE_200) {
                    return $this->responseData['Data'] ?? null;
                }

                return false;
        }
    }
}
