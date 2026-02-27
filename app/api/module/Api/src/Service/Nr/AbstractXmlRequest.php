<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Nr;

use DateTimeInterface;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime as DateTimeExtended;
use Olcs\XmlTools\Xml\XmlNodeBuilder;

class AbstractXmlRequest
{
    public const AUTHORITY_TC = 'Traffic Commissioner';

    private readonly string $responseDateTime;

    private readonly string $timeoutDateTime;

    private readonly string $technicalId;

    protected string $authority;

    public function __construct(protected readonly XmlNodeBuilder $xmlBuilder, private readonly string $erruVersion)
    {
        $dateTime = new DateTimeExtended();
        $this->responseDateTime = $dateTime->format(DateTimeInterface::ATOM);
        $this->timeoutDateTime = $dateTime->add(new \DateInterval('PT10S'))->format(DateTimeInterface::ATOM);
        $this->technicalId = $this->generateGuid();
        $this->authority = self::AUTHORITY_TC;
    }

    /**
     * Member state code will be EU in the case of MSIs and ZZ in the case of check good repute
     */
    protected function getHeader(string $workflowId, string $memberStateCode): array
    {
        return [
            'name' => 'Header',
            'attributes' => [
                'version' => $this->erruVersion,
                'technicalId' => $this->technicalId,
                'workflowId' => $workflowId,
                'sentAt' => $this->responseDateTime,
                'timeoutValue' => $this->timeoutDateTime,
                'from' => 'UK',
                'to' => $memberStateCode
            ],
        ];
    }

    protected function generateGuid(): string
    {
        // com_create_guid is unavailable on our environments
        return sprintf(
            '%04X%04X-%04X-%04X-%04X-%04X%04X%04X',
            random_int(0, 65535),
            random_int(0, 65535),
            random_int(0, 65535),
            random_int(16384, 20479),
            random_int(32768, 49151),
            random_int(0, 65535),
            random_int(0, 65535),
            random_int(0, 65535)
        );
    }

    public function getTechnicalId(): ?string
    {
        return $this->technicalId;
    }

    public function getTimeoutDateTime(): string
    {
        return $this->timeoutDateTime;
    }

    public function getResponseDateTime(): string
    {
        return $this->responseDateTime;
    }

    public function getXmlBuilder(): XmlNodeBuilder
    {
        return $this->xmlBuilder;
    }
}
