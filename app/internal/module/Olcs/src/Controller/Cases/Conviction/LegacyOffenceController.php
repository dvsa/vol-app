<?php

namespace Olcs\Controller\Cases\Conviction;

use Common\Service\Cqrs\Response;
use Olcs\Controller\Cases\CaseController;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Common\Controller\Traits\GenericRenderView;
use Zend\View\Model\ViewModel;

/**
 * Class LegacyOffenceController
 */
class LegacyOffenceController extends AbstractActionController implements CaseControllerInterface
{
    use GenericRenderView {
        GenericRenderView::renderView as parentRenderView;
    }

    public $pageTitle = '';
    public $pageSubTitle = '';
    protected $headerViewTemplate = 'partials/header';
    protected $pageLayout = 'case-section';

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represneted by a single navigation id.
     */
    protected $navigationId = 'case_details_legacy_offence_details';

    /**
     * Load an array of script files which will be rendered inline inside a view
     *
     * @param array $scripts
     * @return array
     */
    protected function loadScripts($scripts)
    {
        return $this->getServiceLocator()->get('Script')->loadFiles($scripts);
    }

    /**
     * Optionally add scripts to view, if there are any
     *
     * @param ViewModel $view
     */
    protected function maybeAddScripts($view)
    {
        $scripts = [];

        if (empty($scripts)) {
            return;
        }

        // this process defers to a service which takes care of checking
        // whether the script(s) exist
        $this->loadScripts($scripts);
    }

    /**
     * Sets the view helper placeholder namespaced value.
     *
     * @param string $namespace
     * @param mixed $content
     */
    public function setPlaceholder($namespace, $content)
    {
        $this->getServiceLocator()->get('ViewHelperManager')->get('placeholder')
            ->getContainer($namespace)->set($content);
    }

    /**
     * Extend the render view method
     *
     * @param string|\Zend\View\Model\ViewModel $view
     * @param string|null $pageTitle
     * @param string|null $pageSubTitle
     * @return \Zend\View\Model\ViewModel
     */
    protected function renderView($view, $pageTitle = null, $pageSubTitle = null)
    {
        $pageLayoutInner = 'layout/case-details-subsection';

        if (property_exists($this, 'navigationId')) {
            $this->setPlaceholder('navigationId', $this->navigationId);
        }

        if (!is_null($pageLayoutInner)) {

            // This is a zend\view\variables object - cast it to an array.
            $layout = new ViewModel((array)$view->getVariables());

            $layout->setTemplate($pageLayoutInner);

            $this->maybeAddScripts($layout);

            $layout->addChild($view, 'content');

            return $this->parentRenderView($layout, $pageTitle, $pageSubTitle);
        }

        $this->maybeAddScripts($view);
        return $this->parentRenderView($view, $pageTitle, $pageSubTitle);
    }

    /**
     * Index Action. Generates the list of legacy offences.
     */
    public function indexAction()
    {
        $view = new ViewModel([]);
        $view->setTemplate('partials/table');

        $response = $this->getLegacyOffenceList();

        if ($response->isNotFound()) {
            return $this->notFoundAction();
        }

        if ($response->isClientError() || $response->isServerError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
            //this probably should end up on a different page...
        }

        if ($response->isOk()) {
            $data = $response->getResult();
            $table = $this->generateResultsTable($data);
            $view->setVariable('table', $table);
        }

        return $this->renderView($view);
    }

    /**
     * Method to build the table from results data
     * @param $data
     * @return mixed
     */
    private function generateResultsTable($data)
    {
        if (!isset($data['url'])) {
            $data['url'] = $this->getPluginManager()->get('url');
        }

        $params = $this->getListParamsForTable();

        return $this->getServiceLocator()->get('Table')->buildTable('legacyOffences', $data, $params,
            false);
    }

    /**
     * Method to display details of a legacy offence
     * @return array|ViewModel
     */
    public function detailsAction()
    {
        $view = new ViewModel(['readonly' => true]);
        $view->setTemplate('pages/case/offence');

        $response = $this->getLegacyOffence();

        if ($response->isNotFound()) {
            return $this->notFoundAction();
        }

        if ($response->isClientError() || $response->isServerError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
            //this probably should end up on a different page...
        }

        if ($response->isOk()) {
            $data = $response->getResult();

            if (isset($data)) {
                $this->setPlaceholder('details', $data);
            }
        }

        return $this->renderView($view);
    }

    /**
     * Gets a single legacy offence by case and legacy offence ID
     * @return Response
     */
    protected function getLegacyOffence()
    {
        $dto = new \Dvsa\Olcs\Transfer\Query\Cases\LegacyOffence();
        $dto->exchangeArray(
            [
                'case' => $this->params()->fromRoute('case'),
                'id' => $this->params()->fromRoute('id')
            ]
        );

        return $this->handleQuery($dto);
    }

    /**
     * Gets a list of legacy offences by case ID
     * @return Response
     */
    protected function getLegacyOffenceList()
    {
        $dto = new \Dvsa\Olcs\Transfer\Query\Cases\LegacyOffenceList();
        $dto->exchangeArray(
            array_merge(
                $this->getListParams(),
                [
                    'case' => $this->params()->fromRoute('case')
                ]
            )
        );

        return $this->handleQuery($dto);
    }

    /**
     * Return params suitable for pagination
     * @return array
     */
    private function getListParams()
    {
        return [
            'page'    => $this->params()->fromQuery('page', 1),
            'sort'    => $this->params()->fromQuery('sort', 'id'),
            'order'   => $this->params()->fromQuery('order', 'ASC'),
            'limit'   => $this->params()->fromQuery('limit', 10),
        ];
    }

    /**
     * Returns array of params with additional query for the table
     * @return array
     */
    private function getListParamsForTable()
    {
        $params = $this->getListParams();

        $params['query'] = $this->getRequest()->getQuery();

        return $params;
    }
}
