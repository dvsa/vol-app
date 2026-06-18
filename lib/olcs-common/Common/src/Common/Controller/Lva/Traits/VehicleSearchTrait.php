<?php

declare(strict_types=1);

namespace Common\Controller\Lva\Traits;

use Common\FormService\FormServiceManager;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableBuilder;
use Laminas\Mvc\Controller\AbstractController;
use Laminas\Mvc\Controller\Plugin\Redirect;

/**
 * @property FormServiceManager $formServiceManager
 * @property TranslationHelperService $translationHelper
 * @method AbstractController getRequest()
 * @method Redirect redirect()
 */
trait VehicleSearchTrait
{
    /**
     * Show removed vehicles action
     *
     * @return mixed
     */
    public function showRemovedVehiclesAction()
    {
        return $this->redirect()->toRouteAjax(
            null,
            ['action' => 'index'],
            ['query' => ['includeRemoved' => 1]],
            true
        );
    }

    /**
     * Hide removed vehicles action
     *
     * @return mixed
     */
    public function hideRemovedVehiclesAction()
    {
        return $this->redirect()->toRouteAjax(
            null,
            ['action' => 'index'],
            ['query' => []],
            true
        );
    }

    /**
     * Get vehicle search form
     *
     * @param array $headerData Data from Api
     *
     * @return \Laminas\Form\FormInterface|null
     */
    protected function getVehcileSearchForm($headerData)
    {
        $searchForm = null;
        if (($headerData['allVehicleCount'] > self::SEARCH_VEHICLES_COUNT) && ($this->lva !== 'application')) {
            /** @var \Laminas\Form\FormInterface $searchForm */
            $searchForm = $this->formServiceManager
                ->get('lva-vehicles-search')
                ->getForm();

            $query = (array)$this->getRequest()->getQuery();

            if (!isset($query['limit']) || !is_numeric($query['limit'])) {
                $query['limit'] = 10;
            }

            if (isset($query['vehicleSearch']['clearSearch'])) {
                unset($query['vehicleSearch']);
            }

            $searchForm->setData($query);
            if (isset($query['vehicleSearch']['filter']) && !$searchForm->isValid()) {
                $message = [
                    'vehicleSearch' => [
                        'vrm' => [$this->translationHelper->translate('vehicle-table.search.message')]
                    ]
                ];
                $searchForm->setMessages($message);
            }

            $searchForm->setAttribute('action', '');
        }

        return $searchForm;
    }

    /**
     * Remove unused parameters from query
     *
     * @param array $query Query
     *
     * @return array
     */
    protected function removeUnusedParametersFromQuery($query)
    {
        if (
            (isset($query['vehicleSearch']['filter']) && empty($query['vehicleSearch']['vrm'])) ||
            isset($query['vehicleSearch']['clearSearch'])
        ) {
            $query['vehicleSearch'] = null;
            unset($query['vehicleSearch']);
        }

        if (isset($query['includeRemoved']) && !$query['includeRemoved']) {
            unset($query['includeRemoved']);
        }

        return $query;
    }

    /**
     * Add removed vehicles actions
     *
     * @param array        $filters Query parameters
     * @param TableBuilder $table   Table builder object
     *
     * @return void
     */
    protected function addRemovedVehiclesActions($filters, TableBuilder $table)
    {
        if (isset($filters['includeRemoved']) && $filters['includeRemoved'] == '1') {
            $table->addAction(
                'hide-removed-vehicles',
                [
                    'label' => 'label-hide-removed-vehciles',
                    'requireRows' => true,
                    'keepForReadOnly' => true,
                ]
            );
        } else {
            $table->addAction(
                'show-removed-vehicles',
                [
                    'label' => 'label-show-removed-vehciles',
                    'requireRows' => false,
                    'keepForReadOnly' => true,
                ]
            );
        }
    }
}
