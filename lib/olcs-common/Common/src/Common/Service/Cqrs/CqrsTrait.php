<?php

namespace Common\Service\Cqrs;

use Laminas\Http\Response as HttpResponse;

/**
 * Cqrs Trait
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
trait CqrsTrait
{
    /**
     * @var bool
     */
    protected $showApiMessages;

    /**
     * @var \Common\Service\Helper\FlashMessengerHelperService
     */
    protected $flashMessenger;

    /**
     * Invalid response
     *
     * @param array $messages   Messages
     * @param int   $statusCode Status Code
     *
     * @return Response
     */
    protected function invalidResponse(array $messages = [], $statusCode = HttpResponse::STATUS_CODE_500)
    {
        $httpResponse = new HttpResponse();
        $httpResponse->setStatusCode($statusCode);

        $response = new Response($httpResponse);
        $response->setResult(['messages' => $messages]);

        if ($this->showApiMessages) {
            $this->showApiMessages($messages);
        }

        return $response;
    }

    /**
     * Show API messages
     *
     * @param array $messages Messages
     *
     * @return void
     */
    protected function showApiMessages($messages = [])
    {
        foreach ($messages as $message) {
            $message = (is_array($message)) ? end($message) : $message;
            $this->flashMessenger->addErrorMessage('DEBUG: ' . print_r($message, true));
        }
    }

    /**
     * Show API messages from response
     *
     * @param \Common\Service\Cqrs\Response $response Cqrs Response
     *
     * @return void
     */
    protected function showApiMessagesFromResponse($response)
    {
        if ($response->getHttpResponse() instanceof HttpResponse\Stream) {
            return;
        }

        if (json_last_error() && $response->getStatusCode() !== 302) {
            $this->showApiMessages(['Error decoding json response: ' . $response->getBody()]);
        }

        $result = $response->getResult();
        if ($response->isOk()) {
            return;
        }
        if (!isset($result['messages'])) {
            return;
        }
        $this->showApiMessages($result['messages']);
    }
}
