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
        // need to determine if this is an unlicensed operator or not
        $response = $this->handleQuery(
            \Dvsa\Olcs\Transfer\Query\Organisation\Organisation::create(
                [
                    'id' => $this->params('organisation'),
                ]
            )
        );

        $organisation = $response->getResult();

        return $organisation['isUnlicensed'] ? 'layout/unlicensed-operator-section' : 'layout/operator-section';

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
        $request->getQuery()->set('sort', 'inForceDate');

        return $this->index(
            \Dvsa\Olcs\Transfer\Query\Licence\GetList::class,
            new \Olcs\Mvc\Controller\ParameterProvider\GenericList(['organisation']),
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
            new \Olcs\Mvc\Controller\ParameterProvider\GenericList(['organisation']),
            'applicationsTable',
            'operator-applications',
            $this->tableViewTemplate
        );
    }
}
