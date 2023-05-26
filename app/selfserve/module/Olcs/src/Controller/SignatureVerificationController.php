<?php

namespace Olcs\Controller;

use Common\FeatureToggle;
use Common\RefData;
use Common\Service\Cqrs\Response;
use Dvsa\Olcs\Transfer\Command\GovUkAccount\ProcessAuthResponse;
use Laminas\Stdlib\Parameters;
use Olcs\Logging\Log\Logger;


class SignatureVerificationController extends AbstractSelfserveController
{
    protected $toggleConfig = [
        'default' => FeatureToggle::GOVUK_ACCOUNT
    ];

    public function indexAction()
    {
        $response = $this->handleCommand(ProcessAuthResponse::create([
            'error' => $this->getRequest()->getQuery('error'),
            'errorDescription' => $this->getRequest()->getQuery('errorDescription'),
            'code' => $this->getRequest()->getQuery('code'),
            'state' => $this->getRequest()->getQuery('state'),
        ]));

        $result = $response->getResult();
        $redirectUrl = $result['flags']['redirect_url'] ?? null;
        $error = $result['flags']['error'] ?? null;

        if (empty($redirectUrl)) {
            throw new \Exception("GovUKAccount/ProcessAuthResponse was OK but specified no redirect URL: " . json_encode($response->getResult()), $response->getStatusCode());
        }

        if (!empty($error)) {
            $this->flashMessenger()->getContainer()->offsetSet('govUkAccountError', true);
        }

        return $this->redirect()->toUrl($redirectUrl);
    }
}
