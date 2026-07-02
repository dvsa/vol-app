<?php

namespace Common\FormService\Form\Lva\OperatingCentres;

use Common\Form\Elements\Validators\TableRequiredValidator;
use Common\FormService\Form\Lva\AbstractLvaFormService;
use Common\RefData;
use Common\Service\Helper\FormHelperService;
use Common\Service\Table\TableBuilder;
use Common\Service\Table\TableFactory;
use Laminas\Form\Form;
use Laminas\Validator\Between;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * @see \CommonTest\FormService\Form\Lva\OperatingCentres\AbstractOperatingCentresTest
 */
abstract class AbstractOperatingCentres extends AbstractLvaFormService
{
    protected $mainTableConfigName = 'lva-operating-centres';

    protected $tableBuilder;

    public function __construct($formHelper)
    {
        $this->formHelper = $formHelper;
    }

    /**
     * Get Form
     *
     * @param array $params Parameters
     *
     * @return Form
     */
    public function getForm($params)
    {
        $form = $this->formHelper->createForm('Lva\OperatingCentres');

        $additionalParams = $params['query'] ?? [];
        $table = $this->tableBuilder->prepareTable($this->mainTableConfigName, $params['operatingCentres'], $additionalParams);

        $this->formHelper->populateFormTable($form->get('table'), $table);
        $this->alterForm($form, $params);

        return $form;
    }

    /**
     * Alter form
     *
     * @param Form  $form   Form
     * @param array $params Parameters
     *
     * @return Form
     */
    protected function alterForm(Form $form, array $params)
    {
        if ($form->has('table')) {
            $table = $form->get('table')->get('table')->getTable();
            if (!$params['canHaveSchedule41']) {
                $table->removeAction('schedule41');
            }

            $this->alterTableForLgv($table, $params);
        }

        if (!$params['canHaveCommunityLicences']) {
            $this->formHelper->remove($form, 'data->totCommunityLicencesFieldset');
        }

        if ($params['isPsv']) {
            $this->alterFormForPsvLicences($form, $params);
            $this->alterFormTableForPsv($form);
        } else {
            $this->alterFormForGoodsLicences($form, $params);
        }

        $rowsInput = $form->getInputFilter()->get('table')->get('rows');
        $validatorChain = $rowsInput->getValidatorChain();

        $validatorExists = array_reduce($validatorChain->getValidators(), static fn($found, $item) => $found || $item['instance'] instanceof TableRequiredValidator, false);

        if (!$validatorExists) {
            $tableRequiredValidator = new TableRequiredValidator(['label' => 'record']);
            $validatorChain->attach($tableRequiredValidator);
            $tableRequiredValidator->setMessage('OperatingCentreNoOfOperatingCentres.required', 'required');
        }

        $this->alterFormForVehicleType($form, $params);

        if ($this->removeTrafficAreaElements($params)) {
            $this->formHelper->remove($form, 'dataTrafficArea');

            return $form;
        }

        $trafficArea = isset($params['licence']) ? $params['licence']['trafficArea'] : $params['trafficArea'];

        $trafficAreaId = $trafficArea ? $trafficArea['id'] : null;

        $dataTrafficAreaFieldset = $form->get('dataTrafficArea');

        // if application/licence is NI then don't show trafficArea help
        if ($params['niFlag'] === 'Y') {
            $form->get('dataTrafficArea')->get('trafficAreaSet')->setOption('hint', null);
        }

        if (empty($trafficAreaId) || $this->allowChangingTrafficArea($trafficAreaId)) {
            $dataTrafficAreaFieldset->get('trafficArea')->setValueOptions($params['possibleTrafficAreas']);
            $dataTrafficAreaFieldset->remove('trafficAreaSet');
        } else {
            $this->formHelper->remove($form, 'dataTrafficArea->trafficArea');
            $dataTrafficAreaFieldset->get('trafficAreaSet')->setValue($trafficArea['name']);
            $dataTrafficAreaInputFilter = $form->getInputFilter()->get('dataTrafficArea');
            foreach ($dataTrafficAreaInputFilter->getInputs() as $input) {
                $input->setRequired(false);
            }
        }

        $dataTrafficAreaFieldset->get('enforcementArea')
            ->setValueOptions($params['possibleEnforcementAreas']);

        return $form;
    }

    /**
     * Can the traffic aread be changed
     *
     * @param int $trafficAreaId Traffic area id
     *
     * @return boolean
     */
    protected function allowChangingTrafficArea($trafficAreaId)
    {
        return false;
    }

    /**
     * Should the Traffic Area elements be removed from the Form
     *
     * @param array $data Data
     *
     * @return bool
     */
    protected function removeTrafficAreaElements($data)
    {
        if (RefData::APP_VEHICLE_TYPE_LGV === $data['vehicleType']['id']) {
            // LGV only - Traffic Area element should not be removed
            return false;
        }

        return empty($data['operatingCentres']);
    }

    /**
     * Alter Form For Psv Licences
     *
     * @param Form  $form   Form
     * @param array $params Parameters
     *
     * @return void
     */
    protected function alterFormForPsvLicences(Form $form, array $params)
    {
        $dataFieldset = $form->get('data');

        if ($dataFieldset->has('totCommunityLicencesFieldset')) {
            $totCommunityLicencesFieldset = $dataFieldset->get('totCommunityLicencesFieldset');
            $totCommunityLicencesFieldset->setLabel('');
            $totCommunityLicencesElement = $totCommunityLicencesFieldset->get('totCommunityLicences');
            $totCommunityLicencesElement->setLabel($totCommunityLicencesElement->getLabel() . '.psv');
            $totCommunityLicencesElement->setOption('hint', null);
        }

        $dataOptions = $dataFieldset->getOptions();
        if (isset($dataOptions['hint'])) {
            $dataOptions['hint'] .= isset($dataOptions['hint']) ? '.psv' : '';
        }

        $dataFieldset->setOptions($dataOptions);

        $removeFields = [
            'totAuthTrailersFieldset'
        ];

        $this->formHelper->removeFieldList($form, 'data', $removeFields);
    }

    /**
     * Alter Form Table For Psv
     *
     * @param Form $form Form
     *
     * @return void
     */
    protected function alterFormTableForPsv(Form $form)
    {
        $table = $form->get('table')->get('table')->getTable();
        assert($table instanceof TableBuilder);

        $table->removeColumn('noOfTrailersRequired');

        $footer = $table->getFooter();
        if (isset($footer['total']['content'])) {
            $footer['total']['content'] .= '-psv';
            unset($footer['trailersCol']);
            $table->setFooter($footer);
        }
    }

    /**
     * Alter Form For Goods Licences
     */
    protected function alterFormForGoodsLicences(Form $form, array $params): void
    {
    }

    /**
     * @param $form
     */
    protected function disableVehicleClassifications($form): void
    {
        $this->formHelper->remove($form, 'data->totAuthLgvVehiclesFieldset');
        $totAuthHgvVehiclesFieldset = $form->get('data')->get('totAuthHgvVehiclesFieldset');
        $totAuthHgvVehiclesFieldset->setLabel('application_operating-centres_authorisation.data.totAuthHgvVehiclesFieldset.vehicles-label');
        $totAuthHgvVehiclesFieldset->get('totAuthHgvVehicles')->setLabel('application_operating-centres_authorisation.data.totAuthHgvVehicles.vehicles-label');
    }

    /**
     * Alter form for vehicle type
     *
     *
     */
    protected function alterFormForVehicleType(Form $form, array $params): void
    {
        switch ($params['vehicleType']['id']) {
            case RefData::APP_VEHICLE_TYPE_LGV:
                // remove operating centres table
                $this->formHelper->remove($form, 'table');

                // remove HGV/PSV specific fields
                $this->formHelper->remove($form, 'data->totAuthHgvVehiclesFieldset');
                $this->formHelper->remove($form, 'data->totAuthTrailersFieldset');

                // modify validators
                // LGV between validator
                $lgvBetweenValidator = $this->formHelper->getValidator(
                    $form,
                    'data->totAuthLgvVehiclesFieldset->totAuthLgvVehicles',
                    Between::class
                );
                if ($lgvBetweenValidator instanceof Between) {
                    // at least 1 is required for LGV only
                    $lgvBetweenValidator->setMin(1);
                }

                // Community Licence between validator
                $comLicBetweenValidator = $this->formHelper->getValidator(
                    $form,
                    'data->totCommunityLicencesFieldset->totCommunityLicences',
                    Between::class
                );
                if (($comLicBetweenValidator instanceof Between) && ($lgvBetweenValidator instanceof Between)) {
                    // set max to the same as what LGV field is set to
                    $comLicBetweenValidator->setMax($lgvBetweenValidator->getMax());
                }

                break;
            case RefData::APP_VEHICLE_TYPE_HGV:
            case RefData::APP_VEHICLE_TYPE_PSV:
                // disable vehicle classifications
                $this->disableVehicleClassifications($form);
                break;
            case RefData::APP_VEHICLE_TYPE_MIXED:
            default:
                // no changes required to the form
                break;
        }
    }

    /**
     * Alter the table in accordance with lgv requirements
     */
    private function alterTableForLgv(TableBuilder $tableBuilder, array $params): void
    {
        $isMixedWithLgv = ($params['vehicleType']['id'] === RefData::APP_VEHICLE_TYPE_MIXED) && ($params['totAuthLgvVehicles'] !== null);

        if ($isMixedWithLgv) {
            $columns = $tableBuilder->getColumns();
            $columns['noOfVehiclesRequired']['title'] = 'application_operating-centres_authorisation.table.hgvs';
            $tableBuilder->setColumns($columns);
        }
    }
}
