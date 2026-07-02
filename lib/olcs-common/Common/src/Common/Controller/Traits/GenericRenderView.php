<?php

/**
 * Generic Render View
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Controller\Traits;

use Laminas\View\Model\ViewModel;

/**
 * Generic Render View
 *
 * - Render View logic moved here so it can be re-used without extending AbstractActionController
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
trait GenericRenderView
{
    /**
     * Wrapper method to render a view with optional title and sub title values
     *
     * @param string|ViewModel $view
     * @param string $pageTitle
     * @param string $pageSubTitle
     *
     * @return ViewModel
     */
    protected function renderView($content, $pageTitle = null, $pageSubTitle = null)
    {
        // allow for very simple views to be passed as a string. Obviously this
        // precludes the passing of any template variables but can still come
        // in handy when no extra variables need to be set
        if (is_string($content)) {
            $viewName = $content;
            $content = new ViewModel();
            $content->setTemplate($viewName);
        }

        if ($pageTitle !== null) {
            $this->setPageTitle($pageTitle);
        }

        if ($pageSubTitle !== null) {
            $this->setPageSubTitle($pageSubTitle);
        }

        return $this->viewBuilder()->buildView($content);
    }

    /**
     * Sets the page title
     *
     * @param array $pageTitle
     * @return $this
     */
    public function setPageTitle($pageTitle)
    {
        $this->placeholder()->setPlaceholder('pageTitle', $pageTitle);
        return $this;
    }

    /**
     * Sets the page sub title
     *
     * @param array $pageSubTitle
     * @return $this
     */
    public function setPageSubTitle($pageSubTitle)
    {
        $this->placeholder()->setPlaceholder('pageSubtitle', $pageSubTitle);
        return $this;
    }

    /**
     * Gets a view model with optional params
     *
     * @return ViewModel
     */
    public function getView(array $params = null)
    {
        return new ViewModel($params);
    }
}
