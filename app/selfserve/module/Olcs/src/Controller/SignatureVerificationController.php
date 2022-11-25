<?php

namespace Olcs\Controller;

use Common\FeatureToggle;
use Dvsa\Olcs\Transfer\Command\GovUkAccount\ProcessAuthResponse;


class SignatureVerificationController extends AbstractSelfserveController
{
    protected $toggleConfig = [
        'default' => FeatureToggle::GOVUK_ACCOUNT
    ];

    public function indexAction()
    {
        // Check for errors
        if (!is_null($this->getRequest()->getQuery('error')) || !is_null($this->getRequest()->getQuery('error_description'))) {
            throw new \Exception(
                "There was error with the response from GOV.UK Account "
                . json_encode([
                    'error' => $this->getRequest()->getQuery('error'),
                    'errorDescription' => $this->getRequest()->getQuery('error_description'),
                ])
            );
        }

        if (is_null($this->getRequest()->getQuery('code'))) {
            throw new \Exception("Response from GOV.UK Account is missing param 'code'");
        }
        if (is_null($this->getRequest()->getQuery('state'))) {
            throw new \Exception("Response from GOV.UK Account is missing param 'state'");
        }

        $response = $this->handleCommand(ProcessAuthResponse::create([
            'code' => $this->getRequest()->getQuery('code'),
            'state' => $this->getRequest()->getQuery('state'),
        ]));

        if (!$response->isOk()) {
            throw new \Exception("There was an error with GovUKAccount/ProcessAuthResponse", $response->getStatusCode());
        }

        $result = $response->getResult();
        $redirectUrl = $result['flags']['redirect_url'] ?? null;

        if (empty($redirectUrl)) {
            throw new \Exception("Response from GovUKAccount/ProcessAuthResponse was OK but does not redirect_url");
        }

        return $this->redirect()->toUrl($redirectUrl);
    }
}
