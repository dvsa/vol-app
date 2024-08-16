<?php

namespace Dvsa\Olcs\Api\Service\Nr\Mapping;

use Olcs\XmlTools\Filter\MapXmlFile;
use Olcs\XmlTools\Xml\Specification\NodeAttribute;
use Olcs\XmlTools\Xml\Specification\Recursion;
use Olcs\XmlTools\Xml\Specification\RecursionAttribute;
use Olcs\XmlTools\Xml\Specification\RecursionValue;

/**
 * Class ComplianceEpisodeXml
 * @package Dvsa\Olcs\Api\Service\Nr\Mapping
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
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

    /**
     * calculate the ns prefix
     *
     * @param \DOMDocument $domDocument dom document
     *
     * @return string|null
     */
    private function calculateNsPrefix(\DOMDocument $domDocument)
    {
        $nsPrefix = $domDocument->documentElement->lookupPrefix($this->xmlNs);
        $this->nsPrefix = ($nsPrefix !== null ? $nsPrefix . ':' : null);

        return $this->nsPrefix;
    }

    /**
     * Gets information to create the serious infringement
     *
     * @return array
     */
    protected function getSeriousInfringement()
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
                new NodeAttribute('notificationDateTime', 'notificationDateTime'),
                new Recursion(
                    $this->nsPrefix . 'TransportUndertaking',
                    [
                        new NodeAttribute('communityLicenceNumber', 'communityLicenceNumber'),
                        new NodeAttribute('vrm', 'vehicleRegNumber'),
                        new NodeAttribute('transportUndertakingName', 'name'),
                        $this->getSi()
                    ]
                )
            ],
        ];
    }

    /**
     * Gets Si information
     *
     * @return RecursionValue
     */
    protected function getSi()
    {
        $spec = [
            new NodeAttribute(['infringementDate'], 'dateOfInfringement'),
            new NodeAttribute(['siCategoryType'], 'infringementType'),
            new NodeAttribute(['checkDate'], 'dateOfCheck'),
            $this->getPenaltiesImposed(),
            $this->getPenaltiesRequested()
        ];

        return new RecursionValue(
            'si',
            new RecursionAttribute($this->nsPrefix . 'SeriousInfringement', $spec)
        );
    }

    /**
     * Gets imposed penalty data
     *
     * @return RecursionValue
     */
    protected function getPenaltiesImposed()
    {
        $spec = [
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

    /**
     * Gets requested penalty data
     *
     * @return RecursionValue
     */
    protected function getPenaltiesRequested()
    {
        $spec = [
            new NodeAttribute('siPenaltyRequestedType', 'penaltyTypeRequested'),
            new NodeAttribute('duration', 'duration'),
        ];

        return new RecursionValue(
            'requestedErrus',
            new RecursionAttribute($this->nsPrefix . 'PenaltyRequested', $spec)
        );
    }
}
