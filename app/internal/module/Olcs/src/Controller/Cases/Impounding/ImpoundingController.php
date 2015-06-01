<?php

namespace Olcs\Controller\Cases\Impounding;

use Common\Service\Cqrs\Response;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Common\Controller\Traits\GenericRenderView;
use Zend\View\Model\ViewModel;

/**
 * Class ImpoundingController
 */
class ImpoundingController extends AbstractActionController implements CaseControllerInterface
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
    protected $navigationId = 'case_details_impounding';

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
        $scripts = ['forms/impounding', 'table-actions'];

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
     * Index Action. Generates the list of impoundings for a case.
     */
    public function indexAction()
    {
        $view = new ViewModel([]);
        $view->setTemplate('pages/table-comments');

        $response = $this->getImpoundingList();


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
                $table = $this->buildTable($data);
                $view->setVariable('table', $table);
            }
        }

        return $this->renderView($view);
    }

    /**
     * Method to build the table from results data
     * @param $data
     * @return mixed
     */
    private function buildTable($data)
    {
        if (!isset($data['url'])) {
            $data['url'] = $this->getPluginManager()->get('url');
        }

        return $this->getServiceLocator()->get('Table')->buildTable('impounding', $data['results'], $data, false);
    }

    /**
     * Gets a list of legacy offences by case ID
     * @return Response
     */
    protected function getImpoundingList()
    {
        $dto = new \Dvsa\Olcs\Transfer\Query\Cases\ImpoundingList();
        $dto->exchangeArray(
            [
                'case' => $this->params()->fromRoute('case')
            ]
        );

        return $this->handleQuery($dto);
    }
}
