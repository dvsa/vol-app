<?php

namespace Dvsa\Olcs\Api\Service\Ebsr;

use Dvsa\Olcs\Api\Service\AccessToken\Provider;
use Laminas\Filter\FilterPluginManager;
use Laminas\Http\Request;
use Laminas\Log\Processor\RequestId;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\XmlTools\Filter\ParseXmlString;
use Olcs\XmlTools\Filter\MapXmlFile;
use Olcs\XmlTools\Validator\Xsd;
use Laminas\Http\Client as RestClient;
use Dvsa\Olcs\Utils\Client\ClientAdapterLoggingWrapper;
use Psr\Container\ContainerInterface;

class TransExchangeClientFactory implements FactoryInterface
{
    public const PUBLISH_XSD = 'http://naptan.dft.gov.uk/transxchange/publisher/schema/3.1.2/TransXChangePublisherService.xsd';

    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return TransExchangeClient
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TransExchangeClient
    {
        $config = $container->get('config');

        if (!isset($config['ebsr']['transexchange_publisher'])) {
            throw new \RuntimeException('Missing transexchange_publisher config');
        }

        $config = $config['ebsr']['transexchange_publisher'];

        $correlationId = (new RequestId())->process([])['extra']['requestId'];

        /** @var Provider $tokenProvider */
        $tokenProvider = $container->build(Provider::class, $config['oauth2']);
        $headers = ['Authorization' => 'Bearer ' .  $tokenProvider->getToken()];

        $config['uri'] = $config['new_uri'];
        $method = Request::METHOD_POST;

        $httpClient = new RestClient($config['uri'], $config['options']);
        $httpClient->setHeaders($headers);
        $httpClient->setMethod($method);
        $wrapper = new ClientAdapterLoggingWrapper();
        $wrapper->wrapAdapter($httpClient);
        /**
         * @var FilterPluginManager $filterManager
         * @var MapXmlFile $xmlFilter
         * @var ParseXmlString $xmlParser
         * @var Xsd $xsdValidator
         */
        $filterManager = $container->get('FilterManager');
        $xmlParser = $filterManager->get(ParseXmlString::class);
        $xmlFilter = $filterManager->get(MapXmlFile::class);
        $xmlFilter->setMapping($container->get('TransExchangePublisherXmlMapping'));
        $xsdValidator = $container->get('ValidatorManager')->get(Xsd::class);
        $xsdValidator->setXsd(self::PUBLISH_XSD);
        return new TransExchangeClient($httpClient, $xmlFilter, $xmlParser, $xsdValidator, $correlationId);
    }
}
