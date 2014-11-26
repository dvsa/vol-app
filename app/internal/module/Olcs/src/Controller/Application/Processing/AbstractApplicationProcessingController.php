<?php

/**
 * Abstract Application Processing Controller
 */
namespace Olcs\Controller\Application\Processing;

use Olcs\Controller\Application\ApplicationController;
use Olcs\Helper\ApplicationProcessingHelper;
use Olcs\Controller\Traits\ProcessingControllerTrait;

/**
 * Abstract Application Processing Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
abstract class AbstractApplicationProcessingController extends ApplicationController
{
    use ProcessingControllerTrait;

    /**
     * Anything using renderView in this section will
     * inherit this layout
     *
     * @var string
     */
    protected $pageLayout = 'application';

    protected $helperClass = '\Olcs\Helper\ApplicationProcessingHelper';

    protected function getNavigationConfig()
    {
        $application = $this->getApplication();

        return $this->getProcessingHelper()->getNavigation(
            $application['id'],
            $this->section
        );
    }

    /**
     * @return \Zend\View\Model\ViewModel
     */
    protected function getProcessingLayout($view, $variables)
    {
        $layout = $this->getViewWithApplication(
            array_merge($variables, (array)$view->getVariables())
        );
        $layout->setTemplate('application/processing/layout');

        $layout->addChild($view, 'content');

        return $layout;

    }
}
