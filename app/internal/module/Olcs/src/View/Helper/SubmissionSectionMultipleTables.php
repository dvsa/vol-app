<?php

namespace Olcs\View\Helper;

use Laminas\View\Helper\AbstractHelper;

/**
 * View helper to render the submission section
 *
 */
class SubmissionSectionMultipleTables extends AbstractHelper
{
    public const DEFAULT_VIEW = 'sections/cases/pages/submission/table';

    /**
     * View map
     *
     * @var array
     */
    protected $viewMap = [];

    /**
     * @var \Laminas\I18n\Translator\Translator
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
     * @param String $submissionSection
     * @param Array $data
     * @param bool $readonly
     * @return string
     */
    public function __invoke($submissionSection = '', $data = [], $readonly = false, $submissionVersion = null)
    {
        if (empty($submissionSection)) {
            return '';
        }

        return $this->render($submissionSection, $data, $readonly, $submissionVersion);
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
    public function render($submissionSection, $data, $readonly, $submissionVersion = null)
    {
        $html = '';

        $viewTemplate = $this->viewMap[$submissionSection] ?? self::DEFAULT_VIEW;

        $tableViewHelper = $this->getView()->plugin('SubmissionSectionTable');

        $tables = $data['data']['tables'] ?? [];
        foreach ($tables as $subSection => $tableData) {
            $html .= $tableViewHelper(
                $subSection,
                [
                    'description' => $this->getTranslator()->translate($data['sectionId'] . '-' . $subSection),
                    'data' => $data['data']
                ],
                $readonly,
                $submissionVersion
            );
        }

        $data['tables'] = $html;

        // config set to remove the section header if an overview already has it
        if (
            isset($data['config']['show_multiple_tables_section_header']) &&
            $data['config']['show_multiple_tables_section_header'] == false
        ) {
            return $html;
        }

        return $this->getView()->render($viewTemplate, ['data' => $data]);
    }

    /**
     * @param \Laminas\I18n\Translator\Translator $translator
     */
    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }

    /**
     * @return \Laminas\I18n\Translator\Translator
     */
    public function getTranslator()
    {
        return $this->translator;
    }
}
