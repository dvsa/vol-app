<?php

namespace Olcs\Controller\Lva;

use Common\Service\Helper\FormHelperService;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Abstract PublishController
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
abstract class AbstractPublishController extends \Common\Controller\Lva\AbstractController
{
    /**
     * @param NiTextTranslation    $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param FormHelperService    $formHelper
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        protected FormHelperService $formHelper
    ) {
        parent::__construct($niTextTranslationUtil, $authService);
    }

    /**
     * indexAction
     *
     * @return \Laminas\Http\Response|\Laminas\View\Model\ViewModel
     */
    public function indexAction()
    {
        $applicationPublish = $this->getApplicationPublish($this->getIdentifier());

        // if validation errors show cannot publish page
        if (!empty($applicationPublish['errors'])) {
            return $this->cannotPublish($applicationPublish['errors']);
        }

        if ($this->getRequest()->isPost()) {
            $this->publishApplication($this->getIdentifier());
            return $this->redirect()->toRouteAjax('lva-' . $this->lva, ['application' => $this->getIdentifier()]);
        }

        // if application has an existing publication then show republish page
        if ($applicationPublish['existingPublication']) {
            return $this->republish($applicationPublish['hasActiveS4']);
        } else {
            return $this->publish($applicationPublish['hasActiveS4']);
        }
    }

    /**
     * Render the publish errors page
     *
     * @param array $errors errors
     *
     * @return \Laminas\View\Model\ViewModel
     */
    protected function cannotPublish($errors)
    {
        $form = $this->getMessageForm();
        $form->setMessage($errors);
        $form->removeOkButton();

        return $this->render(
            'publish_application_error',
            $form
        );
    }

    /**
     * Render the publish page
     *
     * @param string $hasActiveS4 hasActiveS4
     *
     * @return \Laminas\View\Model\ViewModel
     */
    protected function publish($hasActiveS4)
    {
        $form = $this->getMessageForm();
        $form->setMessage(
            $hasActiveS4 ? 'application.publish-s4.confirm.message' : 'application.publish.confirm.message'
        );
        $form->setOkButtonLabel('Publish');

        return $this->render(
            'publish_application_publish',
            $form
        );
    }

    /**
     * Render the republish page
     *
     * @param string $hasActiveS4 hasActiveS4
     *
     * @return \Laminas\View\Model\ViewModel
     */
    protected function republish($hasActiveS4)
    {
        $form = $this->getMessageForm();
        $form->setMessage(
            $hasActiveS4 ? 'application.publish-s4.confirm.message' : 'application.republish.confirm.message'
        );
        $form->setOkButtonLabel('Republish');

        return $this->render(
            'publish_application_republish',
            $form
        );
    }

    /**
     * Get the generic Message form
     */
    protected function getMessageForm()
    {
        return $this->formHelper->createFormWithRequest('Message', $this->getRequest());
    }

    /**
     * Get Application Publish data
     *
     * @param int $applicationId applicationId
     *
     * @return array application entity data + publish data
     */
    protected function getApplicationPublish($applicationId)
    {
        $response = $this->handleQuery(\Dvsa\Olcs\Transfer\Query\Application\Publish::create(['id' => $applicationId]));
        if (!$response->isOk()) {
            throw new \RuntimeException('Error getting application publication data');
        }
        return $response->getResult();
    }

    /**
     * Publish the applciation
     *
     * @param int $applicationId applicationId
     *
     * @return true
     */
    protected function publishApplication($applicationId)
    {
        $response = $this->handleCommand(
            \Dvsa\Olcs\Transfer\Command\Application\Publish::create(['id' => $applicationId])
        );
        if (!$response->isOk()) {
            throw new \RuntimeException('Error publishing application');
        }
        return true;
    }
}
