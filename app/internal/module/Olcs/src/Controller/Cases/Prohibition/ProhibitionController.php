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
use Olcs\Controller\Interfaces\CaseControllerInterface;

    /**
     * Case Prohibition Controller
     *
     * @author Ian Lindsay <ian@hemera-business-services.co.uk>
     */
class ProhibitionController extends OlcsController\CrudAbstract implements CaseControllerInterface
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
     * Name of comment box field.
     *
     * @var string
     */
    protected $commentBoxName = 'prohibitionNote';

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
    protected $pageLayout = 'case-section';

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

    protected $inlineScripts = ['table-actions'];

    /**
     * Gets Prohibition details from within ProhibitionDefectController.
     * We don't need to return anything here, however we do to assist with unit testing.
     *
     * @return array
     */
    public function detailsAction()
    {
        $prohibitionDetails = $this->loadCurrent();

        $this->getViewHelperManager()->get('placeholder')->getContainer('prohibition')->set(
            $prohibitionDetails
        );

        return $prohibitionDetails;
    }
}
