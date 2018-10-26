<?php

namespace OLCS\Controller\Lva\TransportManager;

use Common\RefData;
use Dvsa\Olcs\Transfer\Command;
use Common\Service\Entity\TransportManagerApplicationEntityService;

class TmDeclarationController extends AbstractDeclarationController
{

    protected $declarationMarkup = 'markup-tma-tm_declaration';

    protected function getSignAsRole(): string
    {
        return $this->tma['isOwner'] === "N" ? RefData::TMA_SIGN_AS_TM : RefData::TMA_SIGN_AS_TM_OP;
    }

    /**
     * Get the URL/link to go back
     *
     * @return string
     */
    protected function getBackLink(): string
    {
        return $this->url()->fromRoute(
            'lva-' . $this->lva . '/transport_manager_check_answer',
            [
                'action' => 'index',
                'child_id' => $this->tma["id"],
                'application' => $this->tma["application"]["id"]
            ]
        );
    }

    /**
     * @return \Common\Service\Cqrs\Response
     */
    protected function handlePhysicalSignatureCommand(): \Common\Service\Cqrs\Response
    {
        $response = $this->handleCommand(
            Command\TransportManagerApplication\Submit::create(['id' => $this->tma['id']])
        );
        return $response;
    }

    /**
     * @return string
     */
    protected function getSubmitActionLabel(): string
    {
        $submitText = $this->tma['isOwner'] === "Y" ? 'submit' : 'submit-for-operator-approval';

        $label = $this->tma['disableSignatures'] === false
            ? 'application.review-declarations.sign-button'
            : $submitText;
        return $label;
    }

    /**
     * Is user permitted to access this controller
     *
     * @return bool
     */
    protected function isUserPermitted()
    {
        if ($this->tma['isTmLoggedInUser'] &&
            $this->tma['tmApplicationStatus']['id'] ===
            TransportManagerApplicationEntityService::STATUS_DETAILS_CHECKED) {
            return true;
        }
        return false;
    }
}
