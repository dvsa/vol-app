<?php

namespace Olcs\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * View helper to render the submission section
 *
 */
class SubmissionSectionMultipleTables extends AbstractHelper
{

    /**
     * Table config map
     *
     * @var array
     */
    protected $tableMap = array();

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
        $tableViewHelper = $this->getView()->plugin('SubmissionSectionTable');
        $tables = isset($data['data']['tables']) ?
            $data['data']['tables'] : [];
        foreach ($tables as $subSection => $tableData) {
            $html .= $tableViewHelper(
                $subSection,
                ['description' => ucfirst(str_replace('-', ' ', $subSection)), 'data' => $data['data']]
            );
        }

        return $html;
    }
}
