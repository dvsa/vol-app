<?php

/**
 * Abstract Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Common\Controller\Traits\GenericRenderView;

/**
 * Abstract Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractController extends AbstractActionController
{
    use GenericRenderView;

    /**
     * These properties must be defined to use the GenericRenderView trait
     */
    protected $pageTitle = null;
    protected $pageSubTitle = null;
    protected $pageLayout = 'admin-layout';
    protected $headerViewTemplate = 'partials/header';

    protected function setNavigationId($id)
    {
        $this->getServiceLocator()->get('viewHelperManager')->get('placeholder')
            ->getContainer('navigationId')->set($id);
    }
}
