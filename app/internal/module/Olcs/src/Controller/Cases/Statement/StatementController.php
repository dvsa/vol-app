<?php

/**
 * Case Statement Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\Controller\Cases\Statement;

// Olcs
use Olcs\Controller as OlcsController;
use Olcs\Controller\Traits as ControllerTraits;

/**
 * Case Statement Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class StatementController extends OlcsController\CrudAbstract
{
    use ControllerTraits\CaseControllerTrait;

    /**
     * Identifier name
     *
     * @var string
     */
    protected $identifierName = 'statement';

    /**
     * Table name string
     *
     * @var string
     */
    protected $tableName = 'statement';

    /**
     * Holds the form name
     *
     * @var string
     */
    protected $formName = 'statement';

    /**
     * The current page's extra layout, over and above the
     * standard base template, a sibling of the base though.
     *
     * @var string
     */
    protected $pageLayout = 'case';

    /**
     * For most case crud controllers, we use the layout/case-details-subsection
     * layout file. Except submissions.
     *
     * @var string
     */
    protected $pageLayoutInner = 'layout/case-details-subsection';

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'Statement';

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represneted by a single navigation id.
     */
    protected $navigationId = 'case_details_statements';

    /**
     * Holds an array of variables for the
     * default index list page.
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
        /* '_addresses' => array(
            'requestorsAddress'
        ), */
        'main' => array(
            'mapFrom' => array(
                'fields',
                //'requestorsAddress'
            )
        )
    );

    /**
     * Holds the isAction
     *
     * @var boolean
    */
    protected $isAction = false;

    /**
     * Holds the Data Bundle
     *
     * @var array
     */
    protected $dataBundle = array(
        'children' => array(
            'contactType' => array(
                'properties' => 'ALL'
            ),
            'statementType' => array(
                'properties' => 'ALL'
            ),
            'requestorsAddress' => array(
                'properties' => 'ALL',
                'children' => array(
                    'countryCode' => array(
                        'properties' => array('id')
                    )
                )
            ),
            'case' => array(
                'properties' => 'ALL',
            ),
        )
    );

    /**
     * Transforms the data prior to saving.
     *
     * @param array $data
     * @return array
     */
    public function processSave($data)
    {
        unset($data['requestorsAddress']['searchPostcode']);

        $data['fields']['addresses']['requestorsAddress'] = $data['requestorsAddress'];

        return parent::processSave($data);
    }

    /**
     * Map the data on load
     *
     * @param array $data
     * @return array
     */
    public function processLoad($data)
    {
        if (isset($data['requestorsAddress'])) {
            $address = $data['requestorsAddress'];

            $data = parent::processLoad($data);

            $data['requestorsAddress'] = $address;

            $data['requestorsAddress']['countryCode'] = $address['countryCode']['id'];
        } else {
            $data = parent::processLoad($data);
        }

        return $data;
    }
}
