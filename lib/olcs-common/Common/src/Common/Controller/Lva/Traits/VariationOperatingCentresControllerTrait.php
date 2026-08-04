<?php

namespace Common\Controller\Lva\Traits;

use Dvsa\Olcs\Transfer\Command\Variation\RestoreOperatingCentre;

/**
 * Common variation OC controller logic
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
trait VariationOperatingCentresControllerTrait
{
    /**
     * Action Restore Operation Center
     *
     * @return \Laminas\Http\Response
     */
    public function restoreAction()
    {
        $data = [
            'id' => $this->params('child_id'),
            'application' => $this->getIdentifier()
        ];

        /** @var \Common\Service\Cqrs\Response $response */
        $response = $this->handleCommand(RestoreOperatingCentre::create($data));

        if ($response->isOk()) {
            return $this->redirect()
                ->toRouteAjax($this->getBaseRoute(), ['action' => null, 'child_id' => null], [], true);
        }

        /** @var \Common\Service\Helper\FlashMessengerHelperService $hlpFlashMsgr */
        $hlpFlashMsgr = $this->flashMessengerHelper;
        if ($response->isServerError()) {
            $hlpFlashMsgr->addUnknownError();
        } else {
            $hlpFlashMsgr->addErrorMessage("Can't restore this record");
        }

        return $this->redirect()->toRouteAjax(null, ['action' => null, 'child_id' => null], [], true);
    }
}
