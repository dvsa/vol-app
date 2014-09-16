<?php

/**
 * Cases Submission Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
namespace Olcs\Controller\Cases\Submission;

use Olcs\Controller as OlcsController;
use Zend\View\Model\ViewModel;
use Olcs\Controller\Cases\AbstractController as AbstractCasesController;
use Olcs\Controller\Traits as ControllerTraits;

/**
 * Cases Submission Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class SubmissionController extends OlcsController\CrudAbstract
{
    use ControllerTraits\CaseControllerTrait;

    /**
     * Identifier name
     *
     * @var string
     */
    protected $identifierName = 'submission';

    /**
     * Table name string
     *
     * @var string
     */
    protected $tableName = 'submission';

    /**
     * Holds the form name
     *
     * @var string
     */
    protected $formName = 'submission';

    /**
     * The current page's extra layout, over and above the
     * standard base template, a sibling of the base though.
     *
     * @var string
     */
    protected $pageLayout = 'case';

    protected $pageLayoutInner = null;

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'Submission';

    /**
     * Holds an array of variables for the default
     * index list page.
     */
    protected $listVars = [
        'case',
    ];

    /**
     * Data map
     *
     * @var array
     */
    protected $dataMap = array(
        'main' => array(
            'mapFrom' => array(
                'data'
            )
        )
    );

    /**
     * Holds the Data Bundle
     *
     * @var array
     */
    protected $dataBundle = array(
        'children' => array(
            'submissionActions' => array(
                'properties' => 'ALL',
                'children' => array(
                    'senderUser' => array(
                        'properties' => 'ALL'
                    ),
                    'recipientUser' => array(
                        'properties' => 'ALL'
                    ),
                )
            )
        )
    );

    /**
     * Save data
     *
     * @param array $data
     * @param string $service
     * @return array
     */
    protected function save($data, $service = null)
    {
        // Modify $data

        return parent::save($data, $service);
    }

    /**
     * Map the data on load
     *
     * @param array $data
     * @return array
     */
    protected function processLoad($data)
    {
        // modify $data for form population

        return parent::processLoad($data);
    }
}