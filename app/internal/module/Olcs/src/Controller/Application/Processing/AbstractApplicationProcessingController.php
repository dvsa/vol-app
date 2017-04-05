<?php

/**
 * Abstract Application Processing Controller
 */
namespace Olcs\Controller\Application\Processing;

use Olcs\Controller\Application\ApplicationController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Traits\ProcessingControllerTrait;

/**
 * Abstract Application Processing Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
abstract class AbstractApplicationProcessingController extends ApplicationController implements LeftViewProvider
{
    use ProcessingControllerTrait;

    protected $helperClass = '\Olcs\Helper\ApplicationProcessingHelper';

    /**
     * get method for Navigation config
     *
     * @return array
     */
    protected function getNavigationConfig()
    {
        $application = $this->getApplication();

        return $this->getProcessingHelper()->getNavigation(
            $application['id'],
            $this->section
        );
    }
}
