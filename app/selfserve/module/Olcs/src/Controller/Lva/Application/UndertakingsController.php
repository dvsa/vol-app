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
        $goodsOrPsv  = $applicationData['goodsOrPsv']['id'];

        $output = array(
            'declarationsAndUndertakings' => array(
                'version' => $applicationData['version'],
                'id' => $applicationData['id'],
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

        $goodsOrPsv  = $applicationData['goodsOrPsv']['id'];

        if (!($goodsOrPsv === Licence::LICENCE_CATEGORY_GOODS_VEHICLE)) {
            $this->getServiceLocator()->get('Helper\Form')->remove($form, 'interim');
        }
    }
}
