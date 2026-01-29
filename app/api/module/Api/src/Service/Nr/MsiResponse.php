<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Nr;

use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Si\SeriousInfringement as SiEntity;
use Dvsa\Olcs\Api\Entity\Si\SiPenalty as SiPenaltyEntity;
use Dvsa\Olcs\Api\Entity\Si\ErruRequest as ErruRequestEntity;
use Doctrine\Common\Collections\Collection as CollectionInterface;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;

class MsiResponse extends AbstractXmlRequest
{
    public const AUTHORITY_TRU = 'Transport Regulation Unit';
    public const AUTHORITY_TC = 'Traffic Commissioner';

    /**
     * Creates the Msi response, returns xml string
     *
     * @throws ForbiddenException
     */
    public function create(CasesEntity $case): string
    {
        if (!$case->canSendMsiResponse()) {
            throw new ForbiddenException('Unable to send Msi Response');
        }

        $licence = $case->getLicence();

        if ($licence instanceof Licence) {
            $contactDetails = $licence->getContactAddress();
            $this->authority = self::AUTHORITY_TC;
        } else {
            $contactDetails = null;
            $this->authority = self::AUTHORITY_TRU;
        }

        $erruRequest = $case->getErruRequest();

        $xmlData = [
            'Header' => $this->getHeader($erruRequest->getWorkflowId(), 'EU'),
            'Body' => $this->getBody($case, $erruRequest, $contactDetails)
        ];

        $this->xmlBuilder->setData($xmlData);
        return $this->xmlBuilder->buildTemplate();
    }

    /**
     * Fetches array of information for the xml body
     */
    private function getBody(CasesEntity $cases, ErruRequestEntity $erruRequest, ?ContactDetails $contactDetails): array
    {
        $address = 'unknown';
        $postCode = 'unknown';
        $city = 'unknown';
        $country = 'UK';

        if ($contactDetails instanceof ContactDetails) {
            $addressInfo = $contactDetails->getAddress()->toArray();

            $address = $addressInfo['addressLine1'] ?? 'unknown';
            $postCode = $addressInfo['postcode'] ?? 'unknown';
            $city = $addressInfo['town'] ?? 'unknown';
            $country = $addressInfo['country'] ?? 'UK';
        }

        return [
            'name' => 'Body',
            'attributes' => [
                'businessCaseId' => $erruRequest->getNotificationNumber(),
                'originatingAuthority' => $erruRequest->getOriginatingAuthority(),
                'respondingAuthority' => $this->authority,
                'statusCode' => 'OK',
            ],
            'nodes' => [
                0 => [
                    'name' => 'TransportUndertaking',
                    'attributes' => [
                        'transportUndertakingName' => $erruRequest->getTransportUndertakingName(),
                        'communityLicenceNumber' => $erruRequest->getCommunityLicenceNumber(),
                        'communityLicenceStatus' => $erruRequest->getCommunityLicenceStatus()->getDescription(),
                        'numberOfVehicles' => $erruRequest->getTotAuthVehicles(),
                    ],
                    'nodes' => [
                        0 => [
                            'name' => 'TransportUndertakingAddress',
                            'attributes' => [
                                'address' => $address,
                                'postCode' => $postCode,
                                'city' => $city,
                                'country' => $country,
                            ],
                        ],
                    ],
                ],
                1 => [
                    'name' => 'PenaltiesImposed',
                    'nodes' => $this->formatPenalties($cases->getSeriousInfringements()),
                ]
            ]
        ];
    }

    /**
     * Formats penalty information into something usable by xml node builder
     */
    private function formatPenalties(CollectionInterface $seriousInfringements): array
    {
        $formattedPenalties = [];

        /**
         * @var SiPenaltyEntity $penalty
         * @var SiEntity $si
         */
        foreach ($seriousInfringements as $si) {
            $penalties = $si->getAppliedPenalties();

            foreach ($penalties as $penalty) {
                $newPenalty = [];
                $newPenalty['authorityImposingPenalty'] = $this->authority;
                $newPenalty['penaltyTypeImposed'] = $penalty->getSiPenaltyType()->getId();
                $newPenalty['penaltyImposedIdentifier'] = $penalty->getSiPenaltyErruRequested()->getPenaltyRequestedIdentifier();

                if ($penalty->getImposed() === 'N') {
                    $newPenalty['isImposed'] = 'false';
                    $newPenalty['reason'] = $penalty->getReasonNotImposed();
                } else {
                    $newPenalty['isImposed'] = 'true';
                }

                $startDate = $penalty->getStartDate();
                $endDate = $penalty->getEndDate();

                if ($startDate) {
                    $newPenalty['startDate'] = $startDate;
                }

                if ($endDate) {
                    $newPenalty['endDate'] = $endDate;
                }

                $formattedPenalties[] = [
                    'name' => 'PenaltyImposed',
                    'attributes' => $newPenalty
                ];
            }
        }

        return $formattedPenalties;
    }
}
