<?php

namespace Olcs\Controller\Licence;

use Common\Controller\Lva\AbstractController;
use Common\Controller\Lva\Traits\CrudTableTrait;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\GracePeriod\CreateGracePeriod;
use Dvsa\Olcs\Transfer\Command\GracePeriod\DeleteGracePeriod;
use Dvsa\Olcs\Transfer\Command\GracePeriod\UpdateGracePeriod;
use Dvsa\Olcs\Transfer\Query\GracePeriod\GracePeriod;
use Dvsa\Olcs\Transfer\Query\GracePeriod\GracePeriods;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Form\FormInterface;
use Olcs\Controller\Interfaces\LicenceControllerInterface;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;
use LmcRbacMvc\Service\AuthorizationService;

class LicenceGracePeriodsController extends AbstractController implements LicenceControllerInterface
{
    use LicenceControllerTrait;
    use CrudTableTrait;

    protected $lva = 'licence';
    protected string $location = 'internal';

    protected $section = 'grace-periods';

    protected FlashMessengerHelperService $flashMessengerHelper;
    protected TableFactory $tableFactory;
    protected FormHelperService $formHelper;
    protected ScriptFactory $scriptFactory;
    protected $navigation;

    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FlashMessengerHelperService $flashMessengerHelper,
        TableFactory $tableFactory,
        FormHelperService $formHelper,
        ScriptFactory $scriptFactory,
        $navigation
    ) {
        parent::__construct(
            $niTextTranslationUtil,
            $authService
        );
        $this->flashMessengerHelper = $flashMessengerHelper;
        $this->tableFactory = $tableFactory;
        $this->formHelper = $formHelper;
        $this->scriptFactory = $scriptFactory;
        $this->navigation = $navigation;
    }

    /**
     * List the grace periods for this licence.
     *
     * @return \Laminas\Http\Response|\Laminas\View\Model\ViewModel
     */
    public function indexAction()
    {
        $request = $this->getRequest();

        $licence = $this->params()->fromRoute('licence', null);

        if ($request->isPost()) {
            $data = (array)$request->getPost();
            $crudAction = $this->getCrudAction($data);

            if ($crudAction !== null) {
                return $this->handleCrudAction($crudAction);
            }
        }

        $query = GracePeriods::create(['licence' => $licence]);

        $response = $this->handleQuery($query);

        if (!$response->isOk()) {
            if ($response->isClientError() || $response->isServerError()) {
                $this->flashMessengerHelper->addErrorMessage('unknown-error');
            }

            return $this->notFoundAction();
        }

        $result = (array)$response->getResult();

        $form = $this->getForm($result);

        $this->scriptFactory->loadFile('table-actions');

        return $this->render(
            'grace-period',
            $form
        );
    }

    /**
     * Add a grace period.
     *
     * @return \Laminas\View\Model\ViewModel
     */
    public function addAction()
    {
        $request = $this->getRequest();

        $form = $this->formHelper
            ->createFormWithRequest(
                'GracePeriod',
                $request
            );

        if ($request->isPost()) {
            $form->setData((array)$request->getPost());

            if ($form->isValid()) {
                $params = (array)$form->getData()['details'];
                $params['licence'] = $this->getLicenceId();

                $command = CreateGracePeriod::create($params);

                $response = $this->handleCommand($command);

                if (!$response->isOk()) {
                    if ($response->isClientError() || $response->isServerError()) {
                        $this->flashMessengerHelper->addErrorMessage('unknown-error');
                    }

                    $this->flashMessenger()->addErrorMessage('licence.grace-period.saved.failure');
                    return $this->redirectToIndex();
                }

                return $this->handlePostSave(null, false);
            }
        }

        return $this->render('add-grace-period', $form);
    }

    /**
     * Update a grace period.
     *
     * @return \Laminas\View\Model\ViewModel
     */
    public function editAction()
    {
        $request = $this->getRequest();

        $query = GracePeriod::create(
            [
                'id' => $this->params()->fromRoute('child_id', null)
            ]
        );

        $response = $this->handleQuery($query);

        if (!$response->isOk()) {
            if ($response->isClientError() || $response->isServerError()) {
                $this->flashMessengerHelper->addErrorMessage('unknown-error');
            }

            return $this->notFoundAction();
        }

        $gracePeriod = $response->getResult();

        $form = $this->formHelper
            ->createFormWithRequest(
                'GracePeriod',
                $request
            );

        if ($request->isPost()) {
            $form->setData((array)$request->getPost());

            if ($form->isValid()) {
                $params = array_merge((array)$gracePeriod, (array)$form->getData()['details']);
                $params['licence'] = $this->getLicenceId();

                $command = UpdateGracePeriod::create($params);

                $response = $this->handleCommand($command);

                if (!$response->isOk()) {
                    if ($response->isClientError() || $response->isServerError()) {
                        $this->flashMessengerHelper->addErrorMessage('unknown-error');
                    }

                    $this->flashMessenger()->addErrorMessage('licence.grace-period.saved.failure');
                    return $this->redirectToIndex();
                }

                $this->flashMessenger()->addSuccessMessage('licence.grace-period.saved.success');
                return $this->redirectToIndex();
            }
        }

        $this->formHelper->remove($form, 'form-actions->addAnother');
        $form->setData(
            [
                'details' => $gracePeriod
            ]
        );

        return $this->render('edit-grace-period', $form);
    }

    /**
     * Method to call on the delete action.
     *
     * @return bool
     */
    public function delete()
    {
        $gracePeriodIds = $this->params()->fromRoute('child_id', null);
        $ids = explode(',', $gracePeriodIds);

        $dto = DeleteGracePeriod::create(
            [
                'ids' => $ids
            ]
        );

        $response = $this->handleCommand($dto);

        if ($response->isOk()) {
            return true;
        }

        return false;
    }

    /**
     * Get the form and table.
     *
     * @param array $gracePeriods gracePeriods
     *
     * @return \Laminas\Form\Form
     */
    protected function getForm(array $gracePeriods)
    {
        $form = $this->formHelper
            ->createFormWithRequest(
                'GracePeriods',
                $this->getRequest()
            );

        $this->alterForm($form, $gracePeriods);

        return $form;
    }

    /**
     * Alter the form to set the table and remove un-needed buttons.
     *
     * @param FormInterface $form         form
     * @param array         $gracePeriods gracePeriods
     *
     * @return void
     */
    protected function alterForm(FormInterface $form, array $gracePeriods)
    {
        $this->formHelper
            ->removeFieldList(
                $form,
                'form-actions',
                [
                    'submit',
                    'addAnother',
                    'cancel'
                ]
            );

        $form->get('table')->get('table')->setTable(
            $this->getTable($gracePeriods)
        );
    }

    /**
     * Get the table.
     *
     * @param array $gracePeriods grace periods
     *
     * @return mixed
     */
    protected function getTable(array $gracePeriods)
    {
        return $this->tableFactory
            ->prepareTable('licence.grace-periods', $this->getTableData($gracePeriods));
    }

    /**
     * Format data for the table.
     *
     * @param array $gracePeriods grace periods
     *
     * @return array
     */
    protected function getTableData(array $gracePeriods)
    {
        $tableData = [];
        foreach ($gracePeriods['results'] as $gracePeriod) {
            $tableData[] = [
                'id' => $gracePeriod['id'],
                'startDate' => $gracePeriod['startDate'],
                'endDate' => $gracePeriod['endDate'],
                'description' => $gracePeriod['description'],
                'status' => ($gracePeriod['isActive'] ? "Active" : "Inactive")
            ];
        }

        return $tableData;
    }

    /**
     * Redirect to the grace-periods page.
     *
     * @return mixed
     */
    public function redirectToIndex()
    {
        return $this->redirect()->toRouteAjax(
            'licence/grace-periods',
            [
                'licence' => $this->params()->fromRoute('licence', null)
            ]
        );
    }
}
