<?php

/**
 * CaseController Test
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */

namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Olcs\Controller\Cases\Hearing\StayController;
use OlcsTest\Controller\ControllerTestAbstract;

/**
 * CaseController Test
 */
class CaseControllerTest extends ControllerTestAbstract
{
    protected $testClass = 'Olcs\Controller\Cases\CaseController';

    protected $proxyMethdods = [
        'redirectAction' => 'redirectToRoute',
        'indexAction' => 'redirectToRoute'
    ];
}
