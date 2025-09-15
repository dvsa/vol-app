<?php

namespace Dvsa\Olcs\Api\Service\Ebsr\Mapping;

use Olcs\XmlTools\Xml\Specification\FixedValue;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\XmlTools\Xml\Specification\NodeAttribute;
use Olcs\XmlTools\Xml\Specification\NodeValue;
use Olcs\XmlTools\Xml\Specification\Recursion;
use Olcs\XmlTools\Xml\Specification\MultiNodeValue;
use Psr\Container\ContainerInterface;

/**
 * Class MapXmlFileFactory
 * @package Olcs\Ebsr\Filter
 */
class TransExchangeXmlFactory implements FactoryInterface
{
    protected function getDocuments()
    {
        return ['SupportingDocument' => new Recursion('DocumentUri', new MultiNodeValue('documents'))];
    }

    /**
     * @return array
     */
    protected function getStopPoints()
    {

        return [
            'AnnotatedStopPointRef' => new Recursion('StopPointRef', new MultiNodeValue('stops'))
        ];
    }

    /**
     * @return array
     */
    protected function getRoutes()
    {
        $route = [
            'Description' => new MultiNodeValue('routeDescription'),
            'ReversingManoeuvres' => [
                new FixedValue('hasManoeuvre', 'Y'),
                new NodeValue('manoeuvreDetail')
            ]
        ];

        return ['Routes' => new Recursion('Route', new Recursion($route))];
    }

    /**
     * @return array
     */
    protected function getServicesSpecification()
    {
        $standardService = [
            'Origin' => new NodeValue('startPoint'),
            'Destination' => new NodeValue('finishPoint'),
            'Vias' => new Recursion('Via', new MultiNodeValue('via')),
            'UseAllStopPoints' => new NodeValue('useAllStops')
        ];

        $operatingPeriod = [
            'StartDate' => new NodeValue('effectiveDate'),
            'EndDate' => new NodeValue('endDate')
        ];

        $stopRequirements = [
            'NoNewStopsRequired' => new FixedValue('needNewStop', 'N'),
            'NewStops' => [
                new FixedValue('needNewStop', 'N'),
                new Recursion('StopPointRef', new NodeValue('newStopDetail'))
            ]
        ];

        $serviceClassification = [
            'NormalStopping' => new FixedValue(['serviceClassifications', 'NormalStopping'], 'Y'),
            'LimitedStops' => new FixedValue(['serviceClassifications', 'LimitedStops'], 'Y'),
            'HailAndRide' => new FixedValue(['serviceClassifications', 'HailAndRide'], 'Y'),
            'ExcursionOrTour' => new FixedValue(['serviceClassifications', 'ExcursionOrTour'], 'Y'),
            'SchoolOrWorks' => new FixedValue(['serviceClassifications', 'SchoolOrWorks'], 'Y'),
            'DialARide' => new FixedValue(['serviceClassifications', 'DialARide'], 'Y'),
            'RuralService' => new FixedValue(['serviceClassifications', 'RuralService'], 'Y'),
            'Flexible' => new FixedValue(['serviceClassifications', 'Flexible'], 'Y'),
        ];

        $service = [
            'OperatingPeriod' => new Recursion($operatingPeriod),
            'StandardService' => new Recursion($standardService),
            'Description' => new NodeValue('otherDetails'),
            'Lines' => new Recursion('Line', new Recursion('LineName', new MultiNodeValue('lineNames'))),
            'StopRequirements' => new Recursion($stopRequirements),
            'ServiceClassification' => new Recursion($serviceClassification),
            'SchematicMap' => new NodeValue('map')
        ];

        return ['Service' => new Recursion($service)];
    }

    /**
     * @return array
     */
    protected function getOperators()
    {
        $licencedOperator = [
            'LicenceNumber' => new NodeValue('licNo'),
            'EmailAddress' => new NodeValue('organisationEmail')
        ];

        return ['LicensedOperator' => new Recursion($licencedOperator)];
    }

    /**
     * @return array
     */
    protected function getRegistrations()
    {
        $trafficAreas = [
            'TrafficArea' => new Recursion('TrafficAreaName', new MultiNodeValue('trafficAreas'))
        ];

        $circulatedAuthorities = [
            'CirculatedAuthority' => new Recursion('AuthorityName', new MultiNodeValue('localAuthorities'))
        ];

        $subsidy = [
            'SubsidyType' => new NodeValue('subsidised'),
            'SubsidisingAuthority' => new NodeValue('subsidyDetail')
        ];

        $contractedService = [
            'NotContracted' => new FixedValue('isQualityContract', 'N'),
            'WhollyContracted' => new FixedValue('isQualityContract', 'Y'),
            'PartContracted' => new FixedValue('isQualityContract', 'Y'),
            'ContractingAuthority' => new Recursion('AuthorityName', new MultiNodeValue('qualityContractDetails'))
        ];

        $subsidyDetails = [
            'NoSubsidy' => new FixedValue('subsidised', 'none'),
            'Subsidy' => new Recursion($subsidy)
        ];

        $registration = [
            'ApplicationClassification' => new NodeValue('txcAppType'),
            'VariationNumber' => new NodeValue('variationNo'),
            'VosaRegistrationNumber' => new Recursion('RegistrationNumber', new NodeValue('routeNo')),
            'TrafficAreas' => new Recursion($trafficAreas),
            'CirculatedAuthorities' => new Recursion($circulatedAuthorities),
            'ShortNoticeRegistration' => new Recursion($this->getShortNotice()),
            'SubsidyDetails' => new Recursion($subsidyDetails),
            'QualityPartnership' => [
                new FixedValue('isQualityPartnership', 'Y'),
                new NodeValue('qualityPartnershipDetails')
            ],
            'ContractedService' => new Recursion($contractedService)
        ];

        return ['Registration' => new Recursion($registration)];
    }

    /**
     * @return array
     */
    protected function getShortNotice()
    {
        $changeImpact = [
            'ChangeDoesNotExceedLimit' => [
                new FixedValue(['busShortNotice', 'timetableChange'], 'Y'),
                new Recursion('MinorChangeDescription', new NodeValue(['busShortNotice', 'timetableDetail']))
            ],
            'ChangeExceedsLimit' => new FixedValue(['busShortNotice', 'timetableChange'], 'N'),
        ];

        $publicAvailability = [
            'NotAvailableToPublic' => [
                new FixedValue(['busShortNotice', 'notAvailableChange'], 'Y'),
                new Recursion('NonAvailabilityDescription', new NodeValue(['busShortNotice', 'notAvailableDetail']))
            ],
            'AvailableToPublic' => new FixedValue(['busShortNotice', 'notAvailableChange'], 'N'),
        ];

        $changeRequestedByExternalAuthority = [
            new FixedValue(['busShortNotice', 'policeChange'], 'Y'),
            new Recursion('ChangeRequestDescription', new NodeValue(['busShortNotice', 'policeDetail']))
        ];

        $exceptionalRequirement = [
            new FixedValue(['busShortNotice', 'unforseenChange'], 'Y'),
            new Recursion('ChangeRequestDescription', new NodeValue(['busShortNotice', 'unforseenDetail']))
        ];

        $localHolidayChange = [
            new FixedValue(['busShortNotice', 'holidayChange'], 'Y'),
            new Recursion('LocalHolidayNote', new NodeValue(['busShortNotice', 'holidayDetail']))
        ];

        $regulationOrderCompliance = [
            new FixedValue(['busShortNotice', 'trcChange'], 'Y'),
            new Recursion('TrafficOrderNote', new NodeValue(['busShortNotice', 'trcDetail']))
        ];

        $specialOccasion = [
            new FixedValue(['busShortNotice', 'specialOccasionChange'], 'Y'),
            new Recursion('SpecialOccasionName', new NodeValue(['busShortNotice', 'specialOccasionDetail']))
        ];

        $alteredService = [
            'AlteredServiceRequiringConnection' =>
                new Recursion('Description', new NodeValue(['busShortNotice', 'connectionDetail']))
        ];

        $changeToConnectAlteredService = [
            new FixedValue(['busShortNotice', 'connectionChange'], 'Y'),
            new Recursion($alteredService)
        ];

        $replacedService = [
            'DiscontinuedService' =>
                new Recursion('Description', new NodeValue(['busShortNotice', 'replacementDetail']))
        ];

        $replaceDiscontinuedService = [
            new FixedValue(['busShortNotice', 'replacementChange'], 'Y'),
            new Recursion($replacedService)
        ];

        return [
            'BankHolidayChange' => new FixedValue(['busShortNotice', 'bankHolidayChange'], 'Y'),
            'ChangeImpact' => new Recursion($changeImpact),
            'PublicAvailability' => new Recursion($publicAvailability),
            'ChangeRequestedByExternalAuthority' => $changeRequestedByExternalAuthority,
            'ChangeToConnectAlteredService' => $changeToConnectAlteredService,
            'ExceptionalRequirement' => $exceptionalRequirement,
            'LocalHolidayChange' => $localHolidayChange,
            'MiscellaneousJustification' => new NodeValue(['busShortNotice', 'miscJustification']),
            'RegulationOrderCompliance' => $regulationOrderCompliance,
            'ReplaceDiscontinuedService' => $replaceDiscontinuedService,
            'SpecialOccasion' => $specialOccasion
        ];
    }
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $transXChange = [
            'SchemaVersion'       => new NodeAttribute('txcSchemaVersion', 'SchemaVersion'),
            'Services'            => new Recursion($this->getServicesSpecification()),
            'Operators'           => new Recursion($this->getOperators()),
            'Registrations'       => new Recursion($this->getRegistrations()),
            'Routes'              => new Recursion($this->getRoutes()),
            'StopPoints'          => new Recursion($this->getStopPoints()),
            'SupportingDocuments' => new Recursion($this->getDocuments()),
        ];
        $mapping = new Recursion($transXChange);
        return $mapping;
    }
}
