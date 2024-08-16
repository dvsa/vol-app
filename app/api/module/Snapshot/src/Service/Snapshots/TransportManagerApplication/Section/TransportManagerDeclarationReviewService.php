<?php

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\TransportManagerApplication\Section;

use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication;

/**
 * Transport Manager Responsibility Review Service
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class TransportManagerDeclarationReviewService extends AbstractReviewService
{
    /**
     * Format the readonly config from the given data
     *
     * @param TransportManagerApplication $tma
     *
     * @return array
     */
    public function getConfig(TransportManagerApplication $tma)
    {
        $markup = 'markup-tma-declaration';

        // append flag for external/internal TM
        if ($tma->isTypeInternal()) {
            $markup .= '-internal';
        } else {
            $markup .= '-external';
        }

        $application = $tma->getApplication();

        // append flag for ni/gb
        if ($application->getNiFlag() === 'Y') {
            $markup .= '-ni';
        } else {
            $markup .= '-gb';
        }

        $goodsOrPsvId = $application->getGoodsOrPsv()->getId();

        $markup = $this->translateReplace(
            $markup,
            [
                $this->translate('tma-declaration.residency-clause.' . $goodsOrPsvId),
                $this->translate('tma-declaration.role-clause.' . $goodsOrPsvId)
            ]
        );

        return ['markup' => $markup];
    }
}
