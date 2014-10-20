<?php

/**
 * Operating Centres Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Application;

use Common\Controller\Traits\Lva;
use Common\Service\Entity\LicenceEntityService;
use Zend\Form\Form;

/**
 * Operating Centres Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OperatingCentresController extends AbstractApplicationController
{
    use Lva\OperatingCentresTrait;

    /**
     * Remove trailer elements for PSV and set up Traffic Area section
     *
     * @param \Zend\Form\Form $form
     * @return \Zend\Form\Form
     */
    protected function alterForm(Form $form)
    {
        // Make the same form alterations that are required for the summary section
        $form = $this->makeFormAlterations($form, $this->getAlterFormOptions());

        $tableData = $this->getTableData();

        if (empty($tableData)) {
            $form->remove('dataTrafficArea');
            return $form;
        }

        $trafficArea = $this->getTrafficArea();
        $trafficAreaId = $trafficArea ? $trafficArea['id'] : '';

        $dataTrafficAreaFieldset = $form->get('dataTrafficArea');

        if ($trafficAreaId) {

            $nameExistsElement = $dataTrafficAreaFieldset->remove('trafficArea')->get('trafficAreaInfoNameExists');

            $nameExistsElement->setValue(
                str_replace('%NAME%', $trafficArea['name'], $nameExistsElement->getValue())
            );
            return $form;
        }
        $options = $this->getServiceLocator()
            ->get('Entity\TrafficArea')->getTrafficAreaValueOptions();

        $dataTrafficAreaFieldset->remove('trafficAreaInfoLabelExists')
            ->remove('trafficAreaInfoNameExists')
            ->remove('trafficAreaInfoHintExists')
            ->get('trafficArea')
            ->setValueOptions($options);

        return $form;
    }

    protected function isPsv()
    {
        $data = $this->getTypeOfLicenceData();
        return isset($data['goodsOrPsv']) && $data['goodsOrPsv'] === LicenceEntityService::LICENCE_CATEGORY_PSV;
    }

    protected function getIdentifier()
    {
        return $this->getApplicationId();
    }
}
