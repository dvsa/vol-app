<?php

namespace Olcs\Controller;

use Common\FeatureToggle;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\GovUkAccount\ProcessAuthResponse;
use Permits\Data\Mapper\MapperManager;

class SignatureVerificationController extends AbstractSelfserveController
{
    /**
     * @param TranslationHelperService $translationHelper
     * @param FormHelperService $formHelper
     * @param TableFactory $tableBuilder
     * @param MapperManager $mapperManager
     */
    public function __construct(
        TranslationHelperService $translationHelper,
        FormHelperService $formHelper,
        TableFactory $tableBuilder,
        MapperManager $mapperManager
    ) {
        parent::__construct($translationHelper, $formHelper, $tableBuilder, $mapperManager);
    }

    #[\Override]
    public function indexAction(): \Laminas\Http\Response
    {
        $response = $this->handleCommand(ProcessAuthResponse::create([
            'error' => $this->getRequest()->getQuery('error'),
            'errorDescription' => $this->getRequest()->getQuery('errorDescription'),
            'code' => $this->getRequest()->getQuery('code'),
            'state' => $this->getRequest()->getQuery('state'),
        ]));

        $result = $response->getResult();
        $redirectUrl = $result['flags']['redirect_url'] ?? null;
        $redirectUrlOnError = $result['flags']['redirect_url_on_error'] ?? null;
        $error = $result['flags']['error'] ?? null;

        if (empty($redirectUrl)) {
            throw new \Exception("GovUKAccount/ProcessAuthResponse was OK but specified no redirect URL: " . json_encode($response->getResult()), $response->getStatusCode());
        }

        if (!empty($error)) {
            $this->flashMessenger()->getContainer()->offsetSet('govUkAccountError', true);

            if (!empty($redirectUrlOnError)) {
                $redirectUrl = $redirectUrlOnError;
            }
        }

        return $this->redirect()->toUrl($redirectUrl);
    }
}
