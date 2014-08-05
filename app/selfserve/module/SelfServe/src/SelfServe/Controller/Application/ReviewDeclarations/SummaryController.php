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

        foreach ($this->summarySections as $summarySection) {
            list($section, $subSection) = explode('/', $summarySection);

            // extract from our uber form which fieldsets are relevant
            // to this particular section
            $sectionFieldsets = $this->getSectionFieldsets(
                $form,
                $this->formatFormName('Application', $section, $subSection)
            );

            // naturally if we can't yet access this section make sure we hide
            // any fieldsets which relate to it
            if (!$this->isSectionAccessible($section, $subSection)) {
                foreach ($sectionFieldsets as $fieldset) {
                    $form->remove($fieldset);
                }
            } else {

                // if we're in here then check to see if the relevant controller wants
                // to make any extra form alterations based on the fact it is being
                // rendered out of context on the review page
                $controller = $this->getInvokable($summarySection, 'makeFormAlterations');

                if ($controller) {
                    $options = array(
                        // always let the controller know this is a review
                        'isReview'  => true,
                        'isPsv'     => $this->isPsv(),
                        // most forms only have one fieldset, so we pass the
                        // first through to be helpful...
                        'fieldset'  => $sectionFieldsets[0],
                        // ... but pass the rest through too, just in case
                        'fieldsets' => $sectionFieldsets,
                        // finally, at this stage some controllers may alter based on
                        // data available (or not); so pass that through too
                        'data'      => $data
                    );
                    $form = $controller::makeFormAlterations($form, $this, $options);
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
        $this->isAction = false;

        $this->setRenderNavigation(false);
        $this->setLayout('layout/simple');

        $this->generateSummary();
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
     * @param string $service
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
        $controller = $this->getInvokable($tableData['section'], 'getSummaryTableData');
        if ($controller) {
            return $controller::getSummaryTableData($applicationId, $this);
        }
    }

    private function generateSummary()
    {
        $this->summarySections = [];
        $this->formTables = [];

        $fieldsets = array_keys($this->getForm($this->getFormName())->getFieldsets());

        foreach ($fieldsets as $fieldset) {
            if (preg_match("/application_([\w-]+)_([\w-]+)-\d+/", $fieldset, $matches)) {
                $section    = $this->dashToCamel($matches[1]);
                $subSection = $this->dashToCamel($matches[2]);

                $this->summarySections[] = $section . '/' . $subSection;
            }
        }

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

    private function getSectionFieldsets($form, $fieldsetName)
    {
        $fieldsets = array_keys($form->getFieldsets());
        $sectionFieldsets = [];

        foreach ($fieldsets as $fieldset) {
            if (strpos($fieldset, $fieldsetName) !== false) {
                $sectionFieldsets[] = $fieldset;
            }
        }
        return $sectionFieldsets;
    }

}
