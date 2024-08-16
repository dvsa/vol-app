<?php

namespace Olcs\Controller\Lva\Variation;

use Common\FormService\FormServiceManager;
use Common\RefData;
use Common\Service\Helper\FormHelperService;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Lva\AbstractOverviewController;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;
use Olcs\Service\Helper\ApplicationOverviewHelperService;
use Olcs\View\Model\Variation\VariationOverview;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Variation Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OverviewController extends AbstractOverviewController
{
    use VariationControllerTrait;

    protected $lva = 'variation';
    protected string $location = 'external';

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param FormServiceManager $formServiceManager
     * @param FormHelperService $formHelper
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FormServiceManager $formServiceManager,
        FormHelperService $formHelper
    ) {
        parent::__construct(
            $niTextTranslationUtil,
            $authService,
            $formServiceManager,
            $formHelper
        );
    }

    /**
     * Get overview view
     *
     * @param array             $data     data
     * @param array             $sections sections
     * @param \Common\Form\Form $form     form
     *
     * @return VariationOverview
     */
    protected function getOverviewView($data, $sections, $form)
    {
        $this->placeholder()->setPlaceholder('pageTitle', 'application.apply-to-change-licence.title');
        return new VariationOverview($data, $sections, $form);
    }

    /**
     * Is ready to submit
     *
     * @param array $sections                 variation sections
     * @param bool  $shouldIgnoreUndertakings should we ignore undertakings section
     *
     * @return bool
     */
    protected function isReadyToSubmit($sections, $shouldIgnoreUndertakings = false)
    {
        $updated = 0;
        foreach ($sections as $key => $section) {
            if ($shouldIgnoreUndertakings && $key === RefData::UNDERTAKINGS_KEY) {
                continue;
            }
            if ($section['status'] === RefData::VARIATION_STATUS_REQUIRES_ATTENTION) {
                return false;
            }
            if ($section['status'] === RefData::VARIATION_STATUS_UPDATED) {
                $updated++;
            }
        }
        return ($updated > 0);
    }

    /**
     * Get sections
     *
     * @param array $data variation data
     *
     * @return array e.g. [ 'section_name' => ['status' => 2] ]
     */
    protected function getSections($data)
    {
        $sections = $this->getVariationSections($data);
        $sections[RefData::UNDERTAKINGS_KEY]['enabled'] = $this->isReadyToSubmit($sections, true);
        return $sections;
    }
}
