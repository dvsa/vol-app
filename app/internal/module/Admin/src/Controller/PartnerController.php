<?php
/**
 * Partner Controller
 */

namespace Admin\Controller;

use Olcs\Controller\CrudAbstract;

/**
 * Partner Controller
 *
 * @author  Valtech <uk@valtech.co.uk>
 */

class PartnerController extends CrudAbstract
{
    /**
     * Identifier name
     *
     * @var string
     */
    protected $identifierName = 'id';

    /**
     * Table name string
     *
     * @var string
     */
    protected $tableName = 'partner';

    /**
     * Name of comment box field.
     *
     * @var string
     */
    protected $commentBoxName = null;

    /**
     * Holds the form name
     *
     * @var string
     */
    protected $formName = 'partner';

    /**
     * The current page's extra layout, over and above the
     * standard base template, a sibling of the base though.
     *
     * @var string
     */
    protected $pageLayout = 'admin-partner-section';

    protected $pageLayoutInner = null;

    protected $defaultTableSortField = 'id';

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'ContactDetails';

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'admin-dashboard/admin-partner-management';

    /**
     * Holds an array of variables for the default
     * index list page.
     */
    protected $listVars = [];

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
        'children' => [
            'contactType' => [],
            'address' => [],
        ]
    );

    /**
     * Holds any inline scripts for the current page
     *
     * @var array
     */
    protected $inlineScripts = ['table-actions'];

    /**
     * Entity display name (used by confirm plugin via deleteActionTrait)
     * @var string
     */
    protected $entityDisplayName = 'Partner';

    /**
     * Index action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $this->getViewHelperManager()->get('placeholder')->getContainer('pageTitle')->append('Partners');

        return $this->parentIndexAction();
    }

    /**
     * Calls Parent Index Action Method
     *
     * @codeCoverageIgnore
     * @return mixed
     */
    public function parentIndexAction()
    {
        return parent::indexAction();
    }

    /**
     * Gets table params
     *
     * @return array
     */
    public function getTableParams()
    {
        $params = $this->parentGetTableParams();

        $extraParams = ['contactType' => 'ct_partner'];

        return array_merge($params, $extraParams);
    }

    /**
     * Calls Parent Index Action Method
     *
     * @codeCoverageIgnore
     * @return mixed
     */
    public function parentGetTableParams()
    {
        return parent::getTableParams();
    }

    /**
     * Map the data on load
     *
     * @param array $data
     * @return array
     */
    public function processLoad($data)
    {
        if (isset($data['id']) && !empty($data['id'])) {
            $out = [];
            $out['fields'] = $data;
            $out['fields']['contactType'] = $data['contactType']['id'];
            $out['address'] = $data['address'];
        } else {
            $out = [];
        }

        return $out;
    }

    /**
     * Complete section and save
     *
     * @param array $data
     * @return array
     */
    public function processSave($data)
    {
        //$save = [];
        $save = $data['fields'];
        $save['address'] = $data['address'];

        $response = $this->save($save);

        $this->addSuccessMessage('Saved successfully');

        $this->setIsSaved(true);

        return $this->redirectToIndex();
    }
}
