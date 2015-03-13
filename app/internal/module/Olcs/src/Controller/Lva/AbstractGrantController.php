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

            if ($this->isButtonPressed('cancel') || $this->isButtonPressed('overview')) {
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

        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        $form = $formHelper->createForm('Grant');
        $formHelper->setFormActionFromRequest($form, $request);

        // (is_null check avoids validating twice if POSTing)
        if (is_null($validationErrors)) {
            $validationErrors = $this->validateGrantConditions($id);
        }

        if (!empty($validationErrors)) {
            // add feedback messages as to why validation failed
            $translator = $this->getServiceLocator()->get('Helper\Translation');
            $messages = array_map(
                function ($message) use ($translator) {
                    return $translator->translate($message);
                },
                $validationErrors
            );
            $form->get('messages')->get('message')->setValue(implode('<br>', $messages));
            $formHelper->remove($form, 'form-actions->grant');
        } else {
            $form->get('messages')->get('message')->setValue('confirm-grant-application');
            $formHelper->remove($form, 'form-actions->overview');
        }

        return $this->render('grant_application', $form, $viewData);
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
