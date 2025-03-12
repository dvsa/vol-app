<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Nr;

use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager;

class CheckGoodRepute extends AbstractXmlRequest
{
    public function create(TransportManager $transportManager): string
    {
        //must have address information as a minimum
        if (!$transportManager->hasReputeCheckAddress()) {
            throw new ForbiddenException('No Tm address information available for repute check');
        }

        $xmlData = [
            'Header' => $this->getHeader($this->generateGuid(), 'ZZ'),
            'Body' => $this->getBody($transportManager)
        ];

        $this->xmlBuilder->setData($xmlData);
        return $this->xmlBuilder->buildTemplate();
    }

    /**
     * Fetches array of information for the xml body
     */
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
            'nodes' => [
                0 => [
                    'name' => 'SearchedTransportManager',
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
            ],
        ];
    }

    private function getTransportManagerDetails(TransportManager $transportManager): array
    {

    }
}
