<?php

namespace Olcs\Controller\Operator;

use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Interfaces\OperatorControllerInterface;
use Zend\View\Model\ViewModel;

/**
 * OperatorLicencesApplicationsController Controller
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class OperatorLicencesApplicationsController extends AbstractInternalController implements
    OperatorControllerInterface,
    LeftViewProvider
{
    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/operator/partials/left');

        return $view;
    }

    public function licencesAction()
    {
        $this->setupLicencesTable();
        return $this->viewBuilder()->buildViewFromTemplate('sections/operator/pages/licences-and-applications');
    }

    public function applicationsAction()
    {
        $this->setupApplicationsTable();
        return $this->viewBuilder()->buildViewFromTemplate('sections/operator/pages/licences-and-applications');
    }

    /**
     * Create a table of Licences
     *
     * @return \Zend\View\Model\ViewModel
     */
    private function setupLicencesTable()
    {
        /* @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        // exclude certain licence statuses
        $request->getQuery()->set(
            'excludeStatuses',
            ['lsts_not_submitted', 'lsts_consideration', 'lsts_granted', 'lsts_withdrawn', 'lsts_refused']
        );
        // order by licNo
        $request->getQuery()->set('sort', 'inForceDate');

        return $this->index(
            \Dvsa\Olcs\Transfer\Query\Licence\GetList::class,
            new \Olcs\Mvc\Controller\ParameterProvider\GenericList(['organisation']),
            'table',
            'operator-licences',
            $this->tableViewTemplate
        );
    }

    /**
     * Create a table of Applications
     *
     * @return \Zend\View\Model\ViewModel
     */
    private function setupApplicationsTable()
    {
        /* @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        // order by created date
        $request->getQuery()->set('sort', 'createdOn');

        if (!$request->getQuery()->get('limit')) {
            $request->getQuery()->set('limit', 25);
        }

        return $this->index(
            \Dvsa\Olcs\Transfer\Query\Application\GetList::class,
            new \Olcs\Mvc\Controller\ParameterProvider\GenericList(['organisation']),
            'table',
            'operator-applications',
            $this->tableViewTemplate
        );
    }
}
