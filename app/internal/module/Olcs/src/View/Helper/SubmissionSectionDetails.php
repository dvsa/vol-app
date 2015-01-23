<?php

namespace Olcs\View\Helper;

use Zend\I18n\Translator\TranslatorInterface as Translator;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\View\Helper\AbstractHelper;
use Zend\Form\Exception;
use Olcs\View\Helper\SubmissionSectionOverview;

/**
 * View helper to render the submission sections
 *
 */
class SubmissionSectionDetails extends AbstractHelper
{
    const DEFAULT_HELPER = 'submissionsectionoverview';

    /**
     * Type map to view helper
     *
     * @var array
     */
    protected $typeMap = array(
        'introduction'                      => 'SubmissionSectionOverview',
        'case-summary'                      => 'SubmissionSectionOverview',
        'case-outline'                      => 'SubmissionSectionOverview',
        'outstanding-applications'          => 'SubmissionSectionTable',
        'most-serious-infringement'         => 'SubmissionSectionOverview',
        'persons'                           => 'SubmissionSectionTable',
        'operating-centres'                 => 'SubmissionSectionOverview',
        'conditions-and-undertakings'       => 'SubmissionSectionMultipleTables',
        'intelligence-unit-check'           => 'SubmissionSectionOverview',
        'interim'                           => 'SubmissionSectionOverview',
        'advertisement'                     => 'SubmissionSectionOverview',
        'linked-licences-app-numbers'       => 'SubmissionSectionTable',
        'lead-tc-area'                      => 'SubmissionSectionOverview',
        'current-submissions'               => 'SubmissionSectionOverview',
        'auth-requested-applied-for'        => 'SubmissionSectionTable',
        'transport-managers'                => 'SubmissionSectionOverview',
        'continuous-effective-control'      => 'SubmissionSectionOverview',
        'fitness-and-repute'                => 'SubmissionSectionOverview',
        'previous-history'                  => 'SubmissionSectionOverview',
        'bus-reg-app-details'               => 'SubmissionSectionOverview',
        'transport-authority-comments'      => 'SubmissionSectionOverview',
        'total-bus-registrations'           => 'SubmissionSectionOverview',
        'local-licence-history'             => 'SubmissionSectionOverview',
        'linked-mlh-history'                => 'SubmissionSectionOverview',
        'registration-details'              => 'SubmissionSectionOverview',
        'maintenance-tachographs-hours'     => 'SubmissionSectionOverview',
        'prohibition-history'               => 'SubmissionSectionTable',
        'conviction-fpn-offence-history'    => 'SubmissionSectionTable',
        'annual-test-history'               => 'SubmissionSectionOverview',
        'penalties'                         => ['SubmissionSectionOverview', 'SubmissionSectionMultipleTables'],
        'other-issues'                      => 'SubmissionSectionOverview',
        'te-reports'                        => 'SubmissionSectionOverview',
        'site-plans'                        => 'SubmissionSectionOverview',
        'planning-permission'               => 'SubmissionSectionOverview',
        'applicants-comments'               => 'SubmissionSectionOverview',
        'visibility-access-egress-size'     => 'SubmissionSectionOverview',
        'compliance-complaints'             => 'SubmissionSectionTable',
        'environmental-complaints'          => 'SubmissionSectionTable',
        'oppositions'                       => 'SubmissionSectionTable',
        'financial-information'             => 'SubmissionSectionOverview',
        'maps'                              => 'SubmissionSectionOverview',
        'waive-fee-late-fee'                => 'SubmissionSectionOverview',
        'surrender'                         => 'SubmissionSectionOverview',
        'annex'                             => 'SubmissionSectionOverview',
        'statements'                        => 'SubmissionSectionTable',
    );

    /**
     * Default helper name
     *
     * @var string
     */
    protected $defaultHelper = self::DEFAULT_HELPER;

    /**
     * Renders the data for a SubmissionSection details from the provided $submissionSection
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

    /**
     * Renders the appropriate View Helper for the submissionSection
     *
     * @param String $submissionSection
     * @param Array $data
     *
     * @return mixed
     */
    protected function render($submissionSection, $data)
    {

        $renderer = $this->getView();

        if (!method_exists($renderer, 'plugin')) {
            // Bail early if renderer is not pluggable
            return '';
        }
        $markup = '';
        if (isset($this->typeMap[$submissionSection])) {

            if (is_array($this->typeMap[$submissionSection])) {
                foreach ($this->typeMap[$submissionSection] as $type) {
                    $markup .= $this->renderHelper($type, $submissionSection, $data);
                }
            } else {
                $markup .= $this->renderHelper($this->typeMap[$submissionSection], $submissionSection, $data);
            }
            return $markup;
        }
        return null;
    }

    /**
     * Render element by helper name
     *
     * @param string $name
     * @param ElementInterface $element
     * @return string
     */
    protected function renderHelper($name, $submissionSection, $data)
    {
        $helper = $this->getView()->plugin($name);
        return $helper($submissionSection, $data);
    }
}
