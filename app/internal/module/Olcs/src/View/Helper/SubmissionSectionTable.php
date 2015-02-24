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
class SubmissionSectionTable extends AbstractHelper
{

    const DEFAULT_VIEW = 'partials/submission-table';

    private $tableBuilder;

    /**
     * View map
     *
     * @var array
     */
    protected $viewMap = array(
        'conviction-fpn-offence-history'
    );

    /**
     * Table config map
     *
     * @var array
     */
    protected $tableMap = array();

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
        $params = [];
        $html = '';

        $viewTemplate = isset($this->viewMap[$submissionSection]) ?
            $this->viewMap[$submissionSection] : self::DEFAULT_VIEW;

        $tableConfig = isset($this->tableMap[$submissionSection]) ?
            $this->tableMap[$submissionSection] : 'SubmissionSections/' . $submissionSection;
        $tableData = isset($data['data']['tables'][$submissionSection]) ?
            $data['data']['tables'][$submissionSection] : [];

        $html .=  $this->getTableBuilder()->buildTable(
            $tableConfig,
            ['Results' => $tableData],
            $params,
            false
        );

        return $html;
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
