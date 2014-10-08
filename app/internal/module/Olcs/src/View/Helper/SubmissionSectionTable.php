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

    const DEFAULT_VIEW = '/case/submission/section/table';

    private $tableBuilder;

    /**
     * Type map to views
     *
     * @var array
     */
    protected $typeViewMap = array();

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

        $data['table'] = $this->getTableBuilder()->buildTable('conviction-section', $data, $data,
            false);
        $viewTemplate = isset($this->typeViewMap[$submissionSection]) ?
            $this->typeViewMap[$submissionSection] : self::DEFAULT_VIEW;

        return $this->getView()->render($viewTemplate, ['data' => $data]);
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
