<?php

/**
 * Licence Controller Trait
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Traits;

use Common\Service\Entity\LicenceEntityService;

/**
 * Licence Controller Trait
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
trait LicenceControllerTrait
{
    /**
     * Get view with licence
     *
     * @param array $variables
     * @return \Zend\View\Model\ViewModel
     */
    protected function getViewWithLicence($variables = array())
    {
        $licence = $this->getLicence();
        if ($licence['goodsOrPsv']['id'] == LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE) {
            $this->getServiceLocator()->get('Navigation')->findOneBy('id', 'licence_bus')->setVisible(0);
        }

        $variables['licence'] = $licence;
        $variables['markers'] = $this->setupMarkers($licence);

        $view = $this->getView($variables);

        $this->pageTitle = $licence['licNo'];
        $this->pageSubTitle = $licence['organisation']['name']
            . ' ' . $licence['status']['description'];

        return $view;
    }

    /**
     * Gets the licence by ID.
     *
     * @param integer $id
     * @return array
     */
    protected function getLicence($id = null)
    {
        if (is_null($id)) {
            $id = $this->params()->fromRoute('licence');
        }

        /** @var \Common\Service\Data\Licence $dataService */
        $dataService = $this->getServiceLocator()->get('Common\Service\Data\Licence');
        return $dataService->fetchLicenceData($id);
    }

    /**
     * Gets markers for the licence. Calls CaseMarkers plugin to generate markers and return as placeholder
     *
     * @param array $licence
     * @return array $markers
     */
    public function setupMarkers($licence)
    {
        $markers = [];

        $licenceMarkerPlugin = $this->getServiceLocator()
                ->get('Olcs\Service\Marker\MarkerPluginManager')
                ->get('Olcs\Service\Marker\LicenceMarkers');

        if (!empty($licence['cases'])) {
            foreach ($licence['cases'] as $case) {

                $caseMarkers = $licenceMarkerPlugin->generateMarkerTypes(
                    ['appeal', 'stay'],
                    [
                        'case' => $case,
                        'licence' => $licence
                    ]
                );
                $markers[] = $caseMarkers;
            }
        }

        $licenceMarkerData = $this->getLicenceMarkerData($licence['id']);
        $markers[] = $licenceMarkerPlugin->generateMarkerTypes(
            ['status', 'statusRule', 'continuation'],
            [
                'licence' => $licence,
                'licenceStatusRule' => $this->getLicenceStatusRule($licence['id']),
                'continuationDetails' => $licenceMarkerData['continuationMarker']
            ]
        );

        return $markers;
    }

    /**
     * Get the data required for displaying licence markers
     *
     * @param int $licenceId
     *
     * @return array
     * @throws \RuntimeException
     */
    protected function getLicenceMarkerData($licenceId)
    {
        $response = $this->handleQuery(
            \Dvsa\Olcs\Transfer\Query\Licence\Markers::create(['id' => $licenceId])
        );
        if (!$response->isOk()) {
            throw new \RuntimeException('Error getting licence markers');
        }

        return $response->getResult();
    }

    protected function getLicenceStatusRule($licenceId)
    {
        $rules = $this->getServiceLocator()->get('Helper\LicenceStatus')
            ->getCurrentOrPendingRulesForLicence($licenceId);

        if ($rules) {
            return array_shift($rules);
        }

        return null;
    }
}
