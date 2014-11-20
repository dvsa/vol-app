<?php

namespace Olcs\View\Helper;

use Zend\I18n\Translator\TranslatorInterface as Translator;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\View\Helper\AbstractHelper;
use Zend\Form\Exception;
use Common\Service\Table\TableFactory;

/**
 * View helper to render the submission section
 *
 */
class SubmissionSectionMultipleTables extends AbstractHelper
{

    private $tableBuilder;


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
        foreach ($data['data'] as $subSection => $tableData) {
            $html .= $this->renderHelper('SubmissionSectionTable', $subSection,
                ['description' => $subSection, 'data' => $tableData]);
        }

        return $html;
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

    public function setTableBuilder(TableFactory $tableBuilder)
    {
        $this->tableBuilder = $tableBuilder;
        return $this;
    }

    public function getTableBuilder()
    {
        return $this->tableBuilder;
    }
}
