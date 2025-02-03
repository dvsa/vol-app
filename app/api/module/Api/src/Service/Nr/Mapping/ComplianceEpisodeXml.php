<?php

namespace Dvsa\Olcs\Api\Service\Nr\Mapping;

use Olcs\XmlTools\Filter\MapXmlFile;
use Olcs\XmlTools\Xml\Specification\NodeAttribute;
use Olcs\XmlTools\Xml\Specification\Recursion;
use Olcs\XmlTools\Xml\Specification\RecursionAttribute;
use Olcs\XmlTools\Xml\Specification\RecursionValue;

class ComplianceEpisodeXml
{
    protected $nsPrefix;
    protected $mapXmlFile;

    /**
     * ComplianceEpisodeXml constructor.
     *
     * @param MapXmlFile $mapXmlFile olcs-xmltools xml mapper
     * @param string     $xmlNs      address of xml namespace
     */
    public function __construct(MapXmlFile $mapXmlFile, protected $xmlNs)
    {
        $this->mapXmlFile = $mapXmlFile;
    }

    /**
     * map the xml data to an array
     *
     * @param \DOMDocument $domDocument xml document
     *
     * @return array
     */
    public function mapData(\DOMDocument $domDocument)
    {
        $this->calculateNsPrefix($domDocument);
        $this->mapXmlFile->setMapping(new Recursion($this->getSeriousInfringement()));
        return $this->mapXmlFile->filter($domDocument);
    }

    private function calculateNsPrefix(\DOMDocument $domDocument): ?string
    {
        $nsPrefix = $domDocument->documentElement->lookupPrefix($this->xmlNs);
        $this->nsPrefix = ($nsPrefix !== null ? $nsPrefix . ':' : null);

        return $this->nsPrefix;
    }

    /**
     * Gets information to create the serious infringement
     */
    protected function getSeriousInfringement(): array
    {
        return [
            $this->nsPrefix . 'Header' => [
                new NodeAttribute('workflowId', 'workflowId'),
                new NodeAttribute('memberStateCode', 'from'),
                new NodeAttribute('sentAt', 'sentAt')
            ],
            $this->nsPrefix . 'Body' => [
                new NodeAttribute('notificationNumber', 'businessCaseId'),
                new NodeAttribute('originatingAuthority', 'originatingAuthority'),
                new Recursion(
                    $this->nsPrefix . 'TransportUndertaking',
                    [
                        new NodeAttribute('communityLicenceNumber', 'communityLicenceNumber'),
                        new NodeAttribute('transportUndertakingName', 'transportUndertakingName'),
                        new Recursion(
                            $this->nsPrefix . 'Vehicle',
                            [
                                new NodeAttribute('vrm', 'vehicleRegistrationNumber')
                            ]
                        ),
                        new Recursion(
                            $this->nsPrefix . 'CheckSummary',
                            [
                                new NodeAttribute('checkDate', 'dateOfCheck')
                            ]
                        ),
                        $this->getSi()
                    ]
                )
            ],
        ];
    }

    private function getSi(): RecursionValue
    {
        $spec = [
            new NodeAttribute(['infringementDate'], 'dateOfInfringement'),
            new NodeAttribute(['siCategoryType'], 'infringementType'),
            new Recursion(
                $this->nsPrefix . 'PenaltiesImposed',
                [
                    $this->getPenaltiesImposed(),
                ],
            ),
            new Recursion(
                $this->nsPrefix . 'PenaltiesRequested',
                [
                    $this->getPenaltiesRequested(),
                ],
            ),
        ];

        return new RecursionValue(
            'si',
            new RecursionAttribute($this->nsPrefix . 'SeriousInfringement', $spec)
        );
    }

    private function getPenaltiesImposed(): RecursionValue
    {
        $spec = [
            new NodeAttribute('penaltyImposedIdentifier', 'penaltyImposedIdentifier'),
            new NodeAttribute('finalDecisionDate', 'finalDecisionDate'),
            new NodeAttribute('siPenaltyImposedType', 'penaltyTypeImposed'),
            new NodeAttribute('startDate', 'startDate'),
            new NodeAttribute('endDate', 'endDate'),
            new NodeAttribute('executed', 'isExecuted'),
        ];

        return new RecursionValue(
            'imposedErrus',
            new RecursionAttribute($this->nsPrefix . 'PenaltyImposed', $spec)
        );
    }

    private function getPenaltiesRequested(): RecursionValue
    {
        $spec = [
            new NodeAttribute('penaltyRequestedIdentifier', 'penaltyRequestedIdentifier'),
            new NodeAttribute('siPenaltyRequestedType', 'penaltyTypeRequested'),
            new NodeAttribute('duration', 'duration'),
        ];

        return new RecursionValue(
            'requestedErrus',
            new RecursionAttribute($this->nsPrefix . 'PenaltyRequested', $spec)
        );
    }
}
