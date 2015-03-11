<?php

/**
 * External Application Undertakings Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Application;

use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use Common\Service\Entity\LicenceEntityService as Licence;

/**
 * External Application Undertakings Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class UndertakingsController extends Lva\AbstractUndertakingsController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected $location = 'external';

    public function indexAction()
    {
        $this->getServiceLocator()->get('Script')->loadFile('undertakings');

        return parent::indexAction();
    }

    protected function getForm()
    {
        return $this->getServiceLocator()->get('Helper\Form')
            ->createForm('Lva\ApplicationUndertakings');
    }

    protected function formatDataForForm($applicationData)
    {
        $licenceType = $applicationData['licenceType']['id'];
        $goodsOrPsv  = $applicationData['goodsOrPsv']['id'];
        $niFlag      = $applicationData['niFlag'];

        $output = array(
            'declarationsAndUndertakings' => array(
                'declarationConfirmation' => $applicationData['declarationConfirmation'],
                'version' => $applicationData['version'],
                'id' => $applicationData['id'],
                'undertakings' => $this->getUndertakingsPartial($goodsOrPsv, $licenceType, $niFlag),
            )
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

        $licenceType = $applicationData['licenceType']['id'];
        $goodsOrPsv  = $applicationData['goodsOrPsv']['id'];

        if (!($goodsOrPsv === Licence::LICENCE_CATEGORY_GOODS_VEHICLE)) {
            $form->remove('interim');
        }

        if ($licenceType === Licence::LICENCE_TYPE_SPECIAL_RESTRICTED
            && $goodsOrPsv === Licence::LICENCE_CATEGORY_PSV
        ) {
            // override label
            $form->get('declarationsAndUndertakings')
                ->get('declarationConfirmation')->setLabel('markup-declarations-psv356');
        }
    }

    /**
     * Determine correct partial to use for undertakings html
     *
     * Valid partials are:
     *  gv79-standard, gv79-restricted,
     *  gvni79-standard, gvni79-restricted,
     *  psv421-standard, psv421-restricted, psv-356
     *
     * (public for unit testing)
     *
     * @param string $goodsOrPsv
     * @param string $licenceType
     * @return string
     */
    public function getUndertakingsPartial($goodsOrPsv, $licenceType, $niFlag)
    {
        $part = $this->getPartialPrefix($goodsOrPsv);
        if ($niFlag == 'Y') {
            $part .= 'ni';
        }

        $part .= $this->getSuffix($goodsOrPsv, $licenceType);

        $nonRestricted = [
            Licence::LICENCE_TYPE_STANDARD_NATIONAL,
            Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL
        ];

        if (in_array($licenceType, $nonRestricted)) {
            $part .= '-standard';
        }

        if ($licenceType === Licence::LICENCE_TYPE_RESTRICTED) {
            $part .= '-restricted';
        }

        return 'markup-undertakings-' . $part;
    }

    protected function getSuffix($goodsOrPsv, $licenceType)
    {
        if ($goodsOrPsv === Licence::LICENCE_CATEGORY_PSV) {
            if ($licenceType === Licence::LICENCE_TYPE_SPECIAL_RESTRICTED) {
                return 356;
            }
            return 421;
        }
        return 79;
    }
}
