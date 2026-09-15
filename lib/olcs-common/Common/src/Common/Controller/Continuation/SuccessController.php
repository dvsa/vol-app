<?php

namespace Common\Controller\Continuation;

use Common\FormService\FormServiceManager;
use Common\RefData;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\View\Model\ViewModel;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Success controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 *
 */
class SuccessController extends AbstractContinuationController
{
    /** @var string */
    protected $layout = 'pages/continuation-success';

    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FormServiceManager $formServiceManager,
        TranslationHelperService $translationHelper
    ) {
        parent::__construct($niTextTranslationUtil, $authService, $formServiceManager, $translationHelper);
    }

    /**
     * Index action to handle payment result
     *
     * @return ViewModel
     */
    #[\Override]
    public function indexAction()
    {
        $data = $this->getContinuationDetailData();
        $licence = $data['licence'];

        $params = [
            'paymentRef' => $data['reference'],
            'isPhysicalSignature' => $data['isPhysicalSignature'],
            'isFinancialEvidenceRequired' => $data['isFinancialEvidenceRequired'],
            'isNi' => $licence['trafficArea']['isNi'],
            'licenceId' => $licence['id'],
            'isSpecialRestricted' => $licence['licenceType']['id'] === RefData::LICENCE_TYPE_SPECIAL_RESTRICTED,
        ];

        // if licence is PSV R, PSV SN or PSV SI
        if (
            $licence['goodsOrPsv']['id'] === RefData::LICENCE_CATEGORY_PSV &&
            ($licence['licenceType']['id'] === RefData::LICENCE_TYPE_RESTRICTED ||
            $licence['licenceType']['id'] === RefData::LICENCE_TYPE_STANDARD_NATIONAL ||
            $licence['licenceType']['id'] === RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL)
        ) {
            $params['numPsvDiscs'] = $data['totPsvDiscs'];
            $params['licenceDocumentationMessage'] = $params['numPsvDiscs'] > 0 ? 'continuation.success.licence-documentation' : 'continuation.success.licence-documentation.zero.discs';
        }

        return $this->getViewModel($licence['licNo'], null, $params);
    }
}
