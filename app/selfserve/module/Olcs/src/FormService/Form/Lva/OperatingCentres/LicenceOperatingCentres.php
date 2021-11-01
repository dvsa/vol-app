<?php

namespace Olcs\FormService\Form\Lva\OperatingCentres;

use Common\FormService\Form\Lva\OperatingCentres\LicenceOperatingCentres as CommonLicenceOperatingCentres;
use Common\Service\Table\TableBuilder;
use Laminas\Form\Form;

/**
 * @see \OlcsTest\FormService\Form\Lva\OperatingCentres\LicenceOperatingCentresTest
 */
class LicenceOperatingCentres extends CommonLicenceOperatingCentres
{
    protected $mainTableConfigName = 'lva-licence-operating-centres';

    private $lockElements = [
        'totAuthHgvVehiclesFieldset->totAuthHgvVehicles',
        'totAuthLgvVehiclesFieldset->totAuthLgvVehicles',
        'totAuthTrailersFieldset->totAuthTrailers',
    ];

    /**
     * Alter form
     *
     * @param Form  $form   form
     * @param array $params params
     *
     * @return void
     */
    protected function alterForm(Form $form, array $params)
    {
        parent::alterForm($form, $params);

        $dataElement = $form->get('data');

        $this->getFormHelper()->disableElements($dataElement);

        if ($form->has('dataTrafficArea')) {
            $form->get('dataTrafficArea')->remove('enforcementArea');
        }

        foreach ($this->lockElements as $lockElementRef) {
            $lockElementRefComponents = explode('->', $lockElementRef);
            $lockElement = $dataElement;
            foreach ($lockElementRefComponents as $elementRef) {
                if (null === $lockElement) {
                    break;
                }
                $lockElement = $lockElement->has($elementRef) ? $lockElement->get($elementRef) : null;
            }
            if (null !== $lockElement) {
                $this->getFormHelper()->lockElement($lockElement, 'operating-centres-licence-locked');
            }
        }

        $this->removeStandardFormActions($form);

        $table = $form->get('table')->get('table')->getTable();
        $this->alterTableForLgv($table, $params);
    }

    /**
     * Alter the form table in accordance with lgv requirements
     *
     * @param TableBuilder $tableBuilder
     * @param array $params
     */
    private function alterTableForLgv(TableBuilder $tableBuilder, array $params)
    {
        if ($params['isEligibleForLgv'] && !is_null($params['totAuthLgvVehicles'])) {
            $columns = $tableBuilder->getColumns();
            $columns['noOfVehiclesRequired']['title'] = 'application_operating-centres_authorisation.table.hgvs';
            $tableBuilder->setColumns($columns);
        }
    }
}
