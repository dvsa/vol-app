<?php

namespace Olcs\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * View helper to render the submission section
 *
 */
class SubmissionSectionMultipleTables extends AbstractHelper
{
    const DEFAULT_VIEW = 'partials/submission-table';

    /**
     * View map
     *
     * @var array
     */
    protected $viewMap = array();

    /**
     * @var \Zend\I18n\Translator\Translator
     */
    protected $translator;

    /**
     * Renders the data for a SubmissionSection details $data expected consists of multidimentional array:
     * [
     * <tableName> => <tableData>,
     * <tableName> => <tableData>
     * ...
     * ]
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
        $html = '';

        $viewTemplate = isset($this->viewMap[$submissionSection]) ?
            $this->viewMap[$submissionSection] : self::DEFAULT_VIEW;

        $tableViewHelper = $this->getView()->plugin('SubmissionSectionTable');

        $tables = isset($data['data']['tables']) ?
            $data['data']['tables'] : [];
        foreach ($tables as $subSection => $tableData) {
            $html .= $tableViewHelper(
                $subSection,
                [
                    'description' => $this->getTranslator()->translate($data['sectionId'] . '-' . $subSection),
                    'data' => $data['data']
                ]
            );
        }

        $data['tables'] = $html;
        return $this->getView()->render($viewTemplate, ['data' => $data]);
    }

    /**
     * @param \Zend\I18n\Translator\Translator $translator
     */
    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }

    /**
     * @return \Zend\I18n\Translator\Translator
     */
    public function getTranslator()
    {
        return $this->translator;
    }
}
