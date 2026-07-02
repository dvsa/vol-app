<?php

namespace Common\Controller;

use Laminas\Mvc\Controller\AbstractActionController as LaminasAbstractActionController;
use Laminas\View\Model\ViewModel;

class GuidesController extends LaminasAbstractActionController
{
    public const GUIDE_OC_ADV_GB_NEW = 'advertising-your-operating-centre-gb-new';

    public const GUIDE_OC_ADV_GB_VAR = 'advertising-your-operating-centre-gb-var';

    public const GUIDE_OC_ADV_NI_NEW = 'advertising-your-operating-centre-ni-new';

    public const GUIDE_OC_ADV_NI_VAR = 'advertising-your-operating-centre-ni-var';

    public const GUIDE_PRIVACY_NOTICE = 'privacy-notice';

    public const GUIDE_TERMS_AND_CONDITIONS = 'terms-and-conditions';

    public const GUIDE_FINANCIAL_EVIDENCE = 'financial-evidence';

    public const GUIDE_ACCESSIBILITY_STATEMENT = 'accessibility-statement';

    public const GUIDE_TRAFFIC_AREA = 'traffic-area';

    public const GUIDE_CONVICTIONS_AND_PENALTIES_GUIDANCE_GB = 'convictions-and-penalties-guidance-gb';

    public const GUIDE_CONVICTIONS_AND_PENALTIES_GUIDANCE_NI = 'convictions-and-penalties-guidance-ni';

    public const GUIDE_RIGHT_FIRST_TIME = 'right-first-time';

    public const MAIN_OCCUPATION_CRITERIA_GUIDANCE = 'main-occupation-criteria-guidance';

    protected $guideMap = [
        self::GUIDE_OC_ADV_GB_NEW => 'oc_advert',
        self::GUIDE_OC_ADV_GB_VAR => 'oc_advert',
        self::GUIDE_OC_ADV_NI_NEW => 'oc_advert',
        self::GUIDE_OC_ADV_NI_VAR => 'oc_advert',
        self::GUIDE_PRIVACY_NOTICE => 'default',
        self::GUIDE_TERMS_AND_CONDITIONS => 'default',
        self::GUIDE_ACCESSIBILITY_STATEMENT => 'default-two-thirds-blank-sidebar',
        self::GUIDE_RIGHT_FIRST_TIME => 'default-two-thirds-blank-sidebar',
        self::GUIDE_FINANCIAL_EVIDENCE => 'default',
        self::GUIDE_TRAFFIC_AREA => 'default',
        self::GUIDE_CONVICTIONS_AND_PENALTIES_GUIDANCE_GB => 'default',
        self::GUIDE_CONVICTIONS_AND_PENALTIES_GUIDANCE_NI => 'default',
        self::MAIN_OCCUPATION_CRITERIA_GUIDANCE => 'default',
    ];

    #[\Override]
    public function indexAction()
    {
        $guide = (string)$this->params('guide');

        if (!isset($this->guideMap[$guide])) {
            return $this->notFoundAction();
        }

        $partial = $this->guideMap[$guide];

        $view = new ViewModel(['guide' => $guide]);
        $view->setTemplate('pages/guides/' . $partial);

        $this->placeholder()->setPlaceholder('pageTitle', $guide . '-title');

        return $view;
    }
}
