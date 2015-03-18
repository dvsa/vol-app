<?php

/**
 * Abstract Internal Withdraw Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Controller\Lva;

use Common\Controller\Lva\AbstractController;
use Common\Service\Entity\LicenceEntityService as Licence;
use Zend\View\Model\ViewModel;

/**
 * Abstract Internal Withdraw Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
abstract class AbstractWithdrawController extends AbstractController
{
    protected $lva;
    protected $location;

    public function indexAction()
    {
        $request = $this->getRequest();
        $id      = $this->params('application');
        $form    = $this->getWithdrawForm();

        if ($request->isPost()) {

            if ($this->isButtonPressed('cancel')) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')
                    ->addWarningMessage('application-not-withdrawn');
                return $this->redirect()->toRouteAjax('lva-'.$this->lva, array('application' => $id));
            }

            $postData = (array)$request->getPost();
            $form->setData($postData);

            if ($form->isValid()) {
                $this->getServiceLocator()->get('Processing\Application')->processWithdrawApplication($id);

                $message = $this->getServiceLocator()->get('Helper\Translation')
                    ->translateReplace('application-withdrawn-successfully', [$id]);

                $this->getServiceLocator()->get('Helper\FlashMessenger')
                    ->addSuccessMessage($message);

                $licenceId = $this->getServiceLocator()->get('Entity\Application')
                    ->getLicenceIdForApplication($id);
                return $this->redirect()->toRouteAjax('lva-licence/overview', array('licence' => $licenceId));
            }
        }

        return $this->render('withdraw_application', $form);
    }

    protected function getWithdrawForm()
    {
        $request  = $this->getRequest();
        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        $form = $formHelper->createFormWithRequest('Withdraw', $request);

        // override default label on confirm action button
        $form->get('form-actions')->get('confirm')->setLabel('Confirm');

        return $form;
    }

    /**
     * Check for redirect
     *
     * @param int $lvaId
     * @return null
     */
    protected function checkForRedirect($lvaId)
    {
        // no-op to avoid LVA predispatch magic kicking in
    }
}
