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
        $showGrantButton = $this->shouldShowGrantButton();

        if ($showGrantButton) {
            $showUndoGrantButton = false;
        } else {
            $showUndoGrantButton = $this->shouldShowUndoGrantButton();
        }

        $viewModel = new ViewModel(
            array(
                'showGrant' => $showGrantButton,
                'showUndoGrant' => $showUndoGrantButton
            )
        );
        $viewModel->setTemplate('application/quick-actions');

        return $viewModel;
    }

    protected function shouldShowGrantButton()
    {
        $status = $this->getServiceLocator()->get('Entity\Application')->getStatus($this->params('application'));

        return ($status === ApplicationEntityService::APPLICATION_STATUS_UNDER_CONSIDERATION);
    }

    protected function shouldShowUndoGrantButton()
    {
        $applicationId = $this->params('application');

        if ($this->isApplicationNew($applicationId)) {

            $applicationService = $this->getServiceLocator()->get('Entity\Application');

            $status = $applicationService->getStatus($applicationId);

            if ($status === ApplicationEntityService::APPLICATION_STATUS_GRANTED) {
                $category = $applicationService->getCategory($applicationId);

                return ($category === LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE);
            }

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
}
