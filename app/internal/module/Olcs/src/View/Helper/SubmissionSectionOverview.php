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
        'submission_section_intr'   => '/case/submission/section/blank',
        'submission_section_casu'   => '/case/submission/section/case_outline',
        'submission_section_case'   => '/case/submission/section/blank',
        'submission_section_msin'   => '/case/submission/section/blank',
        'submission_section_pers'   => '/case/submission/section/blank',
        'submission_section_opce'   => '/case/submission/section/blank',
        'submission_section_ochi'   => '/case/submission/section/blank',
        'submission_section_ctud'   => '/case/submission/section/blank',
        'submission_section_inuc'   => '/case/submission/section/blank',
        'submission_section_intm'   => '/case/submission/section/blank',
        'submission_section_advt'   => '/case/submission/section/blank',
        'submission_section_llan'   => '/case/submission/section/blank',
        'submission_section_alau'   => '/case/submission/section/blank',
        'submission_section_ltca'   => '/case/submission/section/blank',
        'submission_section_cusu'   => '/case/submission/section/blank',
        'submission_section_auth'   => '/case/submission/section/blank',
        'submission_section_trma'   => '/case/submission/section/blank',
        'submission_section_cnec'   => '/case/submission/section/blank',
        'submission_section_fire'   => '/case/submission/section/blank',
        'submission_section_preh'   => '/case/submission/section/blank',
        'submission_section_brad'   => '/case/submission/section/blank',
        'submission_section_trac'   => '/case/submission/section/blank',
        'submission_section_tbus'   => '/case/submission/section/blank',
        'submission_section_llhi'   => '/case/submission/section/blank',
        'submission_section_mlhh'   => '/case/submission/section/blank',
        'submission_section_regd'   => '/case/submission/section/blank',
        'submission_section_mtdh'   => '/case/submission/section/blank',
        'submission_section_proh'   => '/case/submission/section/blank',
        'submission_section_cpoh'   => '/case/submission/section/blank',
        'submission_section_pens'   => '/case/submission/section/blank',
        'submission_section_misc'   => '/case/submission/section/blank',
        'submission_section_terp'   => '/case/submission/section/blank',
        'submission_section_site'   => '/case/submission/section/blank',
        'submission_section_plpm'   => '/case/submission/section/blank',
        'submission_section_acom'   => '/case/submission/section/blank',
        'submission_section_vaes'   => '/case/submission/section/blank',
        'submission_section_comp'   => '/case/submission/section/blank',
        'submission_section_envc'   => '/case/submission/section/blank',
        'submission_section_reps'   => '/case/submission/section/blank',
        'submission_section_objs'   => '/case/submission/section/blank',
        'submission_section_fnin'   => '/case/submission/section/blank',
        'submission_section_maps'   => '/case/submission/section/blank',
        'submission_section_wflf'   => '/case/submission/section/blank',
        'submission_section_surr'   => '/case/submission/section/blank',
        'submission_section_annx'   => '/case/submission/section/blank'
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
            return $this;
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
