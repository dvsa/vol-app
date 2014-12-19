<?php

/**
 * Case Opposition Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace Olcs\Controller\Cases\Opposition;

// Olcs
use Olcs\Controller as OlcsController;
use Olcs\Controller\Traits as ControllerTraits;

/**
 * Case Opposition Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class OppositionController extends OlcsController\CrudAbstract
    implements OlcsController\Interfaces\CaseControllerInterface
{
    use ControllerTraits\CaseControllerTrait;

    /**
     * Table name string
     *
     * @var string
     */
    protected $tableName = 'opposition';

    /**
     * Holds the form name
     *
     * @var string
     */
    protected $formName = 'opposition';

    /**
     * The current page's extra layout, over and above the
     * standard base template, a sibling of the base though.
     *
     * @var string
     */
    protected $pageLayout = 'case-section';

    protected $pageLayoutInner = 'layout/wide-layout';

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'Opposition';

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represneted by a single navigation id.
     */
    protected $navigationId = 'case_opposition';

    /**
     * Holds an array of variables for the default
     * index list page.
     */
    protected $listVars = [
        'application'
    ];

    /**
     * Data map
     *
     * @var array
    */
    protected $dataMap = array(
        'main' => array(
            'mapFrom' => array(
                'fields'
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
            'application' => array(
                'properties' => array(
                    'id',
                    'receivedDate'
                ),
                'children' => array(
                    'operatingCentres' => array(
                        'properties' => array(
                            'adPlacedDate'
                        )
                    )
                )
            ),
            'oppositionType' => array(
                'properties' => array(
                    'description'
                )
            ),
            'opposer' => array(
                'children' => array(
                    'contactDetails' => array(
                        'children' => array(
                            'person' => array(
                                'properties' => array(
                                    'forename',
                                    'familyName'
                                )
                            )
                        )
                    )
                )
            ),
            'grounds' => array(
                'children' => array(
                    'grounds' => array(
                        'properties' => array(
                            'id',
                            'description'
                        )

                    )
                )
            )
        )
    );

    public function indexAction()
    {
        $view = $this->getView([]);

        $this->checkForCrudAction(null, [], $this->getIdentifierName());

        $this->buildTableIntoView();

        //we will already have list data
        $listData = $this->getListData();

        //operating centre is linked to the application so we only need to check the first one
        if (isset($listData['Results'][0]['application']['operatingCentres'][0]['adPlacedDate'])) {
            $operatingCentres = $listData['Results'][0]['application']['operatingCentres'];
            rsort($operatingCentres);

            $newspaperDate = $operatingCentres[0]['adPlacedDate'];
            $receivedDate = $listData['Results'][0]['application']['receivedDate'];

            $viewVars = $this->calculateDates($receivedDate, $newspaperDate);
        } else {
            $viewVars = [
                'oooDate' => null,
                'oorDate' => null
            ];
        }

        $view->setVariables($viewVars);
        $view->setTemplate('pages/case/opposition');

        return $this->renderView($view);
    }

    private function calculateDates($applicationDate, $newsPaperDate)
    {
        $appDateObj = new \DateTime($applicationDate);
        $appDateObj->setTime(0, 0, 0); //is from a datetime db field - stop the time affecting the 21 day calculation
        $newsDateObj = new \DateTime($newsPaperDate);

        if ($appDateObj > $newsDateObj) {
            $oorDate = null;
        } else {
            $newsDateObj->add(new \DateInterval('P21D'));

            //we could format the date here but returning the date in ISO format
            //allows us to format the date using the configured view helper
            $oorDate = $newsDateObj->format(\DateTime::ISO8601);
        }

        return [
            'oooDate' => null,
            'oorDate' => $oorDate
        ];
    }
}
