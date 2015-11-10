<?php

/**
 * Guides Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * Guides Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class GuidesController extends AbstractActionController
{
    const GUIDE_OC_ADV_GB_NEW = 'advertising-your-operating-centre-gb-new';
    const GUIDE_OC_ADV_GB_VAR = 'advertising-your-operating-centre-gb-var';
    const GUIDE_OC_ADV_NI_NEW = 'advertising-your-operating-centre-ni-new';
    const GUIDE_OC_ADV_NI_VAR = 'advertising-your-operating-centre-ni-var';
    const GUIDE_PRIVACY_AND_COOKIES = 'privacy-and-cookies';
    const GUIDE_TERMS_AND_CONDITIONS = 'terms-and-conditions';
    const GUIDE_FINANCIAL_EVIDENCE = 'financial-evidence';

    protected $guideMap = [
        self::GUIDE_OC_ADV_GB_NEW => 'oc_advert',
        self::GUIDE_OC_ADV_GB_VAR => 'oc_advert',
        self::GUIDE_OC_ADV_NI_NEW => 'oc_advert',
        self::GUIDE_OC_ADV_NI_VAR => 'oc_advert',
        self::GUIDE_PRIVACY_AND_COOKIES => 'default',
        self::GUIDE_TERMS_AND_CONDITIONS => 'default',
        self::GUIDE_FINANCIAL_EVIDENCE => 'default',
    ];

    public function indexAction()
    {
        $guide = $this->params('guide');

        if (!isset($this->guideMap[$guide])) {
            return $this->notFoundAction();
        }

        $partial = $this->guideMap[$guide];

        $view = new ViewModel(['guide' => $guide]);
        $view->setTemplate('pages/guides/' . $partial);

        return $view;
    }
}
