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

    public function indexAction()
    {
        $this->getServiceLocator()->get('Script')->loadFile('undertakings');

        return parent::indexAction();
    }

    protected function getForm()
    {
        return $this->getServiceLocator()->get('Helper\Form')
            ->createForm('Lva\VariationUndertakings');
    }

    protected function formatDataForForm($applicationData)
    {
        $licenceType = $applicationData['licenceType']['id'];
        $goodsOrPsv  = $applicationData['goodsOrPsv']['id'];
        $niFlag      = $applicationData['niFlag'];
        $isUpgrade   = $applicationData['isLicenceUpgrade'];

        $output = array(
            'declarationsAndUndertakings' => array(
                'declarationConfirmation' => $applicationData['declarationConfirmation'],
                'version' => $applicationData['version'],
                'id' => $applicationData['id'],
                'undertakings' => $this->getUndertakingsPartial($goodsOrPsv, $licenceType, $niFlag, $isUpgrade),
                'additionalUndertakings' => $this->getAdditionalUndertakingsPartial(
                    $goodsOrPsv,
                    $licenceType,
                    $niFlag,
                    $isUpgrade
                ),
            ),
        );

        if ($goodsOrPsv === Licence::LICENCE_CATEGORY_GOODS_VEHICLE) {
            $interim = array();
            if (!is_null($applicationData['interimReason'])) {
                $interim['goodsApplicationInterim'] = "Y";
                $interim['goodsApplicationInterimReason'] = $applicationData['interimReason'];
            }

            $output['interim'] = $interim;
        }

        return $output;
    }

    protected function updateForm($form, $applicationData)
    {
        parent::updateForm($form, $applicationData);

        if (!$applicationData['canHaveInterimLicence']) {
            $this->getServiceLocator()->get('Helper\Form')->remove($form, 'interim');
        }

        if ($applicationData['isLicenceUpgrade']) {
             // override label
            $form->get('declarationsAndUndertakings')
                ->get('declarationConfirmation')
                ->setLabel('variation.review-declarations.confirm-text-upgrade');
        }
    }

    /**
     * Determine correct partial to use for undertakings html
     *
     * Valid partials are:
     *  gv81-standard, gv81-restricted,
     *  gvni81-standard, gvni81-restricted,
     *  gv80a, gvni80a
     *  psv430-431-standard, psv430-431-restricted
     *
     * (public for unit testing)
     *
     * @param string $goodsOrPsv
     * @param string $licenceType
     * @param string $niFlag
     * @param boolean $isUpgrade
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
     * Determine correct partial to use for additional undertakings html
     *
     * Valid partials are:
     *  gv80a, gvni80a
     *
     * (public for unit testing)
     *
     * @param string $goodsOrPsv
     * @param string $licenceType
     * @param string $niFlag
     * @param boolean $isUpgrade
     * @return string
     */
    public function getAdditionalUndertakingsPartial($goodsOrPsv, $licenceType, $niFlag, $isUpgrade)
    {
        if (!$isUpgrade || $goodsOrPsv !== Licence::LICENCE_CATEGORY_GOODS_VEHICLE) {
            return;
        }

        $part = 'gv' . ($niFlag == 'Y' ? 'ni' : '') . '80a';

        return 'markup-additional-undertakings-' . $part;
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
