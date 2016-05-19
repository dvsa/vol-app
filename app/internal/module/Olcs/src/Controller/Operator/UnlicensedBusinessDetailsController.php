<?php

namespace Olcs\Controller\Operator;

use Dvsa\Olcs\Transfer\Command\Operator\CreateUnlicensed as CreateDto;
use Dvsa\Olcs\Transfer\Command\Operator\UpdateUnlicensed as UpdateDto;
use Dvsa\Olcs\Transfer\Query\Operator\UnlicensedBusinessDetails as BusinessDetailsDto;
use Olcs\Data\Mapper\UnlicensedOperatorBusinessDetails as Mapper;

/**
 * Unlicensed Operator Business Details Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class UnlicensedBusinessDetailsController extends OperatorBusinessDetailsController
{
    protected $subNavRoute = 'unlicensed_operator_profile';

    /** @var  Mapper */
    protected $mapperClass = Mapper::class;
    protected $createDtoClass = CreateDto::class;
    protected $updateDtoClass = UpdateDto::class;
    protected $queryDtoClass = BusinessDetailsDto::class;

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

        /** @var \Zend\Form\FormInterface $form */
        $form = $this->getForm('UnlicensedOperator');
        $this->pageTitle = 'internal-operator-create-new-unlicensed-operator';

        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            $postData = $request->getPost();

            // if this is post always take organisation type from parameters
            $form->setData($postData);

            if ($form->isValid()) {
                $action = $operator ? 'edit' : 'add';

                $response = $this->saveForm($form, $action, 'operator-unlicensed');
                // we need to process redirect and catch flashMessenger messages if available
                if ($response !== null) {
                    return $response;
                }
            }
        } elseif ($operator) {
            // we are in edit mode, need to fetch original data
            $mapper = $this->mapperClass;
            $originalData = $mapper::mapFromResult($this->getOrganisation($operator));
            $form->setData($originalData);
        }

        return $this->renderForm($form);
    }

    /**
     * This method is used by OperatorControllerTrait for populating
     * various bits of view data
     */
    protected function getBusinessDetailsData($organisationId)
    {
        return $this->getOrganisation($organisationId);
    }
}
