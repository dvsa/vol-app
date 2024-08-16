<?php

namespace Olcs\View\Helper;

use Laminas\I18n\Translator\TranslatorInterface as Translator;
use Laminas\I18n\Translator\TranslatorAwareInterface;
use Laminas\View\Helper\AbstractHelper;
use Laminas\Form\Exception;
use Common\Service\Table\TableFactory;

/**
 * View helper to render the submission section
 *
 */
class SubmissionSectionTable extends AbstractHelper
{
    public const DEFAULT_VIEW = 'sections/cases/pages/submission/table';

    private $tableBuilder;

    /**
     * View map
     *
     * @var array
     */
    protected $viewMap = [
        'conviction-fpn-offence-history'
    ];

    /**
     * Table config map
     *
     * @var array
     */
    protected $tableMap = [];

    /**
     * Renders the data for a SubmissionSection details
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
     * @param int $submissionVersion
     *
     * @return string
     */
    public function render($submissionSection, $data, $readonly, $submissionVersion = null)
    {
        $params = ['submissionVersion' => $submissionVersion];

        $tableConfig = $this->tableMap[$submissionSection] ?? 'SubmissionSections/' . $submissionSection;
        $tableData = $data['data']['tables'][$submissionSection] ?? [];

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
