<?php

/**
 * Summary Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace SelfServe\Controller\Application\ReviewDeclarations;

use Zend\View\Model\ViewModel;

/**
 * Summary Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class SummaryController extends ReviewDeclarationsController
{
    protected $validateForm = false;

    /**
     * Holds the data bundle
     *
     * @var array
     */
    protected $dataBundle = array(
        'properties' => 'ALL',
        'children' => array(
            'licence' => array(),
            'documents' => array()
        )
    );

    /**
     * Summary sections
     *
     * @var array
     */
    private $summarySections = array(
        'TypeOfLicence/OperatorLocation',
        'TypeOfLicence/OperatorType',
        'TypeOfLicence/LicenceType',
        'PreviousHistory/FinancialHistory',
        'PreviousHistory/LicenceHistory',
        'PreviousHistory/ConvictionsPenalties'
    );

    private $tables = array(
        'application_previous-history_convictions-penalties-2' => array(
            'section' => 'PreviousHistory/ConvictionsPenalties',
            'config'  => 'criminalconvictions'
        )
    );

    /**
     * Render the section form
     *
     * @return Response
     */
    public function indexAction()
    {
        $this->generateSummary();
        return $this->renderSection();
    }

    /**
     * Alter the form
     *
     * @param Form $form
     * @return Form
     */
    protected function alterForm($form)
    {
        $data = $this->loadCurrent();
        $options = array(
            'isPsv'    => $this->isPsv(),
            'isReview' => true
        );

        foreach ($this->summarySections as $summarySection) {
            list($section, $subSection) = explode('/', $summarySection);

            // @NOTE this needs adjusting; we sometimes have more than one fieldset
            $fieldsetName = $this->formatFormName('Application', $section, $subSection) . '-1';

            if (!$this->isSectionAccessible($section, $subSection)) {
                $form->remove($fieldsetName);
            } else {
                $controller = $this->getInvokable($summarySection, 'makeFormAlterations');
                if ($controller) {
                    $newOptions = array_merge(
                        $options,
                        array(
                            'fieldset' => $fieldsetName,
                            'data'     => $data
                        )
                    );
                    $form = $controller::makeFormAlterations($form, $this, $newOptions);
                }
            }
        }
        return $form;
    }

    /**
     * Render the section form
     *
     * @return Response
     */
    public function simpleAction()
    {
        $this->generateSummary();

        $this->isAction = false;

        $this->setRenderNavigation(false);
        $this->setLayout('layout/simple');

        $layout = $this->renderSection();

        if ($layout instanceof ViewModel) {
            $layout->setTerminal(true);
        }

        return $layout;
    }

    /**
     * Placeholder save method
     *
     * @param array $data
     * @parem string $service
     */
    protected function save($data, $service = null)
    {
    }

    /**
     * Process load
     *
     * @param array $loadData
     */
    protected function processLoad($loadData)
    {
        $data = array(
            /**
             * Type of Licence
             */
            'application_type-of-licence_operator-location-1' => array(
                'niFlag' => ($loadData['licence']['niFlag'] == 1 ? '1' : '0')
            ),
            'application_type-of-licence_operator-type-1' => array(
                'goodsOrPsv' => $loadData['licence']['goodsOrPsv']
            ),
            'application_type-of-licence_licence-type-1' => array(
                'licenceType' => $loadData['licence']['licenceType']
            ),

            /**
             * Previous History
             */
            'application_previous-history_financial-history-1' => $this->mapApplicationVariables(
                array(
                    'bankrupt',
                    'liquidation',
                    'receivership',
                    'administration',
                    'disqualified',
                    'insolvencyDetails',
                    'insolvencyConfirmation'
                ),
                $loadData
            ),
            // @NOTE licence history section not yet implemented so no data to map
            'application_previous-history_licence-history-1' => array(),
            'application_previous-history_convictions-penalties-1' => array(
                'prevConviction' => $loadData['prevConviction'] ? 'Y' : 'N'
            ),
            // @NOTE application_previous-history_convictions-penalties-2 is table data
            'application_previous-history_convictions-penalties-3' => $this->mapApplicationVariables(
                array('convictionsConfirmation'),
                $loadData
            )
        );

        return $data;
    }

    protected function mapApplicationVariables($map, $data)
    {
        $final = array();

        foreach ($map as $entry) {
            if (isset($data[$entry])) {
                $final[$entry] = $data[$entry];
            }
        }

        return $final;
    }

    protected function getFormTableData($applicationId, $tableName)
    {
        $tableData = $this->tables[$tableName];
        $controller = $this->getInvokable($tableData['section'], 'getStaticTableData');
        if ($controller) {
            return $controller::getStaticTableData($applicationId, $this);
        }
    }

    private function generateSummary()
    {
        $this->formTables = [];
        foreach ($this->tables as $fieldset => $tableData) {
            $this->formTables[$fieldset] = $tableData['config'];
        }
    }

    private function getInvokable($section, $method)
    {
        list($section, $subSection) = explode('/', $section);
        $controller = '\SelfServe\Controller\Application\\' . $section . '\\' . $subSection . 'Controller';
        if (is_callable(array($controller, $method))) {
            return $controller;
        }
        return null;
    }
}
