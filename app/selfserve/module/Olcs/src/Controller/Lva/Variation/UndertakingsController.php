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
        $goodsOrPsv  = $applicationData['goodsOrPsv']['id'];

        $output = array(
            'declarationsAndUndertakings' => array(
                'version' => $applicationData['version'],
                'id' => $applicationData['id'],
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
    }
}
