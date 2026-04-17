<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\TmReputeCheck;

use Dvsa\Olcs\Snapshot\Service\Snapshots\AbstractGenerator;

class Generator extends AbstractGenerator
{
    public const STATUS_FOUND = 'Found';

    public function generate(array $data): string
    {
        $foundCountries = [];
        $notFoundCountries = [];

        foreach ($data['memberStateResponses'] as $memberResponse) {
            if ($memberResponse['statusCode'] === self::STATUS_FOUND) {
                $foundCountries[] = $this->formatMemberStateData($memberResponse);
            } else {
                $notFoundCountries[] = $memberResponse['memberStateCode'];
            }
        }

        $config = [
            'tmName' => $this->formatTmName($data),
            'searchDetails' => $this->formatSearchDetails($data),
            'foundCountries' => $foundCountries,
            'notFoundCountries' => implode(', ', $notFoundCountries),
        ];

        return $this->generateReadonly($config, 'check-repute-response');
    }

    private function formatTmName(array $response): string
    {
        $nameDetails = $response['searchedTransportManager']['transportManagerNameDetails'] ?? [];
        $firstName = $nameDetails['firstName'] ?? '';
        $familyName = $nameDetails['familyName'] ?? '';
        return $firstName . ' ' . $familyName;
    }

    private function formatSearchDetails(array $response): array
    {
        $nameDetails = $response['searchedTransportManager']['transportManagerNameDetails'] ?? [];
        $certDetails = $response['searchedTransportManager']['transportManagerCertificateDetails'] ?? [];

        return [
            'Date and time of check' => $response['sentAt'],
            'Workflow ID' => $response['workflowId'] ?? '',
            'Technical ID' => $response['technicalId'] ?? '',
            'Last name' => $nameDetails['familyName'] ?? '',
            'First name' => $nameDetails['firstName'] ?? '',
            'Date of birth' => $nameDetails['dateOfBirth'] ?? '',
            'Family name search key' => $nameDetails['familyNameSearchKey'] ?? '',
            'First name search key' => $nameDetails['firstNameSearchKey'] ?? '',
            'Certificate number' => $certDetails['certificateNumber'] ?? '',
            'Certificate issue date' => $certDetails['certificateIssueDate'] ?? '',
            'Certificate issue country' => $certDetails['certificateIssueCountry'] ?? '',
        ];
    }

    private function formatMemberStateData(array $memberResponse): array
    {
        $nameDetails = $memberResponse['transportManagerDetails']['transportManagerNameDetails'];
        $addressDetails = $memberResponse['transportManagerDetails']['transportManagerAddressDetails'];
        $certDetails = $memberResponse['transportManagerDetails']['transportManagerCertificateDetails'];
        $undertakingDetails = $memberResponse['transportManagerDetails']['transportUndertakings'];

        return [
            'name' => $memberResponse['memberStateCode'] ?? '',
            'details' => [
                'Responding authority' => $memberResponse['transportManagerDetails']['respondingAuthority'] ?? '',
                'Last name' => $nameDetails['familyName'] ?? '',
                'First name' => $nameDetails['firstName'] ?? '',
                'Date of birth' => $nameDetails['firstName'] ?? '',
                'Place of birth' => $nameDetails['placeOfBirth'] ?? '',
                'Address' => $addressDetails['address'] ?? '',
                'Postcode' => $addressDetails['postCode'] ?? '',
                'City' => $addressDetails['city'] ?? '',
                'Country' => $addressDetails['country'] ?? '',
                'Certificate number' => $certDetails['certificateNumber'] ?? '',
                'Certificate issue date' => $certDetails['certificateIssueDate'] ?? '',
                'Certificate issue country' => $certDetails['certificateIssueCountry'] ?? '',
                'Certificate validity' => $certDetails['certificateValidity'] ?? '',
                'Fitness status' => $certDetails['fitness']['fitnessStatus'] ?? '',
                'Unfit start date' => $certDetails['fitness']['unfitStartDate'] ?? '',
                'Unfit end date' => $certDetails['fitness']['unfitEndDate'] ?? '',
                'Total managed undertakings' => $undertakingDetails['totalManagedUndertakings'] ?? '',
                'Total managed vehicles' => $undertakingDetails['totalManagedVehicles'] ?? '',
            ],
            'undertakings' => $this->formatUndertakings($undertakingDetails['transportUndertaking'] ?? []),
        ];
    }

    private function formatUndertakings(array $undertakings): array
    {
        $formattedUndertakings = [];

        foreach ($undertakings as $undertaking) {
            $undertakingAddress = $undertaking['transportUndertakingAddress'] ?? '';
            $formattedUndertakings[] = [
                'Transport undertaking name' => $undertaking['transportUndertakingName'] ?? '',
                'Number of vehicles' => $undertaking['numberOfVehicles'] ?? '',
                'Community licence number' => $undertaking['communityLicenceNumber'] ?? '',
                'Community licence status' => $undertaking['communityLicenceStatus'] ?? '',
                'Transport undertaking address' => $undertakingAddress['address'] ?? '',
                'Transport undertaking postcode' => $undertakingAddress['postCode'] ?? '',
                'Transport undertaking city' => $undertakingAddress['city'] ?? '',
                'Transport undertaking country' => $undertakingAddress['country'] ?? '',
            ];
        }

        return $formattedUndertakings;
    }
}
