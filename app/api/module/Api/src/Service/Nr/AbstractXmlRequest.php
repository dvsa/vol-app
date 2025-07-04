<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Nr;

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
        $this->responseDateTime = $dateTime->format(\DateTime::ATOM);
        $this->timeoutDateTime = $dateTime->add(new \DateInterval('PT10S'))->format(\DateTime::ATOM);
        $this->technicalId = $this->generateGuid();
        $this->authority = self::AUTHORITY_TC;
    }

    protected function getHeader(string $workflowId, string $memberStateCode): array
    {
        //if member state was GB, we need to make this UK
        $filteredMemberStateCode = ($memberStateCode === 'GB' ? 'UK' : $memberStateCode);

        return [
            'name' => 'Header',
            'attributes' => [
                'version' => $this->erruVersion,
                'technicalId' => $this->technicalId,
                'workflowId' => $workflowId,
                'sentAt' => $this->responseDateTime,
                'timeoutValue' => $this->timeoutDateTime,
                'from' => 'UK',
                'to' => $filteredMemberStateCode
            ],
        ];
    }

    protected function generateGuid(): string
    {
        // com_create_guid is unavailable on our environments
        return sprintf(
            '%04X%04X-%04X-%04X-%04X-%04X%04X%04X',
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(16384, 20479),
            mt_rand(32768, 49151),
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535)
        );
    }
}
