<?php

namespace unit\Olcs\Service;

use \OlcsCommon\Controller\AbstractHttpControllerTestCase;
use \Mockery as m;

//use PHPUnit_Framework_TestCase;
//use PHPUnit_Framework_ExpectationFailedException;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LookupServiceTest
 *
 * @author valtechuk
 */
//class LookupServiceTest extends PHPUnit_Framework_TestCase {
class LookupServiceTest extends AbstractHttpControllerTestCase {

    public function setUp($noConfig = false) {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../../../config/test/application.config.php'
        );
        parent::setUp();
    }

    public function testFindAllOperators() {


/*        $licenseNumber = 3;
        $emMock = m::mock('\Doctrine\ORM\EntityManager');

        $operatorMock = m::mock('Olcs\Entity\Operator');
        $repositoryMock = m::mock('Doctrine\ORM\EntityRepository');


        $repositoryMock
                ->shouldReceive('findBy')
                ->with(m::any())
                ->andReturn(array($operatorMock));

        $emMock
                ->shouldReceive('getRepository')
                ->with(m::any())
                ->andReturn($repositoryMock);

        //$lookUpService = new \Olcs\Service\LookupService;

        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $log = new \Zend\Log\Logger();
        $mock = new \Zend\Log\Writer\Mock;
        $log->addWriter($mock);
        $serviceManager->setService('Zend\Log', $log);
        
        $lookupService = $serviceManager->get('lookupServiceFactory');

        $lookupService->setEntityManager($emMock);
        $lookupService->setLogger($log);
        $result = $lookupService->findAllOperators($licenseNumber);
        $this->assertEquals($result, array($operatorMock));
 */
    }

}

?>
