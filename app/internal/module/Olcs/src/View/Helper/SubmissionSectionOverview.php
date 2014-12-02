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
    const DEFAULT_VIEW = '/case/submission/section/details';

    /**
     * Type map to views
     *
     * @var array
     */
    protected $typeViewMap = array(
        'case-summary'   => '/case/submission/section/case-summary',
        'case-outline'   => '/case/submission/section/case-outline',
        'conviction-fpn-offence-history'   => '/case/submission/section/table',
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
        $data['data']['overview'] = isset($data['data']['overview']) ? $data['data']['overview'] : [];
        $viewTemplate = isset($this->typeViewMap[$submissionSection]) ?
            $this->typeViewMap[$submissionSection] : self::DEFAULT_VIEW;
       return $this->getView()->render($viewTemplate, ['data' => $data]);
    }
}
