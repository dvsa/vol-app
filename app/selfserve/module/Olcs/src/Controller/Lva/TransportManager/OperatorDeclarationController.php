<?php

namespace OLCS\Controller\Lva\TransportManager;

use Common\Form\Form;
use Olcs\Controller\Lva\Traits\ExternalControllerTrait;
use Dvsa\Olcs\Transfer\Command;

class OperatorDeclarationController extends AbstractDeclarationController
{
    protected $declarationMarkup = 'markup-tma-operator_declaration';

    protected function digitalSignatureAction()
    {
        // write method body
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
            Command\TransportManagerApplication\OperatorApprove::create(['id' => $this->tma['id']])
        );
        return $response;
    }

    protected function alterDeclarationForm(Form $form): void
    {
        if ($this->tma['digitalSignature'] === null) {
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
}
