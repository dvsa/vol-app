<?php

namespace Olcs\View\Helper;

use Zend\I18n\Translator\TranslatorInterface as Translator;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\View\Helper\AbstractHelper;
use Zend\Form\Exception;

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
        'submission_section_intr'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'submission_section_casu'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'submission_section_case'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'submission_section_msin'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'submission_section_pers'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'submission_section_opce'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'submission_section_ochi'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'submission_section_ctud'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'submission_section_inuc'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'submission_section_intm'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'submission_section_advt'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'submission_section_llan'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'submission_section_alau'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'submission_section_ltca'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'submission_section_cusu'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'submission_section_auth'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'submission_section_trma'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'submission_section_cnec'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'submission_section_fire'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'submission_section_preh'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'submission_section_brad'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'submission_section_trac'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'submission_section_tbus'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'submission_section_llhi'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'submission_section_mlhh'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'submission_section_regd'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'submission_section_mtdh'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'submission_section_proh'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'submission_section_cpoh'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'submission_section_pens'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'submission_section_misc'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'submission_section_terp'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'submission_section_site'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'submission_section_plpm'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'submission_section_acom'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'submission_section_vaes'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'submission_section_comp'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'submission_section_envc'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'submission_section_reps'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'submission_section_objs'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'submission_section_fnin'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'submission_section_maps'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'submission_section_wflf'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'submission_section_surr'   => 'Olcs\View\Helper\SubmissionSectionOverview',
        'submission_section_annx'   => 'Olcs\View\Helper\SubmissionSectionOverview'
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
            return $this;
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
    public function render($submissionSection, $data)
    {
        if (empty($submissionSection)) {
            return $this;
        }

        $renderer = $this->getView();
        if (!method_exists($renderer, 'plugin')) {
            // Bail early if renderer is not pluggable
            return '';
        }

        if (isset($this->typeMap[$submissionSection])) {
            return $this->renderHelper($this->typeMap[$submissionSection], $submissionSection, $data);
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
