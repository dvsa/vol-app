<?php

namespace unit\Olcs\Service;
use \OlcsCommon\Controller\AbstractHttpControllerTestCase;
use \Mockery as m;

class CaseServiceTest extends AbstractHttpControllerTestCase
{
    protected $traceError = true;

    public function setUp($noConfig = false) {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../../../config/test/application.config.php'
        );
        parent::setUp();
    }
    
    
    public function testForWhenCaseDoesNotExist() {
        
        $case = null;
        $repositoryMock = m::mock('Doctrine\ORM\EntityRepository');
        $repositoryMock
                ->shouldReceive('find')
                ->withAnyArgs()
                ->andReturn($case);
        
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $caseService = $serviceManager->get('CaseServiceFactory');
        $caseService->setEntityManager($repositoryMock);
        
        $caseId = 123;
        $caseSummaryForm = $caseService->getCaseSummaryDetails($caseId);
 
        $this->assertEmpty($caseSummaryForm);
        
    }

    public function testSortOrder() {
        $serviceManager = $this->getApplicationServiceLocator();

        // Below needed to avoid the test trying to write to some real logs
        $serviceManager->setAllowOverride(true);
        $log = new \Zend\Log\Logger();
        $mock = new \Zend\Log\Writer\Mock;
        $log->addWriter($mock);
        $serviceManager->setService('Zend\Log', $log);

        $caseService = $serviceManager->get('caseServiceFactory');

        $result1 = $caseService->resolveSort('caseId');
        $this->assertEquals($result1, 'caseNumber');

        $result2 = $caseService->resolveSort(null);
        $this->assertEquals($result2, 'openTime');

        $result3 = $caseService->resolveSort('closeTime');
        $this->assertEquals($result3, 'closeTime');

        $result4 = $caseService->resolveSort('openTime');
        $this->assertEquals($result4, 'openTime');

        // testing invalid parameter
        $result5 = $caseService->resolveSort('foobar');
        $this->assertEquals($result5, 'openTime');
    }
}
