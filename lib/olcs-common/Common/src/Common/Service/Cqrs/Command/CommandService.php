<?php

namespace Common\Service\Cqrs\Command;

use Common\Exception\ResourceConflictException;
use Common\Service\Cqrs\CqrsTrait;
use Common\Service\Cqrs\Response;
use Common\Service\Cqrs\Exception;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Util\FileContent;
use Dvsa\Olcs\Transfer\Command\CommandContainerInterface;
use Dvsa\Olcs\Transfer\Command\LoggerOmitContentInterface;
use Dvsa\Olcs\Utils\Client\ClientAdapterLoggingWrapper;
use Laminas\Http\Client;
use Laminas\Http\Client\Exception\ExceptionInterface as HttpClientExceptionInterface;
use Laminas\Http\Header\Authorization;
use Laminas\Http\Header\ContentType;
use Laminas\Http\Header\Cookie;
use Laminas\Http\Headers;
use Laminas\Http\Request;
use Laminas\Http\Response as HttpResponse;
use Laminas\Router\Exception\ExceptionInterface;
use Laminas\Router\RouteInterface;
use Laminas\Session\Container;

/**
 * Command
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CommandService
{
    use CqrsTrait;

    /**
     * @var RouteInterface
     */
    protected $router;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Request
     */
    protected $request;

    /**
     * CommandService constructor.
     *
     * @param RouteInterface              $router          Router
     * @param Client                      $client          Http Client
     * @param Request                     $request         Http Request
     * @param boolean                     $showApiMessages Is Show Api messages
     * @param FlashMessengerHelperService $flashMessenger  Flash messenger
     */
    public function __construct(
        RouteInterface $router,
        Client $client,
        Request $request,
        $showApiMessages,
        $flashMessenger,
        private Container $session
    ) {
        $this->router = $router;
        $this->client = $client;
        $this->request = $request;
        $this->showApiMessages = $showApiMessages;
        $this->flashMessenger = $flashMessenger;
    }

    /**
     * Send a query and return the response
     *
     * @param \Dvsa\Olcs\Transfer\Command\CommandContainer $command Command
     *
     * @return Response
     * @throws ResourceConflictException
     */
    public function send(CommandContainerInterface $command)
    {
        if (!$command->isValid()) {
            return $this->invalidResponse($command->getMessages(), HttpResponse::STATUS_CODE_422);
        }

        $routeName = $command->getRouteName();
        $method = $command->getMethod();

        $data = $command->getDto()->getArrayCopy();

        try {
            $routeName = str_replace('backend/', 'backend/api/', $routeName);
            $uri = $this->router->assemble($data, ['name' => 'api/' . $routeName . '/' . $method]);
        } catch (ExceptionInterface $exception) {
            throw new Exception($exception->getMessage(), HttpResponse::STATUS_CODE_404, $exception);
        }

        /**
         * Always use a clone, to prevent leakage
         */
        $request = clone $this->request;

        /**
         * Check to see if secureToken is defined in the DTO, if so.. override the secureToken in the request.
         */
        if (array_key_exists('secureToken', $data) && !empty($data['secureToken'])) {
            $request = $this->replaceOrAddSecureTokenCookieToRequest($request, $data['secureToken']);
        }

        $this->client->resetParameters(true);
        $this->client->setRequest($request);

        $isMultipart = false;

        /**
         * Check for FileContent, then we will send as multipart rather than JSON
         */
        foreach ($data as $name => $value) {
            if ($value instanceof FileContent) {
                $isMultipart = true;
                $this->client->setFileUpload($value->getFileName(), $name, null, $value->getMimeType());
            }
        }

        $request->setUri($uri);
        $request->setMethod($method);

        $this->addAuthorizationHeader($request);

        /**
         * If we are sending multipart, we need to remove the application/json header, the multipart header will be
         * added by ZF2s client
         */
        if ($isMultipart) {
            $headers = $request->getHeaders();
            $newHeaders = new Headers();
            foreach ($headers as $header) {
                if (!($header instanceof ContentType)) {
                    $newHeaders->addHeader($header);
                }
            }

            $request->setHeaders($newHeaders);
            $this->client->setRequest($request);
            $this->client->setParameterPost($data);
        } else {
            $request->setContent(json_encode($data));
        }

        /** @var ClientAdapterLoggingWrapper $adapter */
        $adapter = $this->client->getAdapter();

        try {
            if ($command->getDto() instanceof LoggerOmitContentInterface) {
                $shouldLogContent = $adapter->getShouldLogData();
                $adapter->setShouldLogData(false);
            }

            $clientResponse = $this->client->send();

            if ($command->getDto() instanceof LoggerOmitContentInterface) {
                $adapter->setShouldLogData($shouldLogContent);
            }

            if ($clientResponse->getStatusCode() === HttpResponse::STATUS_CODE_409) {
                throw new ResourceConflictException('Resource conflict');
            }

            $response = new Response($clientResponse);

            if ($response->getStatusCode() === HttpResponse::STATUS_CODE_404) {
                throw new Exception\NotFoundException('API responded with a 404 Not Found : ' . $uri);
            }

            if ($response->getStatusCode() === HttpResponse::STATUS_CODE_403) {
                throw new Exception\AccessDeniedException($response->getBody() . ' : ' . $uri);
            }

            if ($response->getStatusCode() > HttpResponse::STATUS_CODE_400) {
                throw new Exception($response->getBody()  . " : " . $uri);
            }

            if ($this->showApiMessages) {
                $this->showApiMessagesFromResponse($response);
            }

            return $response;
        } catch (HttpClientExceptionInterface $httpClientException) {
            throw new Exception($httpClientException->getMessage(), HttpResponse::STATUS_CODE_500, $httpClientException);
        }
    }

    private function replaceOrAddSecureTokenCookieToRequest(Request $request, string $token): Request
    {
        $requestHeaders = $request->getHeaders();
        $requestHeaders->addHeader(new Cookie(['secureToken' => $token]));

        $request->setHeaders($requestHeaders);

        return $request;
    }

    private function addAuthorizationHeader(Request $request): void
    {
        $accessToken = $this->session->offsetGet('storage')['AccessToken'] ?? null;

        if (is_null($accessToken)) {
            return;
        }

        $header = sprintf("Authorization:Bearer %s", $accessToken);
        $headers = $request->getHeaders();
        $headers->addHeader(Authorization::FromString($header));

        $request->setHeaders($headers);
    }
}
