<?php

namespace Common\Service\Api;

use Common\Util\RestClient;
use Laminas\Authentication\Storage\Session;
use Laminas\Filter\Word\CamelCaseToDash;
use Laminas\Http\Header\Cookie;
use Laminas\Http\Request;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Laminas\Uri\Http;
use Psr\Container\ContainerInterface;

class AbstractFactory implements AbstractFactoryInterface
{
    #[\Override]
    public function canCreate(ContainerInterface $container, $requestedName): bool
    {
        return str_contains($requestedName, 'Olcs\\RestService\\');
    }

    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): RestClient
    {
        $api = str_replace('Olcs\\RestService\\', '', $requestedName);

        $api = explode('\\', $api);
        if (count($api) == 1) {
            array_unshift($api, 'backend');
        }

        [$endpoint, $uri] = $api;

        $config = $container->get('Config');
        if (!isset($config['service_api_mapping']['endpoints'][$endpoint])) {
            throw new ServiceNotCreatedException('No endpoint defined for: ' . $endpoint);
        }

        /** @var \Laminas\I18n\Translator\TranslatorInterface $translator */
        $translator = $container->get('translator');

        $filter = new CamelCaseToDash();
        $uri = strtolower($filter->filter($uri));
        $url = new Http($uri);

        $endpointConfig = $config['service_api_mapping']['endpoints'][$endpoint];
        $options = [];
        $auth = [];
        if (is_array($endpointConfig)) {
            $url =  $url->resolve($endpointConfig['url']);
            $options = $endpointConfig['options'];
            $auth = $endpointConfig['auth'] ?? [];
        } else {
            $url =  $url->resolve($endpointConfig);
        }

        $userRequest = $container->get('Request');
        $secureToken = new Cookie();
        if ($userRequest instanceof Request) {
            $cookies = $userRequest->getCookie();
            if (isset($cookies['secureToken'])) {
                $secureToken = new Cookie(['secureToken' => $cookies['secureToken']]);
            }
        }

        // options
        $rest = new RestClient($url, $options, $auth, $secureToken);
        $rest->setLanguage($translator->getLocale());

        $session = $container->get(Session::class)->read();
        $accessToken = $session['AccessToken'] ?? null;
        if (!is_null($accessToken)) {
            $rest->setAuthHeader(sprintf("Authorization:Bearer %s", $accessToken));
        }

        return $rest;
    }
}
