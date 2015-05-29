<?php

namespace Olcs\Controller\Cases\PublicInquiry;

use Common\Service\Cqrs\Response;
use Zend\Mvc\Controller\AbstractActionController;
use Common\Controller\Traits\GenericRenderView;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Zend\View\Model\ViewModel;

/**
 * Class PiController
 */
class PiController extends AbstractActionController /*implements CaseControllerInterface*/
{
    use GenericRenderView {
        GenericRenderView::renderView as parentRenderView;
    }

    public $pageTitle = 'test';
    public $pageSubTitle = 'test';
    protected $headerViewTemplate = 'partials/header';
    protected $pageLayout = 'case-section';

        /*
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

    public function detailsAction()
    {
        $view = new ViewModel(['readonly' => false]);
        $view->setTemplate('pages/case/public-inquiry');

        $response = $this->getPi();

        if ($response->isNotFound()) {
            return $this->notFoundAction();
        }

        if ($response->isClientError() || $response->isServerError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
            //this probably should end up on a different page...
        }

        if ($response->isOk()) {
            $data = $response->getResult();
            if (isset($data['pi'])) {
                $this->setPlaceholder('pi', $data['pi']);
            }
        }

        return $this->renderView($view);
    }

    /**
     * @return Response
     */
    protected function getPi()
    {
        $dto = new \Dvsa\Olcs\Transfer\Query\Cases\Pi();
        $dto->exchangeArray(['id' => $this->params()->fromRoute('case')]);

        $query = $this->getServiceLocator()->get('TransferAnnotationBuilder')
            ->createQuery($dto);

        return $this->getServiceLocator()->get('QueryService')->send($query);
    }
}
