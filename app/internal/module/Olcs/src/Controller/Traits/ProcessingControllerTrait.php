<?php

namespace Olcs\Controller\Traits;

use Laminas\Navigation\Navigation;
use Laminas\View\Model\ViewModel;

/**
 * ProcessingControllerTrait
 *
 * @package Olcs\Controller
 * @author  Dan Eggleston <dan@stolenegg.com>
 */
trait ProcessingControllerTrait
{
    /**
     * Holds the current section
     *
     * @var string
     */
    protected $section = '';

    /**
     * Holds the processing helper
     *
     * @var \Olcs\Helper\AbstractProcessingHelper
     */
    protected $processingHelper;

    /**
     * Holds the 'parent' entity name, e.g. licence/application/case
     * rather than note/task
     *
     * @var string
     */
    protected $entity;

    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/processing/partials/left');

        return $view;
    }

    /**
     * @return string
     */
    public function getEntityName()
    {
        return $this->entity;
    }

    /**
     * Get the processing helper
     *
     * @return \Olcs\Helper\AbstractProcessingHelper
     */
    protected function getProcessingHelper()
    {
        if (empty($this->processingHelper)) {
            $this->processingHelper = new $this->helperClass();
        }

        return $this->processingHelper;
    }

    /**
     * Get sub navigation
     *
     * @return \Laminas\Navigation\Navigation
     */
    protected function getSubNavigation()
    {
        $navigation = new Navigation($this->getNavigationConfig());

        $router = $this->router;

        foreach ($navigation->getPages() as $page) {
            $page->setRouter($router);
        }

        return $navigation;
    }
}
