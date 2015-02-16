<?php

/**
 * Processing Test Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */

namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Mockery as m;
use OlcsTest\Controller\ControllerTestAbstract;

/**
 * Processing Test Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class ProcessingControllerTest extends ControllerTestAbstract
{
    protected $testClass = 'Olcs\Controller\Cases\Processing\ProcessingController';

    protected $proxyMethdods = [
        'overviewAction' => 'redirectToRoute',
        'redirectToIndex' => 'redirectToRoute',
        'detailsAction' => 'redirectToIndex',
    ];
}
