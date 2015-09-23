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
     * @param String $submissionSection
     * @param Array $data
     * @param bool $readonly
     * @return string
     */
    public function __invoke($submissionSection = '', $data = array(), $readonly = false, $submissionVersion = null)
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
     * @param int $submissionVersion
     *
     * @return string
     */
    public function render($submissionSection, $data, $readonly, $submissionVersion = null)
    {
        $params = ['submissionVersion' => $submissionVersion];

        $tableConfig = isset($this->tableMap[$submissionSection]) ?
            $this->tableMap[$submissionSection] : 'SubmissionSections/' . $submissionSection;
        $tableData = isset($data['data']['tables'][$submissionSection]) ?
            $data['data']['tables'][$submissionSection] : [];

        $tableBuilder = $this->getTableBuilder()->buildTable(
            $tableConfig,
            ['Results' => $tableData],
            $params,
            false
        );

        if ($readonly) {
            // disable for readonly
            $tableBuilder->setDisabled(true);
        }

        return $tableBuilder->render();
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
