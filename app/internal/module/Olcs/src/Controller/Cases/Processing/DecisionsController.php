<?php

/**
 * Processing Controller
 */
namespace Olcs\Controller\Cases\Processing;

use Dvsa\Olcs\Transfer\Query\TmCaseDecision\GetByCase as ItemDto;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Controller\Interfaces\PageInnerLayoutProvider;
use Olcs\Controller\Interfaces\PageLayoutProvider;

/**
 * Case Decisions Controller
 */
class DecisionsController extends AbstractInternalController implements
    CaseControllerInterface,
    PageLayoutProvider,
    PageInnerLayoutProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'case_processing_decisions';

    public function getPageLayout()
    {
        return 'layout/case-section';
    }

    public function getPageInnerLayout()
    {
        return 'layout/case-details-subsection';
    }

    /**
     * Variables for controlling details view rendering
     * details view and itemDto are required.
     */
    protected $detailsViewTemplate = 'pages/case/tm-decision';
    protected $itemDto = ItemDto::class;
    // 'id' => 'conviction', to => from
    protected $itemParams = ['case'];

    public function indexAction()
    {
        return $this->redirectToDetails();
    }

    public function addAction()
    {
        return $this->redirectToDetails();
    }

    public function editAction()
    {
        return $this->redirectToDetails();
    }

    public function deleteAction()
    {
        return $this->redirectToDetails();
    }

    public function redirectToDetails()
    {
        return $this->redirect()->toRouteAjax(
            null,
            ['action' => 'details'],
            ['code' => '303'],
            true
        );
    }

    /**
     * Not found is a valid response for this particular controller
     */
    public function notFoundAction()
    {
        return $this->viewBuilder()->buildViewFromTemplate($this->detailsViewTemplate);
    }
}
