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

    public function __construct(private readonly MapXmlFile $mapXmlFile, private readonly string $xmlNs)
    {
    }

    public function mapData(\DOMDocument $domDocument): array
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
                new NodeAttribute('version', 'version'),
                new NodeAttribute('workflowId', 'workflowId'),
                new NodeAttribute('technicalId', 'technicalId'),
                new NodeAttribute('sentAt', 'sentAt')
            ],
            $this->nsPrefix . 'Body' => [
                $this->getSearchedTransportManager(),
                $this->getMemberStateResponses(),
            ],
        ];
    }

    private function getSearchedTransportManager(): RecursionValue
    {
        $spec = [
            $this->getSearchedTransportManagerNameDetails(),
            $this->getSearchedTransportManagerCertificateDetails(),
        ];

        return new RecursionValue(
            'searchedTransportManager',
            new Recursion($this->nsPrefix . 'SearchedTransportManager', $spec)
        );
    }

    private function getSearchedTransportManagerNameDetails(): RecursionValue
    {
        return new RecursionValue(
            'transportManagerNameDetails',
            new Recursion($this->nsPrefix . 'TransportManagerNameDetails',
                [
                    new NodeAttribute('familyName', 'familyName'),
                    new NodeAttribute('firstName', 'firstName'),
                    new NodeAttribute('dateOfBirth', 'dateOfBirth'),
                    new NodeAttribute('familyNameSearchKey', 'familyNameSearchKey'),
                    new NodeAttribute('firstNameSearchKey', 'firstNameSearchKey'),
                ],
            ),
        );
    }

    private function getSearchedTransportManagerCertificateDetails(): RecursionValue
    {
        return new RecursionValue(
            'transportManagerCertificateDetails',
            new Recursion($this->nsPrefix . 'TransportManagerCertificateDetails',
                [
                    new NodeAttribute('certificateNumber', 'certificateNumber'),
                    new NodeAttribute('certificateIssueDate', 'certificateIssueDate'),
                    new NodeAttribute('certificateIssueCountry', 'certificateIssueCountry'),
                ],
            ),
        );
    }

    private function getMemberStateResponses(): RecursionValue
    {
        $spec = [
            new NodeAttribute('memberStateCode', 'memberStateCode'),
            new NodeAttribute('statusCode', 'statusCode'),
            $this->getTransportManagerDetailsFromMemberState(),
        ];

        return new RecursionValue(
            'memberStateResponses',
            new RecursionAttribute($this->nsPrefix . 'MemberState', $spec)
        );
    }

    private function getTransportManagerDetailsFromMemberState(): RecursionValue
    {
        return new RecursionValue(
            'transportManagerDetails',
            new Recursion($this->nsPrefix . 'TransportManagerDetails',
                [
                    new NodeAttribute('respondingAuthority', 'respondingAuthority'),
                    new NodeAttribute('searchMethod', 'searchMethod'),
                    $this->getTransportManagerNameDetailsFromMemberState(),
                    $this->getTransportManagerAddressDetailsFromMemberState(),
                    $this->getTransportManagerCertificateDetailsFromMemberState(),
                    $this->getTransportUndertakingsFromMemberState(),
                ],
            ),
        );
    }

    private function getTransportManagerNameDetailsFromMemberState(): RecursionValue
    {
        return new RecursionValue(
            'transportManagerNameDetails',
            new Recursion($this->nsPrefix . 'TransportManagerNameDetails',
                [
                    new NodeAttribute('familyName', 'familyName'),
                    new NodeAttribute('firstName', 'firstName'),
                    new NodeAttribute('dateOfBirth', 'dateOfBirth'),
                    new NodeAttribute('placeOfBirth', 'placeOfBirth'),
                ],
            ),
        );
    }

    private function getTransportManagerAddressDetailsFromMemberState(): RecursionValue
    {
        return new RecursionValue(
            'transportManagerAddressDetails',
            new Recursion($this->nsPrefix . 'TransportManagerAddressDetails',
                [
                    new NodeAttribute('address', 'address'),
                    new NodeAttribute('postCode', 'postCode'),
                    new NodeAttribute('city', 'city'),
                    new NodeAttribute('country', 'country'),
                ],
            ),
        );
    }

    private function getTransportManagerCertificateDetailsFromMemberState(): RecursionValue
    {
        return new RecursionValue(
            'transportManagerCertificateDetails',
            new Recursion($this->nsPrefix . 'TransportManagerCertificateDetails',
                [
                    new NodeAttribute('certificateNumber', 'certificateNumber'),
                    new NodeAttribute('certificateIssueDate', 'certificateIssueDate'),
                    new NodeAttribute('certificateIssueCountry', 'certificateIssueCountry'),
                    new NodeAttribute('certificateValidity', 'certificateValidity'),
                    $this->getTransportManagerCertificateDetailsFitness(),
                ],
            ),
        );
    }

    private function getTransportManagerCertificateDetailsFitness(): RecursionValue
    {
        return new RecursionValue(
            'fitness',
            new Recursion($this->nsPrefix . 'Fitness',
                [
                    new NodeAttribute('fitnessStatus', 'fitnessStatus'),
                    new NodeAttribute('unfitStartDate', 'unfitStartDate'),
                    new NodeAttribute('unfitEndDate', 'unfitEndDate'),
                ],
            ),
        );
    }

    private function getTransportUndertakingsFromMemberState(): RecursionValue
    {
        return new RecursionValue(
            'transportUndertakings',
            new Recursion($this->nsPrefix . 'TransportUndertakings',
                [
                    new NodeAttribute('totalManagedUndertakings', 'totalManagedUndertakings'),
                    new NodeAttribute('totalManagedVehicles', 'totalManagedVehicles'),
                    $this->getTransportUndertakingFromMemberState()
                ],
            ),
        );
    }

    private function getTransportUndertakingFromMemberState(): RecursionValue
    {
        $spec = [
            new NodeAttribute('transportUndertakingName', 'transportUndertakingName'),
            new NodeAttribute('numberOfVehicles', 'numberOfVehicles'),
            new NodeAttribute('communityLicenceNumber', 'communityLicenceNumber'),
            new NodeAttribute('communityLicenceStatus', 'communityLicenceStatus'),
            $this->getTransportUndertakingAddressFromMemberState(),
            new Recursion(
                $this->nsPrefix . 'TransportManagerCertificateDetails',
                [
                    new NodeAttribute('certificateNumber', 'certificateNumber'),
                    new NodeAttribute('certificateIssueDate', 'certificateIssueDate'),
                    new NodeAttribute('certificateIssueCountry', 'certificateIssueCountry'),
                    new NodeAttribute('certificateValidity', 'certificateValidity'),
                ],
            ),
        ];

        return new RecursionValue(
            'transportUndertaking',
            new RecursionAttribute($this->nsPrefix . 'TransportUndertaking', $spec)
        );
    }

    private function getTransportUndertakingAddressFromMemberState(): RecursionValue
    {
        return new RecursionValue(
            'transportUndertakingAddress',
            new Recursion($this->nsPrefix . 'TransportUndertakingAddress',
                [
                    new NodeAttribute('address', 'address'),
                    new NodeAttribute('postCode', 'postCode'),
                    new NodeAttribute('city', 'city'),
                    new NodeAttribute('country', 'country'),
                ],
            ),
        );
    }
}
