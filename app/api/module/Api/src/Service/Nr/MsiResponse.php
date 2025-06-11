<?php

namespace Dvsa\Olcs\Api\Service\Nr;

use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime as DateTimeExtended;
use Dvsa\Olcs\Api\Entity\Si\SeriousInfringement as SiEntity;
use Dvsa\Olcs\Api\Entity\Si\SiPenalty as SiPenaltyEntity;
use Dvsa\Olcs\Api\Entity\Si\ErruRequest as ErruRequestEntity;
use Doctrine\Common\Collections\Collection as CollectionInterface;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Olcs\XmlTools\Xml\XmlNodeBuilder;

class MsiResponse
{
    public const AUTHORITY_TRU = 'Transport Regulation Unit';
    public const AUTHORITY_TC = 'Traffic Commissioner';

    /**
     * @var string $responseDateTime
     */
    private string $responseDateTime = '';

    private string $timeoutDateTime = '';

    /**
     * @var String
     */
    private $technicalId;

    /**
     * @var String
     */
    private $authority;

    /**
     * MsiResponse constructor
     *
     * @param XmlNodeBuilder $xmlBuilder xml node builder
     *
     * @return MsiResponse
     */
    public function __construct(private readonly XmlNodeBuilder $xmlBuilder, private readonly string $erruVersion)
    {
    }

    public function getXmlBuilder(): XmlNodeBuilder
    {
        return $this->xmlBuilder;
    }

    public function getResponseDateTime(): string
    {
        return $this->responseDateTime;
    }

    public function setResponseDateTime(string $responseDateTime): void
    {
        $this->responseDateTime = $responseDateTime;
    }

    public function setTimeoutDateTime(string $timeoutDateTime): void
    {
        $this->timeoutDateTime = $timeoutDateTime;
    }

    public function getTimeoutDateTime(): string
    {
        return $this->timeoutDateTime;
    }

    /**
     * Gets the technical id
     *
     * @return String
     */
    public function getTechnicalId()
    {
        return $this->technicalId;
    }

    /**
     * Sets the technical id
     *
     * @param String $technicalId technical id
     *
     * @return void
     */
    public function setTechnicalId($technicalId)
    {
        $this->technicalId = $technicalId;
    }

    /**
     * Gets the originating authority
     *
     * @return String
     */
    public function getAuthority()
    {
        return $this->authority;
    }

    /**
     * Sets the originating authority
     *
     * @param String $authority originating authority
     *
     * @return void
     */
    public function setAuthority($authority)
    {
        $this->authority = $authority;
    }

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

        $this->setTechnicalId($this->generateGuid());
        $dateTime = new DateTimeExtended();
        $this->setResponseDateTime($dateTime->format(\DateTime::ATOM));
        $this->setTimeoutDateTime($dateTime->add(new \DateInterval('PT10S'))->format(\DateTime::ATOM));

        if ($case->getLicence() === null) {
            $this->setAuthority(self::AUTHORITY_TRU);
        } else {
            $this->setAuthority(self::AUTHORITY_TC);
        }

        $erruRequest = $case->getErruRequest();

        $xmlData = [
            'Header' => $this->getHeader($erruRequest),
            'Body' => $this->getBody($case, $erruRequest)
        ];

        $this->xmlBuilder->setData($xmlData);
        return $this->xmlBuilder->buildTemplate();
    }

    /**
     * Fetches array of header information for the XML
     */
    private function getHeader(ErruRequestEntity $erruRequest): array
    {
        //if member state was GB, we need to make this UK
        $memberStateCode = $erruRequest->getMemberStateCode()->getId();
        $filteredMemberStateCode = ($memberStateCode === 'GB' ? 'UK' : $memberStateCode);

        return [
            'name' => 'Header',
            'attributes' => [
                'version' => $this->erruVersion,
                'technicalId' => $this->getTechnicalId(),
                'workflowId' => $erruRequest->getWorkflowId(),
                'sentAt' => $this->getResponseDateTime(),
                'timeoutValue' => $this->timeoutDateTime,
                'from' => 'UK',
                'to' => $filteredMemberStateCode
            ],
        ];
    }

    /**
     * Fetches array of information for the xml body
     */
    private function getBody(CasesEntity $cases, ErruRequestEntity $erruRequest): array
    {
        return [
            'name' => 'Body',
            'attributes' => [
                'businessCaseId' => $erruRequest->getNotificationNumber(),
                'originatingAuthority' => $erruRequest->getOriginatingAuthority(),
                'respondingAuthority' => $this->getAuthority(),
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
                                'address' => 'address',
                                'postcode' => 'postcode',
                                'city' => 'city',
                                'country' => 'UK',
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
                $newPenalty['authorityImposingPenalty'] = $this->getAuthority();
                $newPenalty['penaltyTypeImposed'] = $penalty->getSiPenaltyType()->getId();

                if ($penalty->getImposed() === 'N') {
                    $newPenalty['isImposed'] = 'false';
                    $newPenalty['reasonNotImposed'] = $penalty->getReasonNotImposed();
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

    private function generateGuid(): string
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
