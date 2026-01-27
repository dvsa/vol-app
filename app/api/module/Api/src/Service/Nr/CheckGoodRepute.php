<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Nr;

use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Entity\Tm\TmQualification;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager;
use Olcs\Logging\Log\Logger;

class CheckGoodRepute extends AbstractXmlRequest
{
    public const MSG_FORBIDDEN_NO_ADDRESS = 'No Tm address information available for repute check';

    public function create(TransportManager $transportManager): string
    {
        //must have address information as a minimum
        if (!$transportManager->hasReputeCheckAddress()) {
            throw new ForbiddenException(self::MSG_FORBIDDEN_NO_ADDRESS);
        }

        $xmlData = [
            'Header' => $this->getHeader($this->generateGuid(), 'ZZ'),
            'Body' => $this->getBody($transportManager)
        ];

        Logger::debug('Repute check xml data', $xmlData);

        $this->xmlBuilder->setData($xmlData);
        return $this->xmlBuilder->buildTemplate();
    }

    private function getBody(TransportManager $transportManager): array
    {
        return [
            'name' => 'Body',
            'attributes' => [
                'businessCaseId' => $this->generateGuid(),
                'originatingAuthority' => $this->authority,
                'requestPurpose' => 'Other',
                'requestSource' => 'Other',
            ],
            'nodes' => $this->getTransportManagerDetails($transportManager),
        ];
    }

    private function getTransportManagerDetails(TransportManager $transportManager): array
    {
        /** @var TmQualification $qualification */
        $qualification = $transportManager->getMostRecentQualification()->current();
        $countryCode = $qualification->getCountryCode()->getId();
        $person = $transportManager->getHomeCd()->getPerson();

        return [
            0 => ['name' => 'SearchedTransportManager',
            'nodes' => [
                0 => [
                    'name' => 'TransportManagerNameDetails',
                    'attributes' => [
                        'familyName' => $person->getFamilyName(),
                        'firstName' => $person->getForename(),
                        'dateOfBirth' => date('d/m/Y', strtotime((string) $person->getBirthDate())),
                        'placeOfBirth' => $person->getBirthPlace(),
                    ],
                ],
                1 => [
                    'name' => 'TransportManagerCertificateDetails',
                    'attributes' => [
                        'certificateNumber' => $qualification->getSerialNo() ?? 'Unknown',
                        'certificateIssueDate' => date('d/m/Y', strtotime((string) $qualification->getIssuedDate())),
                        'certificateIssueCountry' => $countryCode === 'GB' ? 'UK' : $countryCode,
                    ],
                ],
            ],
        ]];
    }
}
