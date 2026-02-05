<?php

namespace Dvsa\Olcs\Api\Service\Ebsr;

use Dvsa\Olcs\Api\Domain\Exception\TransxchangeException;
use Olcs\XmlTools\Filter\ParseXmlString;
use Olcs\XmlTools\Filter\MapXmlFile;
use Olcs\XmlTools\Validator\Xsd;
use Laminas\Http\Client as RestClient;
use Olcs\Logging\Log\Logger;

/**
 * Class TransExchangeClient
 * @package Olcs\Ebsr\Service
 */
class TransExchangeClient implements TransExchangeClientInterface
{
    public const REQUEST_MAP_TEMPLATE = 'RequestMap';
    public const TIMETABLE_TEMPLATE = 'Timetable';
    public const DVSA_RECORD_TEMPLATE = 'DvsaRecord';
    public const TRANSXCHANGE_INVALID_XML = 'TransXchange response did not validate against the schema: ';

    public function __construct(
        private readonly RestClient $restClient,
        private readonly MapXmlFile $xmlFilter,
        private readonly ParseXmlString $xmlParser,
        private readonly Xsd $xsdValidator,
        private readonly string $correlationId
    ) {
    }

    /**
     * Makes the transxchange request
     *
     * @param string $content content of the request
     *
     * @throws TransxchangeException
     * @return array
     */
    public function makeRequest($content)
    {
        Logger::info('TransXchange request', ['data' => $content]);

        $this->restClient->getRequest()->setContent($content);
        $this->restClient->getRequest()->getHeaders()->addHeaders(
            [
                'X-Correlation-Id' => $this->getCorrelationId()
            ]
        );
        $response = $this->restClient->send();
        $body = $response->getContent();

        Logger::info('TransXchange response', ['data' => $response->toString()]);

        //security check, and parse into dom document
        $dom = $this->xmlParser->filter($body);

        //validate against schema
        if (!($this->xsdValidator->isValid($dom))) {
            $message = self::TRANSXCHANGE_INVALID_XML . implode(', ', $this->xsdValidator->getMessages());
            Logger::info('TransXchange error', ['data' => $message]);
            throw new TransxchangeException($message);
        }

        return $this->xmlFilter->filter($dom);
    }
    public function getCorrelationId(): string
    {
        return $this->correlationId;
    }
}
