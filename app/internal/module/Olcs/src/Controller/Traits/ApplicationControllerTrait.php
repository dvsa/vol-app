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

        $layout = new Layout($applicationLayout, $params);

        if ($this->getRequest()->isXmlHttpRequest()) {
            $layout->setTemplate('layout/ajax');
        }

        return $layout;
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
            'status' => $data['status']['id'],
            'statusColour' => $this->getColourForStatus($data['status']['id']),
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

    protected function getColourForStatus($status)
    {
        switch ($status) {
            case ApplicationEntityService::APPLICATION_STATUS_VALID:
                $colour = 'green';
                break;
            case ApplicationEntityService::APPLICATION_STATUS_UNDER_CONSIDERATION:
            case ApplicationEntityService::APPLICATION_STATUS_GRANTED:
                $colour = 'orange';
                break;
            case ApplicationEntityService::APPLICATION_STATUS_WITHDRAWN:
            case ApplicationEntityService::APPLICATION_STATUS_REFUSED:
            case ApplicationEntityService::APPLICATION_STATUS_NOT_TAKEN_UP:
                $colour = 'red';
                break;
            default:
                $colour = 'grey';
                break;
        }

        return $colour;
    }
}
