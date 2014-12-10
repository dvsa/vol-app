<?php

/**
 * Bus Processing Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Bus\Processing;

use Olcs\Controller\Bus\BusController;

/**
 * Bus Processing Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusProcessingController extends BusController
{
    protected $section = 'processing';
    protected $subNavRoute = 'licence_bus_processing';

    /**
     * Holds an array of variables for the
     * default index list page.
     */
    protected $listVars = [
        'licence',
        'busRegId'
    ];
}
