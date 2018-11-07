<?php

namespace OLCS\Controller\Lva\TransportManager;

use Common\Controller\Lva\AbstractController;
use Common\RefData;
use Olcs\Controller\Lva\Traits\ExternalControllerTrait;
use Olcs\Controller\Lva\Traits\TransportManagerApplicationTrait;
use Common\Service\Entity\TransportManagerApplicationEntityService;

class ConfirmationController extends AbstractController
{
    use ExternalControllerTrait,
        TransportManagerApplicationTrait;

    const TM_MARKUP = 'markup-tma-confirmation-tm';

    const OPERATOR_MARKUP = 'markup-tma-confirmation-operator';

    protected $tma;

    protected $markup = self::OPERATOR_MARKUP;

    protected $signature;

    /**
     * index action for /transport-manager/:TmaId/confirmation route
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $translationHelper = $this->getServiceLocator()->get('Helper\Translation');

        $this->signature = $this->tma['opDigitalSignature'];

        $this->setMarkupAndSignatureIfTm();

        $params = [
            'content' => $translationHelper->translateReplace(
                $this->markup,
                [
                    $this->getSignatureFullName($this->signature),
                    $this->getSignatureDate($this->signature),
                    $this->getBacklink()
                ]
            ),
            'tmFullName' => $this->getTmName(),
        ];

        return $this->renderTmAction(null, null, $params);
    }

    /**
     * Render the Transport manager application confirmation pages
     *
     * @param string            $title  Title
     * @param \Common\Form\Form $form   Form
     * @param array             $params Params
     *
     * @return \Zend\View\Model\ViewModel
     */
    private function renderTmAction($title, $form, $params)
    {
        $defaultParams = [
            'tmFullName' => $this->getTmName(),
            'backLink' => $this->getBacklink(),
        ];

        $params = array_merge($defaultParams, $params);

        $layout = $this->render($title, $form, $params);
        /* @var $layout \Zend\View\Model\ViewModel */

        $content = $layout->getChildrenByCaptureTo('content')[0];
        $content->setTemplate('pages/confirmation');

        return $layout;
    }

    /**
     * Get the URL/link to go back
     *
     * @return string
     */
    private function getBacklink()
    {
        if ($this->isOperatorUserOrAdmin()) {
            return $this->url()->fromRoute(
                "lva-{$this->lva}/transport_managers",
                ['application' => $this->getIdentifier()],
                [],
                false
            );
        }
        // in this context, if not an operator the user is a TM
        return $this->url()->fromRoute('dashboard');
    }

    private function isOperatorUserOrAdmin() :bool
    {
        if ($this->isGranted(RefData::PERMISSION_SELFSERVE_LVA)) {
            return true;
        }
        return false;
    }

    private function getSignatureDate($signature)
    {
        $unixTimeStamp = strtotime($signature['createdOn']);
        return date("j M Y", $unixTimeStamp);
    }

    private function getSignatureFullName($signature)
    {
        $attributes = json_decode($signature['attributes']);
        return $attributes->firstname . ' ' . $attributes->surname;
    }

    /**
     * Is user permitted to access this controller
     *
     * @return bool
     */
    protected function isUserPermitted()
    {
        if ($this->tma['isTmLoggedInUser'] &&
            ($this->tma['tmApplicationStatus']['id'] === TransportManagerApplicationEntityService::STATUS_TM_SIGNED ||
                $this->tma['tmApplicationStatus']['id'] === TransportManagerApplicationEntityService::STATUS_RECEIVED) &&
            !is_null($this->tma['tmDigitalSignature'])) {
            return true;
        }

        if ((!$this->tma['isTmLoggedInUser']) &&
            $this->tma['tmApplicationStatus']['id'] === TransportManagerApplicationEntityService::STATUS_RECEIVED &&
            !is_null($this->tma['opDigitalSignature'])) {
            return true;
        }
        return false;
    }

    private function setMarkupAndSignatureIfTm(): void
    {
        if ($this->tma['isTmLoggedInUser'] && $this->tma["isOwner"] === "N") {
            $this->markup = self::TM_MARKUP;
            $this->signature = $this->tma['tmDigitalSignature'];
        }
    }
}
