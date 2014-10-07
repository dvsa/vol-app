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
class SubmissionSectionTable extends AbstractHelper
{

    /**
     * Type map to views
     *
     * @var array
     */
    protected $tableMap = array(
        'conviction-fpn-offence-history'   => '/case/submission/section/details',
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
        $table = isset($this->tableMap[$submissionSection]) ?
            $this->typeViewMap[$submissionSection] : self::DEFAULT_VIEW;

        return $this->getView()->render('/case/submission/submission-section-table', ['data' => $data]);
    }
}
