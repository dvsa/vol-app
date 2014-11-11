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
            $id = $this->params('licence');
        }

        /** @var \Olcs\Service\Data\Licence $dataService */
        $dataService = $this->getServiceLocator()->get('Olcs\Service\Data\Licence');
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

        if (!empty($licence['cases'])) {

            $licenceMarkerPlugin = $this->getServiceLocator()
                ->get('Olcs\Service\Marker\MarkerPluginManager')
                ->get('Olcs\Service\Marker\LicenceMarkers');

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
        return $markers;
    }
}
