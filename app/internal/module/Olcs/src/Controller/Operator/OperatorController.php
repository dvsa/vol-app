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
    use Traits\OperatorControllerTrait;

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

    public function disqualifyAction()
    {
        $organisationId = (int) $this->params('organisation');
        $organisation = $this->getOperator($organisationId);
        $disqualification = isset($organisation['disqualifications'][0]) ? $organisation['disqualifications'][0] : null;

        // unset layout file
        $this->layoutFile = null;
        $this->pageLayout = null;

        $request = $this->getRequest();

        if ($request->isPost()) {
            $data = (array)$request->getPost();
        } else {
            $data = [];
            if ($disqualification !== null) {
                $data['isDisqualified'] = $disqualification['isDisqualified'];
                $data['startDate'] = $disqualification['startDate'];
                $data['period'] = $disqualification['period'];
                $data['notes'] = $disqualification['notes'];
                $data['version'] = $disqualification['version'];
            }
        }
        $data['name'] = $organisation['name'];

        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        /* @var $form \Common\Form\Form */
        $form = $formHelper->createForm('Disqualify');
        $form->setData($data);
        $formHelper->setFormActionFromRequest($form, $this->getRequest());

        // Must be ticked if no disqualification record exists
        if ($disqualification === null) {
            $formHelper->attachValidator($form, 'isDisqualified', new \Zend\Validator\Identical('Y'));
        }
        // Start date is required if isDisqualified is ticked
        if (isset($data['isDisqualified']) && $data['isDisqualified'] == 'N') {
            $form->getInputFilter()->get('startDate')->setRequired(false);
        }

        if ($request->isPost() && $form->isValid()) {
            if ($this->saveDisqualification($form->getData(), $organisation['id'], $disqualification)) {
                return $this->redirect()->toRouteAjax('operator', ['organisation' => $organisationId]);
            }
        }

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('partials/form');

        return $this->renderView($view, 'Disqualify');
    }

    /**
     * Save the disqualification
     *
     * @param array $formData         Data from the form
     * @param int   $organisationId   Organisation ID
     * @param array $disqualification disqualification being edited, null if creating a new one
     *
     * @return bool Success
     */
    protected function saveDisqualification(array $formData, $organisationId, $disqualification)
    {
        $params = [
            'isDisqualified' => $formData['isDisqualified'],
            'period' => $formData['period'],
            'startDate' => $formData['startDate'],
            'notes' => $formData['notes'],
        ];
        if ($disqualification === null) {
            // create
            $params['organisation'] = $organisationId;
            $command = \Dvsa\Olcs\Transfer\Command\Disqualification\Create::create($params);
        } else {
            // update
            $params['id'] = $disqualification['id'];
            $params['version'] = $formData['version'];
            $command = \Dvsa\Olcs\Transfer\Command\Disqualification\Update::create($params);
        }

        $response = $this->handleCommand($command);
        if ($response->isOk()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')
                ->addSuccessMessage('The disqualification details have been saved');
            return true;
        } else {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
            return false;
        }
    }

    /**
     * Get Operator(Organisation) data
     *
     * @param int $id operator(organisation) ID
     *
     * @return array
     * @throws \RuntimeException
     */
    protected function getOperator($id)
    {
        $response = $this->handleQuery(
            \Dvsa\Olcs\Transfer\Query\Organisation\Organisation::create(['id' => $id])
        );
        if (!$response->isOk()) {
            throw new \RuntimeException('Error getting organisation');
        }

        return $response->getResult();
    }

    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $this->pageLayout = $this->isUnlicensed() ? 'unlicensed-operator-section' : 'operator-section';

        return parent::onDispatch($e);
    }

    protected function isUnlicensed()
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

        return $organisation['isUnlicensed'];
    }
}
