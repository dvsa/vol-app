<?php

/**
 * IRHP Candidate Permit Application
 *
 * @author Andy Newton <andy@vitri.ltd>
 */

namespace Olcs\Controller\IrhpPermits;

use Dvsa\Olcs\Transfer\Query\IrhpCandidatePermit\GetListByIrhpApplication as ListDTO;
use Dvsa\Olcs\Transfer\Query\IrhpCandidatePermit\ById as ItemDTO;
use Dvsa\Olcs\Transfer\Command\IrhpCandidatePermit\Delete as DeleteCmd;
use Dvsa\Olcs\Transfer\Command\IrhpCandidatePermit\Update as UpdateCmd;
use Dvsa\Olcs\Transfer\Command\IrhpCandidatePermit\Create as CreateCmd;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\ById as IrhpApplicationDTO;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\RangesByIrhpApplication as RangesDTO;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\IrhpApplicationControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Data\Mapper\IrhpCandidatePermit as IrhpCandidatePermitMapper;
use Olcs\Form\Model\Form\IrhpCandidatePermit as IrhpCandidatePermitForm;
use Zend\Form\Form;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class IrhpCandidatePermitController extends AbstractInternalController implements
    IrhpApplicationControllerInterface,
    LeftViewProvider
{
    protected $itemParams = ['id'];
    protected $deleteParams = ['id'];
    protected $tableName = 'irhp-permits-pre-grant';
    protected $defaultTableSortField = 'id';
    protected $defaultTableOrderField = 'ASC';
    protected $listVars = ['irhpApplication' => 'irhpAppId'];

    protected $listDto = ListDTO::class;
    protected $itemDto = ItemDTO::class;
    protected $deleteCommand = DeleteCmd::class;
    protected $updateCommand = UpdateCmd::class;
    protected $createCommand = CreateCmd::class;

    protected $formClass = IrhpCandidatePermitForm::class;
    protected $addFormClass = IrhpCandidatePermitForm::class;

    protected $hasMultiDelete = false;
    protected $indexPageTitle = 'IRHP Candidate Permits';

    protected $tableViewTemplate = 'pages/irhp-permit/pre-grant';

    protected $mapperClass = IrhpCandidatePermitMapper::class;

    /**
     * Any inline scripts needed in this section
     *
     * @var array
     */
    protected $inlineScripts = array(
        'indexAction' => ['table-actions'],
        'addAction' => ['forms/irhp-candidate-permit'],
        'editAction' => ['forms/irhp-candidate-permit'],
    );

    /**
     * Get left view
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel(
            [
                'navigationId' => 'irhp_permits',
                'navigationTitle' => 'Application details'
            ]
        );

        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }


    /**
     * Small override to pull in application data needed for permits/countries requested on original application.
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function indexAction()
    {
        $this->placeholder()->setPlaceholder('applicationData', IrhpCandidatePermitMapper::mapApplicationData($this->getIrhpApplication()));
        return parent::indexAction();
    }

    /**
     * Get existing application and populate some required fields on Add form.
     *
     * @param Form $form
     * @param array $formData
     * @return mixed
     */
    protected function alterFormForAdd($form, $formData)
    {
        $irhpApplication = $this->getIrhpApplication();
        $formData['fields']['irhpPermitApplication'] = $irhpApplication['irhpPermitApplications'][0]['id'];
        $form->setData($formData);
        return $form;
    }

    /**
     * Utility function to get IrhpApplication relating to ID in the path.
     *
     * @return array|mixed
     * @throws \RuntimeException
     */
    protected function getIrhpApplication()
    {
        $applicationQry = $this->handleQuery(IrhpApplicationDTO::create(['id' => $this->params()->fromRoute('irhpAppId')]));
        if (!$applicationQry->isOk()) {
            throw new \RuntimeException('Error getting application data');
        }
        return $applicationQry->getResult();
    }

    /**
     * Add required parameter to list DTO query
     *
     * @param array $parameters
     * @return array
     */
    protected function modifyListQueryParameters($parameters)
    {
        $parameters['isPreGrant'] = true;
        return $parameters;
    }

    /**
     * AJAX endpoint to return ranges for a given IrhpApplication's stock
     *
     * @return JsonModel
     * @throws \RuntimeException
     */
    public function rangesAction()
    {
        $rangesQry = $this->handleQuery(RangesDTO::create(['irhpApplication' => $this->params()->fromRoute('irhpAppId')]));
        if (!$rangesQry->isOk()) {
            throw new \RuntimeException('Error getting application data');
        }
        return new JsonModel($rangesQry->getResult());
    }
}
