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

    protected $tma;

    /**
     * index action for /transport-manager/:TmaId/confirmation route
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $this->getCurrentUser();
        $translationHelper = $this->getServiceLocator()->get('Helper\Translation');

        $confirmationMarkup = $this->tma["isOwner"] === "N" ? 'markup-tma-confirmation-tm' :
            'markup-tma-confirmation-operator';

        $digitalSignature = $this->isTransportManagerRole() ?
            $this->tma['tmDigitalSignature'] :
            $this->tma['opDigitalSignature'];

        $params = [
            'content' => $translationHelper->translateReplace(
                $confirmationMarkup,
                [$this->getSignatureFullName($digitalSignature), $this->getSignatureDate($digitalSignature), $this->getBacklink()]
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
        if ($this->isTransportManagerRole()) {
            return $this->url()->fromRoute('dashboard');
        } else {
            return $this->url()->fromRoute(
                "lva-{$this->lva}/transport_managers",
                ['application' => $this->getIdentifier()],
                [],
                false
            );
        }
    }

    /**
     * is the logged in user just TM, eg not an admin
     *
     * @return bool
     */
    private function isTransportManagerRole()
    {
        return ($this->isGranted(RefData::PERMISSION_SELFSERVE_TM_DASHBOARD) &&
            !$this->isGranted(RefData::PERMISSION_SELFSERVE_LVA));
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
}
