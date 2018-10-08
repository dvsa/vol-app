<?php

namespace OLCS\Controller\Lva\TransportManager;

use Common\Form\Form;
use Common\RefData;
use Olcs\Controller\Lva\Traits\ExternalControllerTrait;
use Dvsa\Olcs\Transfer\Command;
use Common\Service\Entity\TransportManagerApplicationEntityService;

class OperatorDeclarationController extends AbstractDeclarationController
{
    protected $declarationMarkup = 'markup-tma-operator_declaration';

    protected function getSignAsRole(): string
    {
        return RefData::TMA_SIGN_AS_OP;
    }

    /**
     * Get the URL/link to go back
     *
     * @param array $tma
     *
     * @return string
     */
    protected function getBackLink(): string
    {
        return $this->url()->fromRoute(
            "lva-" . $this->returnApplicationOrVariation() . "/transport_manager_details",
            [
                'child_id' => $this->tma["id"],
                'application' => $this->tma["application"]["id"]
            ]
        );
    }

    /**
     * @param $tma
     * @return \Common\Service\Cqrs\Response
     */
    protected function handlePhysicalSignatureCommand(): \Common\Service\Cqrs\Response
    {
        $response = $this->handleCommand(
            Command\TransportManagerApplication\OperatorSigned::create(['id' => $this->tma['id']])
        );
        return $response;
    }

    protected function alterDeclarationForm(Form $form): void
    {
        if ($this->tma['opDigitalSignature'] === null) {
            $form->remove('content');
        }

        parent::alterDeclarationForm($form);
    }

    /**
     * @return string
     */
    protected function getSubmitActionLabel(): string
    {
        return 'application.review-declarations.sign-button';
    }

    /**
     * Is user permitted to access this controller
     *
     * @return bool
     */
    protected function isUserPermitted()
    {
        if (!$this->tma['isTmLoggedInUser'] &&
            $this->tma['tmApplicationStatus']['id'] ===
            TransportManagerApplicationEntityService::STATUS_OPERATOR_APPROVED) {
            return true;
        }
        return false;
    }
}
