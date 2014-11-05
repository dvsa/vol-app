<?php

namespace Olcs\View\Helper;

use Zend\I18n\Translator\TranslatorInterface as Translator;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\View\Helper\AbstractHelper;
use Zend\Form\Exception;

/**
 * View helper to render the submission section
 *
 */
class SubmissionSectionOverview extends AbstractHelper
{
    const DEFAULT_VIEW = '/case/submission/submission-section-overview';

    /**
     * Type map to views
     *
     * @var array
     */
    protected $typeViewMap = array(
        'introduction'   => '/case/submission/section/details',
        'case-summary'   => '/case/submission/section/case-summary',
        'case-outline'   => '/case/submission/section/case-outline',
        'most-serious-infringement'   => '/case/submission/section/details',
        'persons'   => '/case/submission/section/details',
        'operating-centres'   => '/case/submission/section/details',
        'conditions-and-undertakings'   => '/case/submission/section/details',
        'intelligence-unit-check'   => '/case/submission/section/details',
        'interim'   => '/case/submission/section/details',
        'advertisement'   => '/case/submission/section/details',
        'linked-licences-app-numbers'   => '/case/submission/section/details',
        'lead-tc-area'   => '/case/submission/section/details',
        'current-submissions'   => '/case/submission/section/details',
        'auth-requested-applied-for'   => '/case/submission/section/details',
        'transport-managers'   => '/case/submission/section/details',
        'continuous-effective-control'   => '/case/submission/section/details',
        'fitness-and-repute'   => '/case/submission/section/details',
        'previous-history'   => '/case/submission/section/details',
        'bus-reg-app-details'   => '/case/submission/section/details',
        'transport-authority-comments'   => '/case/submission/section/details',
        'total-bus-registrations'   => '/case/submission/section/details',
        'local-licence-history'   => '/case/submission/section/details',
        'linked-mlh-history'   => '/case/submission/section/details',
        'registration-details'   => '/case/submission/section/details',
        'maintenance-tachographs-hours'   => '/case/submission/section/details',
        'prohibition-history'   => '/case/submission/section/details',
        'conviction-fpn-offence-history'   => '/case/submission/section/table',
        'annual-test-history'   => '/case/submission/section/details',
        'penalties'   => '/case/submission/section/details',
        'other-issues'   => '/case/submission/section/details',
        'te-reports'   => '/case/submission/section/details',
        'site-plans'   => '/case/submission/section/details',
        'planning-permission'   => '/case/submission/section/details',
        'applicants-comments'   => '/case/submission/section/details',
        'visibility-access-egress-size'   => '/case/submission/section/details',
        'compliance-complaints'   => '/case/submission/section/details',
        'environmental-complaints'   => '/case/submission/section/details',
        'oppositions'   => '/case/submission/section/details',
        'financial-information'   => '/case/submission/section/details',
        'maps'   => '/case/submission/section/details',
        'waive-fee-late-fee'   => '/case/submission/section/details',
        'surrender'   => '/case/submission/section/details',
        'annex'   => '/case/submission/section/details'
    );

    /**
     * Renders the data for a SubmissionSection details
     *
     * @param  String $submissionSection
     * @param Array $data
     * @return string
     */
    public function __invoke($submissionSection = '', $data = array())
    {

        if (empty($submissionSection)) {
            return '';
        }

        return $this->render($submissionSection, $data);
    }

    public function render($submissionSection, $data)
    {
        $viewTemplate = isset($this->typeViewMap[$submissionSection]) ?
            $this->typeViewMap[$submissionSection] : self::DEFAULT_VIEW;

        return $this->getView()->render($viewTemplate, ['data' => $data]);
    }
}
