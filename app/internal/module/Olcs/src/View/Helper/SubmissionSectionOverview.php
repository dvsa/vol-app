<?php

namespace Olcs\View\Helper;

use Laminas\I18n\Translator\TranslatorInterface as Translator;
use Laminas\I18n\Translator\TranslatorAwareInterface;
use Laminas\View\Helper\AbstractHelper;
use Laminas\Form\Exception;

/**
 * View helper to render the submission section
 *
 */
class SubmissionSectionOverview extends AbstractHelper
{
    public const DEFAULT_VIEW = 'sections/cases/pages/submission/details';

    /**
     * Type map to views
     *
     * @var array
     */
    protected $typeViewMap = [
        'case-summary'   => 'sections/cases/pages/submission/summary',
        'case-outline'   => 'sections/cases/pages/submission/outline',
        'penalties'      => 'sections/cases/pages/submission/penalties',
        'conviction-fpn-offence-history'   => 'sections/cases/pages/submission/table',
        'most-serious-infringement'   => 'sections/cases/pages/submission/most-serious-infringement',
        'tm-details'   => 'sections/cases/pages/submission/tm-details',
    ];

    /**
     * Renders the data for a SubmissionSection details
     *
     * @param String $submissionSection
     * @param Array $data
     * @param bool $readonly
     * @return string
     */
    public function __invoke($submissionSection = '', $data = [], $readonly = false)
    {

        if (empty($submissionSection)) {
            return '';
        }

        return $this->render($submissionSection, $data, $readonly);
    }

    /**
     * Renders the data for a SubmissionSection details
     *
     * @param String $submissionSection
     * @param Array $data
     * @param bool $readonly
     *
     * @return string
     */
    public function render($submissionSection, $data, $readonly)
    {
        $data['data']['overview'] ??= [];
        $viewTemplate = $this->typeViewMap[$submissionSection] ?? self::DEFAULT_VIEW;

        return $this->getView()->render($viewTemplate, ['data' => $data]);
    }
}
