<?php

/**
 * Operator Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\Operator;

use Dvsa\Olcs\Transfer\Command\Application\CreateApplication;
use Olcs\Controller as OlcsController;
use Olcs\Controller\Traits;
use Zend\View\Model\ViewModel;

/**
 * Operator Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class OperatorController extends OlcsController\CrudAbstract implements
    OlcsController\Interfaces\OperatorControllerInterface
{
    use Traits\OperatorControllerTrait,
        Traits\DocumentSearchTrait,
        Traits\DocumentActionTrait,
        Traits\ListDataTrait;

    /**
     * @var string
     */
    protected $pageLayout = 'operator-section';

    /**
     * @var string
     */
    protected $layoutFile = 'layout/operator-subsection';

    /**
     * @var string
     */
    protected $subNavRoute;

    /**
     * @var string
     */
    protected $section;

    /**
     * Redirect to the first menu section
     *
     * @return \Zend\Http\Response
     */
    public function indexJumpAction()
    {
        return $this->redirect()->toRoute('operator/business-details', [], [], true);
    }

    public function newApplicationAction()
    {
        $this->pageLayout = null;

        $request = $this->getRequest();

        if ($request->isPost()) {
            $data = (array)$request->getPost();
        } else {
            $data['receivedDate'] = $this->getServiceLocator()->get('Helper\Date')->getDateObject();
        }

        $formHelper = $this->getServiceLocator()->get('Helper\Form');

        $form = $formHelper->createForm('NewApplication');
        $form->setData($data);

        $formHelper->setFormActionFromRequest($form, $this->getRequest());

        if ($request->isPost() && $form->isValid()) {

            $data = $form->getData();

            $dto = CreateApplication::create(
                [
                    'organisation' => $this->params('organisation'),
                    'receivedDate' => $data['receivedDate'],
                    'trafficArea' => $data['trafficArea']
                ]
            );

            $command = $this->getServiceLocator()->get('TransferAnnotationBuilder')->createCommand($dto);

            /** @var \Common\Service\Cqrs\Response $response */
            $response = $this->getServiceLocator()->get('CommandService')->send($command);

            if ($response->isOk()) {
                return $this->redirect()->toRouteAjax(
                    'lva-application/type_of_licence',
                    ['application' => $response->getResult()['id']['application']]
                );
            }

            $this->getServiceLocator()->get('Helper\FlashMessenger')
                ->addErrorMessage('unknown-error');
        }

        // unset layout file
        $this->layoutFile = null;

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('partials/form');

        return $this->renderView($view, 'Create new application');
    }

    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $organisationId = $this->params('organisation');

        if (!empty($organisationId)) {
            $this->pageLayout = $this->isUnlicensed() ? 'unlicensed-operator-section' : 'operator-section';
        }

        return parent::onDispatch($e);
    }

    protected function isUnlicensed()
    {
        if (empty($this->params('organisation'))) {
            return;
        }

        // need to determine if this is an unlicensed operator or not
        $response = $this->handleQuery(
            \Dvsa\Olcs\Transfer\Query\Organisation\Organisation::create(
                [
                    'id' => $this->params('organisation'),
                ]
            )
        );

        $organisation = $response->getResult();

        return $organisation['isUnlicensed'];
    }

    /**
     * Transfer associated entities from one Operator to another
     */
    public function mergeAction()
    {
        $organisationId = (int) $this->params()->fromRoute('organisation');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = (array)$request->getPost();
        } else {
            $organisationData = $this->getOrganisation($organisationId);

            $data = ['fromOperatorName' => $organisationData['name']];
        }

        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        /* @var $form \Common\Form\Form */
        $form = $formHelper->createForm('OperatorMerge');
        $form->setData($data);
        $formHelper->setFormActionFromRequest($form, $request);

        if ($request->isPost() && $form->isValid()) {
            $toOperatorId = (int) $form->getData()['toOperatorId'];
            $response = $this->handleCommand(
                \Dvsa\Olcs\Transfer\Command\Organisation\TransferTo::create(
                    ['id' => $organisationId, 'receivingOrganisation' => $toOperatorId]
                )
            );

            if ($response->isOk()) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')
                    ->addSuccessMessage('form.operator-merge.success');
                return $this->redirect()->toRouteAjax('dashboard');
            } else {
                $formMessages['toOperatorId'][] = 'form.operator-merge.to-operator-id.validation';
                $form->setMessages($formMessages);
            }
        }

        $this->getServiceLocator()->get('Script')->loadFile('operator-merge');

        // unset layout file
        $this->layoutFile = null;
        $this->pageLayout = null;

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('partials/form');

        return $this->renderView($view, 'Merge operator');
    }

    /**
     * Get Organisation(Operator) data
     *
     * @param int $id operator(organisation) ID
     *
     * @return array
     * @throws \RuntimeException
     */
    protected function getOrganisation($id)
    {
        $response = $this->handleQuery(
            \Dvsa\Olcs\Transfer\Query\Organisation\Organisation::create(['id' => $id])
        );

        if (!$response->isOk()) {
            throw new \RuntimeException('Error getting organisation');
        }

        return $response->getResult();
    }

    /**
     * Ajax lookup of organisation name
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function lookupAction()
    {
        $organisationId = (int) $this->params()->fromRoute('organisation');
        $view = new \Zend\View\Model\JsonModel();

        try {
            $data = $this->getOrganisation($organisationId);
            $view->setVariables(
                [
                    'id' => $data['id'],
                    'name' => $data['name'],
                ]
            );
        } catch (\RuntimeException $e) {
            $this->getResponse()->setStatusCode(404);
        }

        return $view;
    }

    /**
     * Route (prefix) for document action redirects
     * @see Olcs\Controller\Traits\DocumentActionTrait
     * @return string
     */
    protected function getDocumentRoute()
    {
        return 'operator/documents';
    }

    /**
     * Route params for document action redirects
     * @see Olcs\Controller\Traits\DocumentActionTrait
     * @return array
     */
    protected function getDocumentRouteParams()
    {
        return ['organisation' => $this->getFromRoute('organisation')];
    }

    /**
     * Get view model for document action
     * @see Olcs\Controller\Traits\DocumentActionTrait
     * @return ViewModel
     */
    protected function getDocumentView()
    {
        $filters = $this->mapDocumentFilters(['irfoOrganisation' => $this->getFromRoute('organisation')]);

        return $this->getViewWithOrganisation(
            [
                'table' => $this->getDocumentsTable($filters),
                'form'  => $this->getDocumentForm($filters),
                'documents' => true
            ]
        );
    }
}
