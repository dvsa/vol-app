<?php

/**
 * Licence Controller Trait
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Traits;

use Olcs\Helper\LicenceDetailsHelper;

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
    public function getViewWithLicence($variables = array())
    {
        $licence = $this->getLicence();

        if ($licence['goodsOrPsv']['id'] == LicenceDetailsHelper::GOODS_OR_PSV_GOODS) {
            $this->getServiceLocator()->get('Navigation')->findOneBy('id', 'licence_bus')->setVisible(0);
        }

        $variables['licence'] = $licence;

        $view = $this->getView($variables);

        $this->pageTitle = $view->licence['licNo'];
        $this->pageSubTitle = $view->licence['goodsOrPsv']['description'] . ', ' .
            $view->licence['licenceType']['description']
            . ', ' . $view->licence['status']['description'];

        return $view;
    }

    public function getTranslator()
    {
        return $this->getServiceLocator()->get('translator');
    }

    /**
     * Gets the licence by ID.
     *
     * @param integer $id
     * @return array
     */
    public function getLicence($id = null)
    {
        if (is_null($id)) {
            $id = $this->getFromRoute('licence');
        }

        /** @var \Olcs\Service\Data\Licence $dataService */
        $dataService = $this->getServiceLocator()->get('Olcs\Service\Data\Licence');
        return $dataService->fetchLicenceData($id);
    }
}
