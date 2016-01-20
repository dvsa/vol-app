<?php

/**
 * Printing Controller
 */
namespace Admin\Controller;

use Olcs\Controller\AbstractInternalController;
use Common\Controller\Traits\GenericMethods;

/**
 * Printing Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class PrintingController extends AbstractInternalController
{
    use GenericMethods;

    /**
     * Index action
     *
     * @return \Zend\Http\Response
     */
    public function indexAction()
    {
        return $this->redirectToRoute(
            'admin-dashboard/admin-disc-printing',
            ['action'=>'index'],
            ['code' => '302'],
            true
        );
    }
}
