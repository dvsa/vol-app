<?php

namespace Common\Service\Cqrs;

use Psr\Container\ContainerInterface;
use Laminas\Http\Header\Cookie;
use Laminas\Http\Headers;
use Laminas\Http\Header\Accept;
use Laminas\Http\Header\ContentType;
use Laminas\Http\Request;
use Laminas\ServiceManager\Factory\FactoryInterface;

class RequestFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Request
    {
        $accept = new Accept();
        $accept->addMediaType('application/json');

        $contentType = new ContentType();
        $contentType->setMediaType('application/json');

        $identifier = $container->get(\Olcs\Logging\Log\Processor\RequestId::class)->getIdentifier();
        $correlationHeader = new \Laminas\Http\Header\GenericHeader('X-Correlation-Id', $identifier);

        $headers = new Headers();
        $headers->addHeaders([$accept, $contentType, $correlationHeader]);

        $userRequest = $container->get('Request');
        if ($userRequest instanceof Request) {
            $cookies = $userRequest->getCookie();
            if (isset($cookies['secureToken'])) {
                $secureToken = new Cookie(['secureToken' => $cookies['secureToken']]);
                $headers->addHeader($secureToken);
            }
        }

        $request = new Request();
        $request->setHeaders($headers);

        return $request;
    }
}
