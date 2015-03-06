<?php

/**
 * Abstract Internal Grant Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Controller\Lva;

use Common\Controller\Lva\AbstractController;
use Common\Service\Entity\LicenceEntityService as Licence;
use Zend\View\Model\ViewModel;

/**
 * Abstract Internal Grant Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
abstract class AbstractGrantController extends AbstractController
{
    protected $lva;
    protected $location;

    public function grantAction()
    {
        $request  = $this->getRequest();
        $id       = $this->params('application');
        $viewData = [
            'route' => 'lva-'.$this->lva,
            'routeParams' => ['application' => $id],
        ];
        $validationErrors = null;

        if ($request->isPost()) {

            if ($this->isButtonPressed('cancel')) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')
                    ->addWarningMessage('application-not-granted');
                return $this->redirect()->toRouteAjax('lva-'.$this->lva, array('application' => $id));
            }

            $validationErrors = $this->validateGrantConditions($id);
            if (empty($validationErrors)) {
                $method = 'processGrant'.ucfirst($this->lva);
                $this->getServiceLocator()->get('Processing\Application')->$method($id);
                $this->getServiceLocator()->get('Helper\FlashMessenger')
                    ->addSuccessMessage('application-granted-successfully');
                return $this->redirect()->toRouteAjax('lva-'.$this->lva, array('application' => $id));
            }
        }

        // (is_null check avoids validating twice if POSTing)
        if (is_null($validationErrors)) {
            $validationErrors = $this->validateGrantConditions($id);
        }

        if (!empty($validationErrors)) {
            // render the feedback as to why validation failed
            $placeholder = $this->getServiceLocator()->get('ViewHelperManager')->get('placeholder');
            foreach ($validationErrors as $message) {
                $placeholder->getContainer('guidance')->append($message);
            }
        } else {
            // render generic confirmation form
            $formHelper = $this->getServiceLocator()->get('Helper\Form');
            $form = $formHelper->createForm('GenericConfirmation');
            $form->get('messages')->get('message')->setValue('confirm-grant-application');
            $formHelper->setFormActionFromRequest($form, $request);
            $viewData['form'] = $form;
        }

        $view = new ViewModel($viewData);
        $view->setTemplate('partials/grant');

        return $this->render($view);
    }

    /**
     * Check that the application can be granted
     *
     * @param int $applicationId
     * @return array Array of error messages, empty if no validation errors
     */
    abstract protected function validateGrantConditions($applicationId);

    /**
     * Check for redirect
     *
     * @param int $lvaId
     * @return null|\Zend\Http\Response
     */
    protected function checkForRedirect($lvaId)
    {
        // no-op to avoid LVA predispatch magic kicking in
    }
}
