<?php

/**
 * External Variation Undertakings Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Variation;

use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;
use Common\Service\Entity\LicenceEntityService as Licence;

/**
 * External Variation Undertakings Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class UndertakingsController extends Lva\AbstractUndertakingsController
{
    use VariationControllerTrait;

    protected $lva = 'variation';
    protected $location = 'external';

    protected function getForm(){
        return $this->getServiceLocator()->get('Helper\Form')
            ->createForm('Lva\VariationUndertakings');
    }

    protected function formatDataForForm($applicationData)
    {
        $licenceType = $applicationData['licenceType']['id'];
        $goodsOrPsv  = $applicationData['goodsOrPsv']['id'];
        $niFlag      = $applicationData['niFlag'];
        $isUpgrade   = $this->isUpgrade($applicationData['id']);

        $formData = [
            'declarationConfirmation' => $applicationData['declarationConfirmation'],
            'version' => $applicationData['version'],
            'id' => $applicationData['id'],
            'undertakings' => $this->getUndertakingsPartial($goodsOrPsv, $licenceType, $niFlag, $isUpgrade),
        ];

        return ['declarationsAndUndertakings' => $formData];
    }

    protected function updateForm($form, $applicationData)
    {
        return;
    }

    protected function isUpgrade($applicationId)
    {
        return $this->getServiceLocator()->get('Processing\VariationSection')
            ->isLicenceUpgrade($applicationId);
    }

    /**
     * Determine correct partial to use for undertakings html
     *
     * (public for unit testing)
     *
     * @param string $goodsOrPsv
     * @param string $licenceType
     * @return string
     */
    public function getUndertakingsPartial($goodsOrPsv, $licenceType, $niFlag, $isUpgrade)
    {
        $part   = '';

        $part = $this->getPartialPrefix($goodsOrPsv);

        if ($niFlag == 'Y') {
            $part .= 'ni';
        }

        $part .= $this->getSuffix($goodsOrPsv, $isUpgrade);

        if (!$isUpgrade) {
            $nonRestricted = [
                Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
            ];
            if (in_array($licenceType, $nonRestricted)) {
                $part .= '-standard';
            } else {
                $part .= '-restricted';
            }
        }

        return 'markup-undertakings-' . $part;
    }

    /**
     * @param string $goodsOrPsv
     * @param boolean $isUpgrade
     * @return string
     */
    protected function getSuffix($goodsOrPsv, $isUpgrade)
    {
        if ($goodsOrPsv === Licence::LICENCE_CATEGORY_PSV) {
            return '430-431';
        }

        if ($isUpgrade) {
            return '80a';
        }
        return '81';
    }
}
