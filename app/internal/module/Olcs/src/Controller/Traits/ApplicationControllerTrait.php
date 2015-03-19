<?php

/**
 * Application Controller Trait
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Traits;

use Zend\View\Model\ViewModel;
use Common\Service\Entity\ApplicationEntityService;
use Common\Service\Entity\LicenceEntityService;
use Olcs\View\Model\Application\Layout;
use Olcs\View\Model\Application\ApplicationLayout;

/**
 * Application Controller Trait
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
trait ApplicationControllerTrait
{
    protected function render($view)
    {
        $applicationLayout = new ApplicationLayout();

        $applicationLayout->addChild($view, 'content');

        $params = $this->getHeaderParams();

        return new Layout($applicationLayout, $params);
    }

    /**
     * Get headers params
     *
     * @return array
     */
    protected function getHeaderParams()
    {
        $data = $this->getServiceLocator()->get('Entity\Application')->getHeaderData($this->params('application'));

        return array(
            'applicationId' => $data['id'],
            'licNo' => $data['licence']['licNo'],
            'licenceId' => $data['licence']['id'],
            'companyName' => $data['licence']['organisation']['name'],
            'status' => $data['status']['id']
        );
    }

    /**
     * Gets the application by ID.
     *
     * @param integer $id
     * @return array
     */
    protected function getApplication($id = null)
    {
        if (is_null($id)) {
            $id = $this->params('application');
        }

        return $this->getServiceLocator()->get('Entity\Application')
            ->getDataForProcessing($id);
    }

    /**
     * Get view with application
     *
     * @param array $variables
     * @return \Zend\View\Model\ViewModel
     */
    protected function getViewWithApplication($variables = array())
    {
        $application = $this->getApplication();

        if ($application['licence']['goodsOrPsv']['id'] == LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE) {
            $this->getServiceLocator()->get('Navigation')->findOneBy('id', 'licence_bus')->setVisible(0);
        }

        $variables = array_merge(
            $variables,
            $this->getHeaderParams()
        );

        $view = $this->getView($variables);

        return $view;
    }
}
