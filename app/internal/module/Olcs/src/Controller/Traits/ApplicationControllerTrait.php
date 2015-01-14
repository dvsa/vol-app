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

        $applicationLayout->addChild($this->getQuickActions(), 'actions');
        $applicationLayout->addChild($view, 'content');

        $params = $this->getHeaderParams();

        return new Layout($applicationLayout, $params);
    }

    /**
     * Quick action view model
     *
     * @return \Zend\View\Model\ViewModel
     */
    protected function getQuickActions()
    {
        $status = $this->getServiceLocator()->get('Entity\Application')->getStatus($this->params('application'));
        $showGrantButton = $this->shouldShowGrantButton($status);

        if ($showGrantButton) {
            $showUndoGrantButton = false;
        } else {
            $showUndoGrantButton = $this->shouldShowUndoGrantButton($status);
        }

        $viewModel = new ViewModel(
            array(
                'showGrant' => $showGrantButton,
                'showUndoGrant' => $showUndoGrantButton
            )
        );
        $viewModel->setTemplate('partials/application-sidebar');

        return $viewModel;
    }

    protected function shouldShowGrantButton($status)
    {
        return ($status === ApplicationEntityService::APPLICATION_STATUS_UNDER_CONSIDERATION);
    }

    protected function shouldShowUndoGrantButton($status)
    {
        $applicationId = $this->params('application');

        $applicationType = $this->getServiceLocator()->get('Entity\Application')->getApplicationType($applicationId);

        if ($applicationType === ApplicationEntityService::APPLICATION_TYPE_NEW
            && $status === ApplicationEntityService::APPLICATION_STATUS_GRANTED
        ) {
            $applicationService = $this->getServiceLocator()->get('Entity\Application');

            $category = $applicationService->getCategory($applicationId);

            return ($category === LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE);
        }

        return false;
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
