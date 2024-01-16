<?php

namespace Dvsa\Olcs\Application\Controller;

use Common\FormService\FormServiceManager;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Dvsa\Olcs\Application\View\Model\ApplicationOverview;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Lva\AbstractOverviewController;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use LmcRbacMvc\Service\AuthorizationService;

class OverviewController extends AbstractOverviewController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected string $location = 'external';

    protected RestrictionHelperService $restrictionHelper;
    protected StringHelperService $stringHelper;

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param FormServiceManager $formServiceManager
     * @param FormHelperService $formHelper
     * @param RestrictionHelperService $restrictionHelper
     * @param StringHelperService $stringHelper
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FormServiceManager $formServiceManager,
        FormHelperService $formHelper,
        RestrictionHelperService $restrictionHelper,
        StringHelperService $stringHelper
    ) {
        $this->restrictionHelper = $restrictionHelper;
        $this->stringHelper = $stringHelper;

        parent::__construct(
            $niTextTranslationUtil,
            $authService,
            $formServiceManager,
            $formHelper
        );
    }

    protected function getOverviewView($data, $sections, $form)
    {
        return new ApplicationOverview($data, $sections, $form);
    }

    protected function isReadyToSubmit($sections)
    {
        foreach ($sections as $section) {
            if ($section['enabled'] && !$section['complete']) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return array
     *
     * e.g. [ 'section_name' => ['enabled' => true, 'complete' => false] ]
     */
    protected function getSections($data)
    {
        return $this->setEnabledAndCompleteFlagOnSections(
            $data['sections'],
            $data['applicationCompletion']
        );
    }
}
