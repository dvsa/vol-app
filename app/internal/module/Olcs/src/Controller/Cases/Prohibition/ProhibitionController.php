<?php

/**
 * Case Prohibition Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Cases\Prohibition;

// Olcs
use Olcs\Controller as OlcsController;
use Olcs\Controller\Traits as ControllerTraits;

    /**
     * Case Prohibition Controller
     *
     * @author Ian Lindsay <ian@hemera-business-services.co.uk>
     */
class ProhibitionController extends OlcsController\CrudAbstract
{
    use ControllerTraits\CaseControllerTrait;

    /**
     * Identifier name
     *
     * @var string
     */
    protected $identifierName = 'prohibition';

    /**
     * Table name string
     *
     * @var string
     */
    protected $tableName = 'prohibition';

    /**
     * Holds the form name
     *
     * @var string
     */
    protected $formName = 'prohibition';

    /**
     * The current page's extra layout, over and above the
     * standard base template, a sibling of the base though.
     *
     * @var string
     */
    protected $pageLayout = 'case';

    /**
     * For most case crud controllers, we use the case/inner-layout
     * layout file. Except submissions.
     *
     * @var string
     */
    protected $pageLayoutInner = 'case/inner-layout';

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'Prohibition';

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represneted by a single navigation id.
     */
    protected $navigationId = 'case_details_prohibitions';

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
        'main' => array(
            'mapFrom' => array(
                'fields',
                'base',
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
            'case' => array(
                'properties' => array(
                    'id'
                )
            ),
            'prohibitionType' => array(
                'properties' => array(
                    'id',
                    'description'
                )
            )
        )
    );

    /**
     * Map the data on load
     *
     * @param array $data
     * @return array
     */
    protected function processLoad($data)
    {
        if (isset($data['id'])) {
            $data = $this->replaceIds($data, ['prohibitionType', 'case']);
            $data['fields'] = $data;
            $data['base'] = $data;
        } else {
            $data = [];
            $data['case'] = $this->getQueryOrRouteParam('case');
            $data['base']['case'] = $this->getQueryOrRouteParam('case');
        }

        return $data;
    }
}
