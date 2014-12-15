<?php

/**
 * Revoke Controller
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
namespace Olcs\Controller\Cases\Processing;

// Olcs
use Olcs\Controller as OlcsController;
use Olcs\Controller\Traits as ControllerTraits;

/**
 * Revoke Controller
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
class RevokeController extends OlcsController\CrudAbstract
{
    use ControllerTraits\CaseControllerTrait;

    /**
     * Identifier name
     *
     * @var string
     */
    protected $identifierName = 'case';

    /**
     * Placeholder name
     *
     * @var string
     */
    protected $placeholderName = 'proposeToRevoke';

    /**
     * Table name string
     *
     * @var string
     */
    protected $tableName = 'revoke';

    /**
     * Holds the form name
     *
     * @var string
     */
    protected $formName = 'revoke';

    /**
     * The current page's extra layout, over and above the
     * standard base template, a sibling of the base though.
     *
     * @var string
     */
    protected $pageLayout = 'case';

    /**
     * For most case crud controllers, we use the view-new/layouts/case-inner-layout
     * layout file. Except submissions.
     *
     * @var string
     */
    protected $pageLayoutInner = 'view-new/layouts/case-inner-layout';

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'proposeToRevoke';

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represneted by a single navigation id.
     */
    protected $navigationId = 'case_processing_in_office_revocation';

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
            'reasons' => array(
                'properties' => 'ALL'
            ),
            'presidingTc' => array(
                'properties' => 'ALL'
            )
        )
    );

    /**
     * Holds the details view
     *
     * @return array|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    protected $detailsView = 'view-new/pages/case/in-office-revocation';

    /**
     * Is the result a result of REST call to getList. Set to true when
     * identifierKey is not 'id'
     *
     * @var bool
     */
    protected $isListResult = true;

    /**
     * Identifier key
     *
     * @var string
     */
    protected $identifierKey = 'case';

    /**
     * Ensure index action redirects to details action
     *
     * @return array|mixed|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        return $this->redirectToIndex();
    }

    /**
     * Override to redirect to details page
     *
     * @return mixed|\Zend\Http\Response
     */
    public function redirectToIndex()
    {
        return $this->redirectToRoute(null, ['action' => 'details'], [], true);
    }
}
