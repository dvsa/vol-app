<?php

/**
 * Printing Controller
 */
namespace Admin\Controller;

use Common\Controller\AbstractActionController;

/**
 * Printing Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class PrintingController extends AbstractActionController
{
    /**
     * Index action
     *
     * @return \Zend\Http\Response
     */
    public function indexAction()
    {
        return $this->redirectToRoute(
            'admin-dashboard/admin-printing/irfo-stock-control',
            ['action'=>'index'],
            ['code' => '303'], // Why? No cache is set with a 303 :)
            true
        );
    }
}
