<?php

namespace Olcs\Controller\Operator;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Laminas\Navigation\Navigation;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Interfaces\OperatorControllerInterface;
use Olcs\Form\Model\Form;
use Olcs\Service\Data\ApplicationStatus;

class OperatorLicencesApplicationsController extends AbstractInternalController implements
    OperatorControllerInterface,
    LeftViewProvider
{
    public function __construct(
        TranslationHelperService $translationHelper,
        FormHelperService $formHelper,
        FlashMessengerHelperService $flashMessenger,
        Navigation $navigation,
        protected ApplicationStatus $operAppStatusService
    ) {
        parent::__construct($translationHelper, $formHelper, $flashMessenger, $navigation);
    }
    /**
     * Get Left View
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/operator/partials/left');

        return $view;
    }

    /**
     * Process action: Licenses
     *
     * @return ViewModel
     */
    public function licencesAction()
    {
        $this->setupLicencesTable();
        return $this->viewBuilder()->buildViewFromTemplate('sections/operator/pages/licences-and-applications');
    }

    /**
     * Process actions: Applications
     *
     * @return ViewModel
     */
    public function applicationsAction()
    {
        $this->setupApplicationsTable();
        return $this->viewBuilder()->buildViewFromTemplate('sections/operator/pages/licences-and-applications');
    }

    /**
     * Create a table of Licences
     *
     * @return \Laminas\View\Model\ViewModel
     */
    private function setupLicencesTable()
    {
        /* @var $request \Laminas\Http\Request */
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
     * @return \Laminas\View\Model\ViewModel
     */
    private function setupApplicationsTable()
    {
        /* @var $request \Laminas\Http\Request */
        $request = $this->getRequest();

        // order by created date
        $request->getQuery()->set('sort', 'createdOn');

        if (!$request->getQuery()->get('limit')) {
            $request->getQuery()->set('limit', 25);
        }

        $this->setFilterDefaults();

        return $this->index(
            \Dvsa\Olcs\Transfer\Query\Application\GetList::class,
            new \Olcs\Mvc\Controller\ParameterProvider\GenericList(['organisation']),
            'table',
            'operator-applications',
            $this->tableViewTemplate,
            Form\Filter\OperatorApplication::class
        );
    }

    /**
     * Sets filter defaults
     *
     * @return void
     */
    private function setFilterDefaults()
    {
        $this->inlineScripts = ['forms/filter'];

        /**
 * @var \Olcs\Service\Data\ApplicationStatus $operAppStatusSrv
*/
        $operAppStatusSrv = $this->operAppStatusService;

        $operAppStatusSrv->setOrgId(
            $this->params()->fromRoute('organisation')
        );
    }
}
