<?php

/**
 * Application Controller
 */

namespace Olcs\Controller\IrhpPermits;

use Dvsa\Olcs\Transfer\Query\IrhpApplication\InternalApplicationsSummary;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Interfaces\LicenceControllerInterface;
use Olcs\Form\Model\Form\IrhpApplicationFilter as FilterForm;

class ApplicationController extends AbstractInternalController implements LeftViewProvider, LicenceControllerInterface
{
    protected $navigationId = 'licence_irhp_permits-application';

    // Maps the licence route parameter into the ListDTO as licence => value
    protected $listVars = ['licence'];
    protected $listDto = InternalApplicationsSummary::class;
    protected $filterForm = FilterForm::class;

    protected $tableName = 'permit-applications';
    protected $tableViewTemplate = 'pages/table';

    // Scripts to include when rendering actions.
    protected $inlineScripts = [
        'indexAction' => ['forms/filter', 'table-actions']
    ];

    /**
     * Get left view
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel();

        $view->setTemplate('sections/irhp-permit/partials/left');

        return $view;
    }

    /**
     * @return Response|ViewModel
     */
    public function redirectAction()
    {
        return $this->redirect()
            ->toRoute(
                'licence/irhp-permits/application',
                [
                    'licence' => $this->params()->fromRoute('licence')
                ]
            );
    }

    /**
     * @return Response|ViewModel
     */
    public function indexAction()
    {
        $this->handleIndexPost();

        return parent::indexAction();
    }

    /**
     * Override to handle the Table from POST when Apply clicked and redirect to the Add form.
     *
     * @return Response|void
     */
    protected function handleIndexPost()
    {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $postData = (array)$this->params()->fromPost();

            if ($postData['action'] === 'Apply') {
                return $this->redirect()
                    ->toRoute(
                        'licence/irhp-application/selectType',
                        [
                            'licence' => $this->params()->fromRoute('licence')
                        ]
                    );
            }
        }
    }
}
