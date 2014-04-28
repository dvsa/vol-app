<?php

namespace OlcsTest\Controller\PreviousHistory;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class IndexControllerTest extends AbstractHttpControllerTestCase
{

    protected function setUpMockController($methods)
    {
        $this->controller = $this->getMock(
            '\SelfServe\Controller\PreviousHistory\IndexController',
            $methods
        );    
    }
    
    protected function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../config/application.config.php'
        );

        parent::setUp();

    }

    /**
     * @group PreviousHistory
     *
     */
    public function testGenerateFinanceFormAction()
    {
        $this->setUpMockController( [
            'params',
            'generateSectionForm',
            'formPost',
            'getPersistedFormData'
        ]);
        $mockParams = $this->getMock('\stdClass', array('fromRoute'));
        $applicationId = 1;
        
        $mockParams->expects($this->at(0))
            ->method('fromRoute')
            ->with($this->equalTo('applicationId'))
            ->will($this->returnValue($applicationId));
        
        $mockParams->expects($this->at(1))
            ->method('fromRoute')
            ->with($this->equalTo('step'))
            ->will($this->returnValue('finance'));
        $mockForm = new \Zend\Form\Form();
        
        $this->controller->expects($this->at(0))
                ->method('params')
                ->will($this->returnValue($mockParams));
           
        $this->controller->expects($this->at(1))
                ->method('params')
                ->will($this->returnValue($mockParams));     
        
        $this->controller->expects($this->once())
                ->method('generateSectionForm')
                ->will($this->returnValue($mockForm));
        
        $this->controller->expects($this->once())
                ->method('formPost')
                ->with($mockForm, 'processFinance', ['applicationId' => $applicationId])
                ->will($this->returnValue($mockForm));
        
        $formData = []; // no prefill
        $this->controller->expects($this->once())
                ->method('getPersistedFormData')
                ->with($mockForm)
                ->will($this->returnValue($formData));
        
        $this->controller->generateStepFormAction();
    }

    /**
     * @group PreviousHistory
     *
     */
    public function testGenerateStepFormAction()
    {
        $this->setUpMockController( [
            'params',
            'generateSectionForm',
            'formPost',
            'getPersistedFormData'
        ]);
        $mockParams = $this->getMock('\stdClass', array('fromRoute'));
        $applicationId = 1;

        $mockParams->expects($this->at(0))
            ->method('fromRoute')
            ->with($this->equalTo('applicationId'))
            ->will($this->returnValue($applicationId));

        $mockParams->expects($this->at(1))
            ->method('fromRoute')
            ->with($this->equalTo('step'))
            ->will($this->returnValue('licence'));
        $mockForm = new \Zend\Form\Form();

        $this->controller->expects($this->at(0))
            ->method('params')
            ->will($this->returnValue($mockParams));

        $this->controller->expects($this->at(1))
            ->method('params')
            ->will($this->returnValue($mockParams));

        $this->controller->expects($this->once())
            ->method('generateSectionForm')
            ->will($this->returnValue($mockForm));

        $this->controller->expects($this->once())
            ->method('formPost')
            ->with($mockForm, 'processLicence', ['applicationId' => $applicationId])
            ->will($this->returnValue($mockForm));

        $formData = []; // no prefill
        $this->controller->expects($this->once())
            ->method('getPersistedFormData')
            ->with($mockForm)
            ->will($this->returnValue($formData));

        $this->controller->generateStepFormAction();
    }

    /**
     * @group PreviousHistory
     *
     */
    public function testProcessFinance()
    {
        $this->setUpMockController( [
            'processEdit',
            'evaluateNextStep',
        ]);

        $validData = array(
            'version' => 1,
            'finance' => array(
                'bankrupt' => 'Y',
            ),
        );
        $mockForm = new \Zend\Form\Form();
        $params['applicationId'] = 1;

        $this->controller->expects($this->once())
            ->method('evaluateNextStep')
            ->with($mockForm)
            ->will($this->returnValue('licence'));

        $router = $this->getMock('\Zend\Mvc\Router\SimpleRouteStack', ['assemble']);
        $router->expects($this->once())
            ->method('assemble')
            ->will($this->returnValue('/selfserve/1/previous-history/licence'))
        ;
        $this->controller->getEvent()->setRouter($router);
        $this->controller->getEvent()->setResponse(new \Zend\Http\PhpEnvironment\Response());

        $this->controller->processFinance($validData, $mockForm, $params);
    }

    /**
     * @group PreviousHistory
     *
     */
    public function testGetFinanceFormData()
    {
        $this->setUpMockController( [
            'params',
            'makeRestCall',
        ]);

        $mockParams = $this->getMock('\stdClass', array('fromRoute'));
        $applicationId = 1;

        $mockParams->expects($this->once())
            ->method('fromRoute')
            ->with($this->equalTo('applicationId'))
            ->will($this->returnValue($applicationId));

        $this->controller->expects($this->once())
            ->method('params')
            ->will($this->returnValue($mockParams));

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->with($this->equalTo('Application'), $this->equalTo('GET'), $this->equalTo(['id' => $applicationId]))
            ->will($this->returnValue(array('version' => 1)));

        $this->controller->getFinanceFormData();

    }

    /**
     *
     * @group PreviousHistory
     * @expectedException \OlcsEntities\Exceptions\EntityNotFoundException
     */
    public function testGetFinanceFormDataWithException()
    {
        $this->setUpMockController( [
            'params',
            'makeRestCall',
        ]);

        $mockParams = $this->getMock('\stdClass', array('fromRoute'));
        $applicationId = 1;

        $mockParams->expects($this->once())
            ->method('fromRoute')
            ->with($this->equalTo('applicationId'))
            ->will($this->returnValue($applicationId));

        $this->controller->expects($this->once())
            ->method('params')
            ->will($this->returnValue($mockParams));

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->with($this->equalTo('Application'), $this->equalTo('GET'), $this->equalTo(['id' => $applicationId]))
            ->will($this->returnValue(null));

        $this->controller->getFinanceFormData();

    }

    public function testCompleteAction()
    {
        $this->setUpMockController( [
            'params',
        ]);

        $mockParams = $this->getMock('\stdClass', array('fromRoute'));
        $applicationId = 1;

        $mockParams->expects($this->once())
            ->method('fromRoute')
            ->with($this->equalTo('applicationId'))
            ->will($this->returnValue($applicationId));

        $this->controller->expects($this->once())
            ->method('params')
            ->will($this->returnValue($mockParams));

        $router = $this->getMock('\Zend\Mvc\Router\SimpleRouteStack', ['assemble']);
        $router->expects($this->once())
            ->method('assemble')
            ->will($this->returnValue('/selfserve/1/finance/index'))
        ;
        $this->controller->getEvent()->setRouter($router);
        $this->controller->getEvent()->setResponse(new \Zend\Http\PhpEnvironment\Response());

        $this->controller->completeAction();
    }


    
}
