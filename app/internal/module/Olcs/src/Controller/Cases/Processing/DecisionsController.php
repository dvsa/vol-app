<?php

/**
 * Processing Controller
 */
namespace Olcs\Controller\Cases\Processing;

use Dvsa\Olcs\Transfer\Query\Cases\Cases as CasesItemDto;
use Dvsa\Olcs\Transfer\Query\TmCaseDecision\GetByCase as ItemDto;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Common\Exception\ResourceNotFoundException;
use Zend\View\Model\ViewModel;

/**
 * Case Decisions Controller
 */
class DecisionsController extends AbstractInternalController implements CaseControllerInterface, LeftViewProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'case_processing_decisions';

    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/cases/partials/left');

        return $view;
    }

    /**
     * Variables for controlling details view rendering
     * details view and itemDto are required.
     */
    protected $detailsViewTemplate = 'sections/cases/pages/tm-decision';
    protected $itemDto = ItemDto::class;
    // 'id' => 'conviction', to => from
    protected $itemParams = ['case'];

    public function indexAction()
    {
        $case = $this->getCase($this->params()->fromRoute('case'));

        if (!empty($case['transportManager']['id'])) {
            // is TM
            return $this->redirectToDetails();
        }

        return $this->redirect()->toRouteAjax(
            'processing_in_office_revocation',
            ['action' => 'index'],
            ['code' => '303'],
            true
        );
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
            'processing_decisions',
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

    /**
     * Get the Case data
     *
     * @param id $id
     * @return array
     * @throws ResourceNotFoundException
     */
    private function getCase($id)
    {
        $response = $this->handleQuery(
            CasesItemDto::create(['id' => $id])
        );

        if (!$response->isOk()) {
            throw new ResourceNotFoundException("Case id [$id] not found");
        }

        return $response->getResult();
    }
}
