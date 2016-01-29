<?php

/**
 * Operator Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\Operator;

use Dvsa\Olcs\Transfer\Command\Application\CreateApplication;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Traits;
use Zend\View\Model\ViewModel;
use Olcs\Controller\Interfaces\OperatorControllerInterface;
use Olcs\Controller\AbstractController;

/**
 * Operator Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class OperatorController extends AbstractController implements OperatorControllerInterface, LeftViewProvider
{
    use Traits\OperatorControllerTrait,
        Traits\ListDataTrait;

    /**
     * @var string
     */
    protected $subNavRoute;

    /**
     * @var string
     */
    protected $section;

    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/operator/partials/left');

        return $view;
    }

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
                    'trafficArea' => $data['trafficArea'],
                    'appliedVia' => $data['appliedVia']
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

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('pages/form');

        return $this->renderView($view, 'Create new application');
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
            if (!$organisationData) {
                return $this->notFoundAction();
            }

            $data = ['fromOperatorName' => $organisationData['name']];
        }

        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        /* @var $form \Common\Form\Form */
        $form = $formHelper->createForm('OperatorMerge');
        $form->setData($data);
        $formHelper->setFormActionFromRequest($form, $request);
        $form->get('toOperatorId')->setAttribute('data-lookup-url', $this->url()->fromRoute('operator-lookup'));

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

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('pages/form');

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
        if ($response->isNotFound()) {
            return null;
        }
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
        $organisationId = (int) $this->params()->fromQuery('organisation');
        $view = new \Zend\View\Model\JsonModel();

        $data = $this->getOrganisation($organisationId);
        if (!$data) {
            return $this->notFoundAction();
        }
        $view->setVariables(
            [
                'id' => $data['id'],
                'name' => $data['name'],
            ]
        );

        return $view;
    }
}
