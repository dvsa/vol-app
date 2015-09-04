<?php

/**
 * Publication Controller
 */
namespace Admin\Controller;

use Olcs\Controller\CrudAbstract;
use Common\Service\Data\Search\Search;
use Common\Service\Data\Search\SearchType;
use Zend\View\Model\ViewModel;
use Common\Exception\ResourceNotFoundException;
use Common\Exception\BadRequestException;
use Common\Exception\DataServiceException;

/**
 * Publication Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class PublicationController extends CrudAbstract
{
    /**
     * Identifier name
     *
     * @var string
     */
    protected $identifierName = 'publication';

    /**
     * Table name string
     *
     * @var string
     */
    protected $tableName = 'admin-publication';

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
    protected $formName = 'publication';

    /**
     * The current page's extra layout, over and above the
     * standard base template, a sibling of the base though.
     *
     * @var string
     */
    protected $pageLayout = 'admin-publication-section';

    protected $pageLayoutInner = null;

    protected $defaultTableSortField = 'publicationNo';

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'Publication';

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'admin-dashboard/admin-publication';

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
            'pubStatus' => [],
            'trafficArea' => [],
            'document' => []
        ]
    );

    /**
     * Any inline scripts needed in this section
     *
     * @var array
     */
    protected $inlineScripts = array('table-actions', 'file-link');

    /**
     * Entity display name (used by confirm plugin via deleteActionTrait)
     * @var string
     */
    protected $entityDisplayName = 'Publication';

    /**
     * Index action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $this->getViewHelperManager()->get('placeholder')->getContainer('pageTitle')->append('Publications');

        return parent::indexAction();
    }

    /**
     * Gets table params
     *
     * @return array
     */
    public function getTableParams()
    {
        $params = parent::getTableParams();

        $extraParams = [
            'pubStatus' => 'IN ["pub_s_new", "pub_s_generated"]',
        ];

        return array_merge($params, $extraParams);
    }

    public function backAction()
    {
        $sd = $this->ElasticSearch()->getSearchData();

        /**
         * Remove the "index" key from the incoming parameters.
         */
        $index = $sd['index'];
        unset($sd['index']);

        return $this->redirect()->toRoute(
            'admin-dashboard/admin-publication',
            ['index' => $index, 'action' => 'search'],
            ['query' => $sd, 'code' => 303],
            true
        );
    }

    /**
     * Placeholder for published document table
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function publishedAction()
    {
        $options = [
            'layout_template' => 'elastic-search-results-table',
        ];
        $elasticSearch =  $this->ElasticSearch($options);

        $filterForm = $elasticSearch->getFiltersForm();

        $this->setPlaceholder('tableFilters', $filterForm);

        $elasticSearch->processSearchData();

        $view = new ViewModel();

        $view = $elasticSearch->generateResults($view);

        return $this->renderView($view, 'Publications');
    }

    /**
     * Redirect action
     *
     * @return \Zend\Http\Response
     */
    public function redirectAction()
    {
        return $this->redirectToRouteAjax(
            'admin-dashboard/admin-publication/pending',
            ['action'=>'index', $this->getIdentifierName() => null],
            ['code' => '303'], // Why? No cache is set with a 303 :)
            true
        );
    }

    /**
     * Generate action
     *
     * @return mixed|\Zend\Http\Response
     */
    public function generateAction()
    {
        $id = $this->params()->fromRoute('publication');

        $service = $this->getPublicationService();

        try {
            $service->generate($id);
            $this->addSuccessMessage('Publication was generated successfully');
        } catch (DataServiceException $e) {
            $this->addErrorMessage($e->getMessage());
        } catch (ResourceNotFoundException $e) {
            $this->addErrorMessage($e->getMessage());
        }

        return $this->redirectToIndex();
    }

    /**
     * Publish action
     *
     * @return mixed|\Zend\Http\Response
     */
    public function publishAction()
    {
        $id = $this->params()->fromRoute('publication');

        $service = $this->getPublicationService();

        try {
            $service->publish($id);
            $this->addSuccessMessage('Publication was published successfully');
        } catch (DataServiceException $e) {
            $this->addErrorMessage($e->getMessage());
        } catch (ResourceNotFoundException $e) {
            $this->addErrorMessage($e->getMessage());
        }

        return $this->redirectToIndex();
    }

    /**
     * Gets the publication service
     *
     * @return mixed
     */
    private function getPublicationService()
    {
        return $this->getServiceLocator()->get('DataServiceManager')->get('Common\Service\Data\Publication');
    }
}
