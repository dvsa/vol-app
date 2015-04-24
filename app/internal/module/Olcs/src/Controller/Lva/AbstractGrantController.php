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
        $isPost = $request->isPost();
        $post = $isPost ? (array)$request->getPost() : [];

        if ($this->isButtonPressed('cancel') || $this->isButtonPressed('overview')) {
            $this->getServiceLocator()
                ->get('Helper\FlashMessenger')
                ->addWarningMessage('application-not-granted');
            return $this->redirectToOverview($id);
        }

        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        $form = $this->alterGrantForm($formHelper->createForm('Grant'));
        $form->setData($post);

        $validationErrors = $this->validateGrantConditions($id, $isPost, $post);

        if ($isPost && empty($validationErrors)) {
            $method = 'processGrant'.ucfirst($this->lva);
            $this->getServiceLocator()->get('Processing\Application')->$method($id);
            $this->getServiceLocator()->get('Helper\FlashMessenger')
                ->addSuccessMessage('application-granted-successfully');

            // create inspection request if needed
            if (isset($post['inspection-request-confirm']['createInspectionRequest']) &&
                $post['inspection-request-confirm']['createInspectionRequest'] === 'Y') {

                $this->getServiceLocator()->get('BusinessServiceManager')
                    ->get('InspectionRequest')
                    ->process(
                        [
                            'data' => $post,
                            'applicationId' => $id,
                            'type' => 'applicationFromGrant'
                        ]
                    );
            }

            return $this->redirectToOverview($id);
        }

        $formHelper->setFormActionFromRequest($form, $request);

        if (!empty($validationErrors)) {

            $form = $this->addMessages($form, $validationErrors);

            if (!$isPost) {
                $form = $this->maybeRemoveInspectionRequestQuestion($form);
                $formHelper->remove($form, 'form-actions->grant');
            }
        } else {
            $form = $this->maybeSetConfirmGrantApplication($form);
            $message = $form->get('messages')->get('message')->getValue();
            if (empty($message)) {
                $formHelper->remove($form, 'messages');
            }
            $formHelper->remove($form, 'form-actions->overview');
        }

        $this->maybeLoadScripts();

        if ($this->getRequest()->isXmlHttpRequest()) {
            return $this->renderModalWithForm($form, 'internal.inspection-request.form.title.grant');
        }

        return $this->render(
            'grant_application',
            $form,
            [
                'route' => 'lva-'.$this->lva,
                'routeParams' => ['application' => $id],
            ]
        );
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

    /**
     * Render modal window with form
     *
     * @param Common\Form\Form $form
     * @param string $title
     * @return Common\Form\Form
     */
    protected function renderModalWithForm($form, $title)
    {
        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('partials/form');

        $layout = new ViewModel(['pageTitle' => $title]);
        $layout->setTemplate('layout/ajax')
            ->setTerminal(true)
            ->addChild($view, 'content');
        return $layout;
    }

    /**
     * Add feedback messages as to why validation failed
     *
     * @param Common\Form\Form $form
     * @param array $validationErrors
     * @return Common\Form\Form
     */
    protected function addMessages($form, $validationErrors)
    {
        $translator = $this->getServiceLocator()->get('Helper\Translation');
        $messages = array_map(
            function ($message) use ($translator) {
                return $translator->translate($message);
            },
            $validationErrors
        );
        $form->get('messages')->get('message')->setValue(implode('<br>', $messages));
        return $form;
    }

    /**
     * Redirect to overview
     *
     * @return Zend\Http\Redirect
     */
    protected function redirectToOverview($id)
    {
        return $this->redirect()->toRouteAjax('lva-'.$this->lva, array('application' => $id));
    }
}
