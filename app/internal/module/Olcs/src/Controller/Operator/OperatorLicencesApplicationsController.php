<?php

namespace Olcs\Controller\Operator;

use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\OperatorControllerInterface;
use Olcs\Controller\Interfaces\PageInnerLayoutProvider;
use Olcs\Controller\Interfaces\PageLayoutProvider;

/**
 * OperatorLicencesApplicationsController Controller
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class OperatorLicencesApplicationsController extends AbstractInternalController implements OperatorControllerInterface,
 PageLayoutProvider,
 PageInnerLayoutProvider
{
    public function getPageLayout()
    {
        return 'layout/operator-section';
    }

    public function getPageInnerLayout()
    {
        return 'pages/operator/licences-and-applications';
    }

    public function indexAction()
    {
        /**
         * Both methods return the same view
         */
        $this->setupLicencesTable();

        return $this->setupEnvironmentComplaintsTable();
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
        $request->getQuery()->set('excludeStatuses', ['lsts_not_submitted', 'lsts_consideration', 'lsts_granted']);
        // order by licNo
        $request->getQuery()->set('sort', 'licNo');

        return $this->index(
            \Dvsa\Olcs\Transfer\Query\Licence\GetList::class,
            ['organisation'],
            'id',
            'licencesTable',
            'operator-licences',
            $this->tableViewTemplate
        );
    }

    /**
     * Create a table of Applications
     *
     * @return \Zend\View\Model\ViewModel
     */
    private function setupEnvironmentComplaintsTable()
    {
        /* @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        // order by created date
        $request->getQuery()->set('sort', 'createdOn');

        return $this->index(
            \Dvsa\Olcs\Transfer\Query\Application\GetList::class,
            ['organisation'],
            'id',
            'applicationsTable',
            'operator-applications',
            $this->tableViewTemplate
        );
    }
}
