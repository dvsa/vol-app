<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Nr\Mapping;

use Olcs\XmlTools\Filter\MapXmlFile;
use Olcs\XmlTools\Xml\Specification\NodeAttribute;
use Olcs\XmlTools\Xml\Specification\Recursion;
use Olcs\XmlTools\Xml\Specification\RecursionAttribute;
use Olcs\XmlTools\Xml\Specification\RecursionValue;

class CgrResponseXml
{
    private ?string $nsPrefix;

    public function __construct(private MapXmlFile $mapXmlFile, private string $xmlNs)
    {
    }

    /**
     * map the xml data to an array
     *
     * @return array
     */
    public function mapData(\DOMDocument $domDocument)
    {
        $this->calculateNsPrefix($domDocument);
        $this->mapXmlFile->setMapping(new Recursion($this->getCheckReputeResponse()));
        return $this->mapXmlFile->filter($domDocument);
    }

    private function calculateNsPrefix(\DOMDocument $domDocument): ?string
    {
        $nsPrefix = $domDocument->documentElement->lookupPrefix($this->xmlNs);
        $this->nsPrefix = ($nsPrefix !== null ? $nsPrefix . ':' : null);

        return $this->nsPrefix;
    }

    protected function getCheckReputeResponse(): array
    {
        return [
            $this->nsPrefix . 'Header' => [
                new NodeAttribute('varsion', 'version'),
                new NodeAttribute('workflowId', 'workflowId'),
                new NodeAttribute('technicalId', 'technicalId'),
                new NodeAttribute('sentAt', 'sentAt')
            ],
            $this->nsPrefix . 'Body' => [
                new Recursion(
                    $this->nsPrefix . 'SearchedTransportManager',
                    [
                        new Recursion(
                            $this->nsPrefix . 'TransportManagerNameDetails',
                            [
                                new NodeAttribute('familyName', 'familyName'),
                                new NodeAttribute('firstName', 'firstName'),
                                new NodeAttribute('dateOfBirth', 'dateOfBirth'),
                                new NodeAttribute('familyNameSearchKey', 'familyNameSearchKey'),
                                new NodeAttribute('firstNameSearchKey', 'firstNameSearchKey'),
                            ]
                        ),
                        new Recursion(
                            $this->nsPrefix . 'TransportManagerCertificateDetails',
                            [
                                new NodeAttribute('certificateNumber', 'certificateNumber'),
                                new NodeAttribute('certificateIssueDate', 'certificateIssueDate'),
                                new NodeAttribute('certificateIssueCountry', 'certificateIssueCountry'),
                            ]
                        ),
                    ],
                ),
                $this->getMemberStateResponses(),
            ],
        ];
    }

    private function getMemberStateResponses(): RecursionValue
    {
        $spec = [
            new NodeAttribute('memberStateCode', 'memberStateCode'),
            new NodeAttribute('statusCode', 'statusCode'),
            new Recursion($this->nsPrefix . 'TransportManagerCertificateDetails')
        ];

        return new RecursionValue(
            'memberStateResponses',
            new RecursionAttribute($this->nsPrefix . 'MemberState', $spec)
        );
    }
}
