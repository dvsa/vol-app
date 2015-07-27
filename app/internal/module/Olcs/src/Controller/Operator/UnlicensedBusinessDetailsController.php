<?php

/**
 * Unlicensed Operator Business Details Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Controller\Operator;

use Common\RefData;
use Dvsa\Olcs\Transfer\Command\Operator\CreateUnlicensed as CreateDto;
use Dvsa\Olcs\Transfer\Command\Operator\UpdateUnlicensed as UpdateDto;
use Dvsa\Olcs\Transfer\Query\Operator\UnlicensedBusinessDetails as BusinessDetailsDto;
use Olcs\Data\Mapper\UnlicensedOperatorBusinessDetails as Mapper;

/**
 * Unlicensed Operator Business Details Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class UnlicensedBusinessDetailsController extends OperatorController
{
    /**
     * @var string
     */
    protected $section = 'business_details';

    /**
     * @var string
     */
    protected $subNavRoute = 'operator_profile';

    protected $organisation = null;

    /**
     * Redirect to the first menu section
     *
     * @return \Zend\Http\Response
     */
    public function indexJumpAction()
    {
        return $this->redirect()->toRoute('operator-unlicensed/business-details', [], [], true);
    }

    /**
     * Index action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $operator = $this->params()->fromRoute('organisation');
        $this->loadScripts(['operator-profile']);
        $post = $this->params()->fromPost();

        if ($this->isButtonPressed('cancel')) {
            // user pressed cancel button in edit form
            if ($operator) {
                $this->flashMessenger()->addSuccessMessage('Your changes have been discarded');
                return $this->redirectToRoute('operator-unlicensed/business-details', ['organisation' => $operator]);
            } else {
                // user pressed cancel button in add form
                return $this->redirectToRoute('operators/operators-params');
            }
        }

        $form = $this->getForm('UnlicensedOperator');
        $this->pageTitle = 'internal-operator-create-new-unlicensed-operator';

        if ($this->getRequest()->isPost()) {
            // if this is post always take organisation type from parameters
            $form->setData($post);
        } elseif ($operator) {
            // we are in edit mode, need to fetch original data
            $originalData = Mapper::mapFromResult($this->getOrganisation($operator));
            $form->setData($originalData);
        }

        // @todo don't validate if we're doing postcode lookup
        if ($this->getRequest()->isPost()) {
            if (!$this->getEnabledCsrf()) {
                $this->getServiceLocator()->get('Helper\Form')->remove($form, 'csrf');
            }
            if ($form->isValid()) {

                $action = $operator ? 'edit' : 'add';
                $this->saveForm($form, $action);

                // we need to process redirect and catch flashMessenger messages if available
                if ($this->getResponse()->getStatusCode() == 302) {
                    return $this->getResponse();
                }
            }
        }

        $view = $this->getView(['form' => $form]);
        $view->setTemplate('partials/form');
        return $this->renderView($view);
    }

    /**
     * Save form
     *
     * @param Zend\Form\Form $form
     * @param strring $action
     * @return mixed
     */
    private function saveForm($form, $action)
    {
        $data = $form->getData();

        $params = Mapper::mapFromForm($data);

        if ($action == 'edit') {
            $message = 'The operator has been updated successfully';
            $dto = UpdateDto::create($params);
        } else {
            $message = 'The operator has been created successfully';
            $dto = CreateDto::create($params);
        }

        /** @var \Common\Service\Cqrs\Response $response */
        $response = $this->handleCommand($dto);
        if ($response->isOk()) {
            $this->flashMessenger()->addSuccessMessage($message);
            $orgId = $response->getResult()['id']['organisation'];
            return $this->redirectToRoute('operator/business-details', ['organisation' => $orgId]);
        }
        if ($response->isClientError()) {
            $this->mapErrors($form, $response->getResult()['messages']);
        }
        if ($response->isServerError()) {
            $this->addErrorMessage('unknown-error');
        }
    }

    protected function mapErrors($form, array $errors)
    {
        Mapper::mapFromErrors($form, $errors);
        if (!empty($errors)) {
            $fm = $this->getServiceLocator()->get('Helper\FlashMessenger');
            foreach ($errors as $error) {
                $fm->addCurrentErrorMessage($error);
            }
        }
    }

    private function getOrganisation($organisationId)
    {
        if (!$this->organisation) {
            $response = $this->handleQuery(BusinessDetailsDto::create(['id' => $organisationId]));

            if ($response->isClientError() || $response->isServerError()) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addCurrentErrorMessage('unknown-error');
                return $this->notFoundAction();
            }
            $this->organisation = $response->getResult();
        }
        return $this->organisation;
    }
}
