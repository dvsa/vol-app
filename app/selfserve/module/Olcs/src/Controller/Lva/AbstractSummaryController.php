<?php

/**
 * External Abstract Summary Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\Controller\Lva;

use Common\Controller\Lva\AbstractController;
use Common\RefData;
use Dvsa\Olcs\Transfer\Query\Application\Summary as Qry;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\View\Model\ViewModel;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * External Abstract Summary Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractSummaryController extends AbstractController
{
    protected string $location = 'external';

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     */
    public function __construct(NiTextTranslation $niTextTranslationUtil, AuthorizationService $authService)
    {
        parent::__construct($niTextTranslationUtil, $authService);
    }

    /**
     * Index action
     *
     * @return \Common\View\Model\Section
     */
    public function indexAction()
    {
        return $this->renderSummary($this->getParams(true));
    }

    /**
     * post submit summary
     *
     * @return \Common\View\Model\Section
     */
    public function postSubmitSummaryAction()
    {
        return $this->renderSummary($this->getParams());
    }

    /**
     * Render summary
     *
     * @param array $params params
     *
     * @return \Common\View\Model\Section
     */
    public function renderSummary($params)
    {
        $template = 'pages/application-summary';

        if (!empty($params['autoGrantChanges'])) {
            $template = 'pages/auto-grant-success';
            $params['changes'] = $params['autoGrantChanges']['messages'] ?? [];
        }

        $view = new ViewModel($params);
        $view->setTemplate($template);
        return $this->render($view);
    }

    /**
     * get params
     *
     * @param bool $justPaid just paid
     *
     * @return array
     */
    protected function getParams($justPaid = false)
    {
        $id = $this->getIdentifier();

        $dto = Qry::create(['id' => $id]);
        $response = $this->handleQuery($dto);
        $data = $response->getResult();

        $reference = $this->params()->fromRoute('reference') ?: $data['reference'];
        return [
            'justPaid' => $justPaid,
            'lva' => $this->lva,
            'licence' => $data['licence']['licNo'],
            'application' => $data['id'],
            'canWithdraw' => $data['canWithdraw'],
            'status' => $data['status']['description'],
            'submittedDate' => $data['receivedDate'],
            'completionDate' => $data['targetCompletionDate'],
            'paymentRef' => $reference,
            'actions' => $data['actions'],
            'transportManagers' => $data['transportManagers'] ?: [],
            'outstandingFee' => $data['outstandingFee'],
            'importantText' => $this->getImportantText($data),
            'hideContent' => ($data['appliedVia']['id'] !== RefData::APPLIED_VIA_SELFSERVE),
            'interimStatus' => isset($data['interimStatus']) ? $data['interimStatus']['description'] : null,
            'interimStart' => isset($data['interimStatus']) ? $data['interimStart'] : null,
            'isNi' => isset($data['niFlag']) && $data['niFlag'] === 'Y' ? true : false,
            'getWasAutoGranted' => $data['wasAutoGranted'] ?? false,
            'autoGrantChanges' => $data['autoGrantChanges'] ?? []
        ];
    }

    /**
     * Get the important text translation key for an application/variation
     *
     * @param array $applicationData Application data
     *
     * @return string translation key
     */
    protected function getImportantText($applicationData)
    {
        $isVariation = $applicationData['isVariation'];
        $licenceType = $applicationData['licenceType']['id'];
        $goodsOrPsv = $applicationData['goodsOrPsv']['id'];

        if ($goodsOrPsv === RefData::LICENCE_CATEGORY_GOODS_VEHICLE) {
            return $isVariation ? 'application-summary-important-goods-var' : 'application-summary-important-goods-app';
        } else {
            if ($isVariation) {
                return 'application-summary-important-psv-var';
            } else {
                if ($licenceType === RefData::LICENCE_TYPE_SPECIAL_RESTRICTED) {
                    return 'application-summary-important-psv-app-sr';
                } else {
                    return 'application-summary-important-psv-app';
                }
            }
        }
    }
}
