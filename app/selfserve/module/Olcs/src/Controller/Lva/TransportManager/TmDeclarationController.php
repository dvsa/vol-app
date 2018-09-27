<?php

namespace OLCS\Controller\Lva\TransportManager;

use Olcs\Controller\Lva\Traits\ExternalControllerTrait;
use Dvsa\Olcs\Transfer\Command;

class TmDeclarationController extends AbstractDeclarationController
{
    use ExternalControllerTrait;

    protected $declarationMarkup = 'markup-tma-tm_declaration';

    /**
     * Action for when the operator chooses to digitally sign the transport manager application
     *
     *
     *
     * @return void
     */
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
            "lva-transport_manager/check_answers/action",
            [
                'action' => 'index',
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
            Command\TransportManagerApplication\Submit::create(['id' => $this->tma['id']])
        );
        return $response;
    }

    /**
     * @return string
     */
    protected function getSubmitActionLabel(): string
    {
        $label = $this->tma['disableSignatures'] === false
            ? 'application.review-declarations.sign-button'
            : 'submit-for-operator-approval';
        return $label;
    }
}
