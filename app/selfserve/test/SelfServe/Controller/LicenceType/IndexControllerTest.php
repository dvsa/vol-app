<?php

namespace OlcsTest\Controller\LicenceType;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class IndexControllerTest extends AbstractHttpControllerTestCase
{

    protected function setUpMockController($methods)
    {
        $this->controller = $this->getMock(
            '\SelfServe\Controller\LicenceType\IndexController',
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
     * Method to test generateStepFormAction
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
        $applicationId = 7;
        
        $mockParams->expects($this->at(0))
            ->method('fromRoute')
            ->with($this->equalTo('applicationId'))
            ->will($this->returnValue($applicationId));
        
        $mockParams->expects($this->at(1))
            ->method('fromRoute')
            ->with($this->equalTo('step'))
            ->will($this->returnValue('operator-location'));
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
                ->with($mockForm, 'processOperatorLocation', ['applicationId' => $applicationId])
                ->will($this->returnValue($mockForm));
        
        $formData = []; // no prefill
        $this->controller->expects($this->once())
                ->method('getPersistedFormData')
                ->with($mockForm)
                ->will($this->returnValue($formData));
        
        $this->controller->generateStepFormAction();
    }

    /**
     * Method to test processOperatorLocation
     */
    public function testProcessOperatorLocation()
    {
        $this->setUpMockController( [
            'processEdit',
            'redirect',
            'evaluateNextStep',
            '_getLicenceEntity'
        ]);
        $params['applicationId'] = 7;
        $entity_data = ['id' => $params['applicationId']];
        
        $valid_data['operator_location']['operator_location'] = 'ni';
        $valid_data['version'] = 1;
        $operatorLocation = $valid_data['operator_location']['operator_location'];
        $process_data = array(
            'id' => $params['applicationId'],
            'niFlag' =>  $operatorLocation == 'ni' ? 1 : 0,
            'version' => $valid_data['version'],
        );
                
        if ($operatorLocation == 'ni'){
            $process_data['goodsOrPsv'] = 'goods';
        }
        
        $mockRedirect = $this->getMock('\stdClass', array('toRoute'));
        $next_step = 'licence_type_ni';
        
        $mockForm = new \Zend\Form\Form();

                
        $this->controller->expects($this->any())
                ->method('_getLicenceEntity')
                ->will($this->returnValue($entity_data)); 
        
        $this->controller->expects($this->once())
                ->method('processEdit')
                ->with($this->equalTo($process_data), $this->equalTo('Licence'));
        
        $this->controller->expects($this->once())
                ->method('evaluateNextStep')
                ->with($mockForm)
                ->will($this->returnValue($next_step));
        
        $mockRedirect->expects($this->once())
                ->method('toRoute')
                ->with($this->equalTo('selfserve/licence-type'), 
                       $this->equalTo(['applicationId' => $params['applicationId'], 'step' => $next_step]));
           
        $this->controller->expects($this->once())
                ->method('redirect')
                ->will($this->returnValue($mockRedirect)); 
                
        $this->controller->processOperatorLocation($valid_data, $mockForm, $params);
    }

    /**
     * Test getOperatorLocationFormData NI
     */
    public function testGetNIOperatorLocationFormData()
    {
        $applicationId  = 7;
        
        
        // Test NI
        $entity_data['version'] = 3;
        $entity_data['niFlag'] = 1;
        
        $this->setUpMockController( [
            'makeRestCall',
            'redirect',
            'params',
            '_getLicenceEntity'
        ]);

         
        $entity_data['id'] = 7;
        
        $this->controller->expects($this->any())
                ->method('_getLicenceEntity')
                ->will($this->returnValue($entity_data)); 
        
        $result = $this->controller->getOperatorLocationFormData();
        
        $expected = ['version' => $entity_data['version'], 
                    'operator_location' => [
                        'operator_location' => 'ni' 
                        ]
                    ];
        $this->assertSame($expected, $result);

    }
    
    /**
     * Test getOperatorLocationFormData GB location
     */
    public function testGetGBOperatorLocationFormData()
    {
        $this->setUpMockController( [
            '_getLicenceEntity'
        ]);
        
        $entity_data['version'] = 1;
        $entity_data['niFlag'] = 0;
        
        $this->controller->expects($this->any())
                ->method('_getLicenceEntity')
                ->will($this->returnValue($entity_data)); 
                
        $result = $this->controller->getOperatorLocationFormData();
        
        $expected = ['version' => $entity_data['version']];
        $this->assertSame($expected, $result);


    }
    
    /**
     * Test processOperatorType 
     */
    public function testProcessOperatorType()
    {

        $this->setUpMockController( [
            'processEdit',
            'redirect',
            'evaluateNextStep',
            '_getLicenceEntity'
        ]);
        $params['applicationId'] = 1;
        $entity_data = ['id' => $params['applicationId']];
        
        $valid_data['operator-type']['operator-type'] = 'psv';
        $valid_data['version'] = 1;
        $operatorType = $valid_data['operator-type']['operator-type'];

        $process_data = array(
            'id' => $params['applicationId'],
            'goodsOrPsv' =>  $operatorType,
            'version' => $valid_data['version'],
        );
                
        $mockRedirect = $this->getMock('\stdClass', array('toRoute'));
        $next_step = 'licence_type_psv';
        
        $mockForm = new \Zend\Form\Form();
                
        $this->controller->expects($this->any())
                ->method('_getLicenceEntity')
                ->will($this->returnValue($entity_data)); 
        
        $this->controller->expects($this->once())
                ->method('processEdit')
                ->with($this->equalTo($process_data), $this->equalTo('Licence'));
        
        $this->controller->expects($this->once())
                ->method('evaluateNextStep')
                ->with($mockForm)
                ->will($this->returnValue($next_step));
        
        $mockRedirect->expects($this->once())
                ->method('toRoute')
                ->with($this->equalTo('selfserve/licence-type'), 
                       $this->equalTo(['applicationId' => $params['applicationId'], 'step' => $next_step]));
           
        $this->controller->expects($this->once())
                ->method('redirect')
                ->will($this->returnValue($mockRedirect)); 
                
        $this->controller->processOperatorType($valid_data, $mockForm, $params);

    }
    
    /**
     * Test getOperatorTypeFormData GB location
     */
    public function testGetOperatorTypeFormData()
    {
        $this->setUpMockController( [
            '_getLicenceEntity'
        ]);
        
        $entity_data['version'] = 1;
        $entity_data['goodsOrPsv'] = 'goods';
        
        $this->controller->expects($this->any())
                ->method('_getLicenceEntity')
                ->will($this->returnValue($entity_data)); 
                
        $result = $this->controller->getOperatorTypeFormData();
        
        $expected = ['version' => $entity_data['version'], 
                    'operator-type' => [
                        'operator-type' => $entity_data['goodsOrPsv'] 
                        ]
                    ];
        $this->assertSame($expected, $result);


    }
    
    /**
     * Test processLicenceType 
     */
    public function testProcessLicenceType()
    {

        $this->setUpMockController( [
            'processEdit',
            'redirect',
            'evaluateNextStep',
            '_getLicenceEntity'
        ]);
        $params['applicationId'] = 7;
        
        $valid_data['licence-type']['licence_type'] = 'standard-national';
        $valid_data['version'] = 1;
        $licenceType = $valid_data['licence-type']['licence_type'];

        $process_data = array(
            'id' => $params['applicationId'],
            'licenceType' =>  $licenceType,
            'version' => $valid_data['version'],
        );
                
        $mockRedirect = $this->getMock('\stdClass', array('toRoute'));
        $next_step = 'licence_type_psv';
        
        $mockForm = new \Zend\Form\Form();

        $entity_data = ['id' => $params['applicationId']];
        
        $this->controller->expects($this->any())
                ->method('_getLicenceEntity')
                ->will($this->returnValue($entity_data)); 
        
        $this->controller->expects($this->once())
                ->method('processEdit')
                ->with($this->equalTo($process_data), $this->equalTo('Licence'));
        
        $this->controller->expects($this->once())
                ->method('evaluateNextStep')
                ->with($mockForm)
                ->will($this->returnValue($next_step));
        
        $mockRedirect->expects($this->once())
                ->method('toRoute')
                ->with($this->equalTo('selfserve/licence-type-complete'), 
                       $this->equalTo(['applicationId' => $params['applicationId'], 'step' => $next_step]));
           
        $this->controller->expects($this->once())
                ->method('redirect')
                ->will($this->returnValue($mockRedirect)); 
                
        $this->controller->processLicenceType($valid_data, $mockForm, $params);

    }
    
    /**
     * Test getLicenceTypeFormData 
     */
    public function testGetLicenceTypeFormData()
    {
        $this->setUpMockController( [
            '_getLicenceEntity'
        ]);
        
        $entity_data['version'] = 1;
        $entity_data['licenceType'] = 'standard-national';
        
        $this->controller->expects($this->any())
                ->method('_getLicenceEntity')
                ->will($this->returnValue($entity_data)); 
                
        $result = $this->controller->getLicenceTypeFormData();
        
        $expected = ['version' => $entity_data['version'], 
                    'licence-type' => [
                        'licence_type' => $entity_data['licenceType'] 
                        ]
                    ];
        $this->assertSame($expected, $result);


    }
    
    /**
     * Test processLicenceTypePsv 
     */
    public function testProcessLicenceTypePsv()
    {

        $this->setUpMockController( [
            'processEdit',
            'redirect',
            'evaluateNextStep',
            '_getLicenceEntity'
        ]);
        $params['applicationId'] = 7;
        
        $valid_data['licence-type-psv']['licence-type-psv'] = 'standard-national';
        $valid_data['version'] = 1;
        $licenceType = $valid_data['licence-type-psv']['licence-type-psv'];

        $process_data = array(
            'id' => $params['applicationId'],
            'licenceType' =>  $licenceType,
            'version' => $valid_data['version'],
        );
                
        $mockRedirect = $this->getMock('\stdClass', array('toRoute'));
        $next_step = 'licence_type_psv';
        
        $mockForm = new \Zend\Form\Form();
        
        $entity_data = ['id' => $params['applicationId']];
        
        $this->controller->expects($this->any())
                ->method('_getLicenceEntity')
                ->will($this->returnValue($entity_data)); 
        
        $this->controller->expects($this->once())
                ->method('processEdit')
                ->with($this->equalTo($process_data), $this->equalTo('Licence'));
        
        $this->controller->expects($this->once())
                ->method('evaluateNextStep')
                ->with($mockForm)
                ->will($this->returnValue($next_step));
        
        $mockRedirect->expects($this->once())
                ->method('toRoute')
                ->with($this->equalTo('selfserve/licence-type-complete'), 
                       $this->equalTo(['applicationId' => $params['applicationId'], 'step' => $next_step]));
           
        $this->controller->expects($this->once())
                ->method('redirect')
                ->will($this->returnValue($mockRedirect)); 
                
        $this->controller->processLicenceTypePsv($valid_data, $mockForm, $params);

    }
    
    /**
     * Test getLicenceTypePsvFormData 
     */
    public function testGetLicenceTypePsvFormData()
    {
        $this->setUpMockController( [
            '_getLicenceEntity'
        ]);
        
        $entity_data['version'] = 1;
        $entity_data['licenceType'] = 'standard-national';
        
        $this->controller->expects($this->any())
                ->method('_getLicenceEntity')
                ->will($this->returnValue($entity_data)); 
                
        $result = $this->controller->getLicenceTypePsvFormData();
        
        $expected = ['version' => $entity_data['version'], 
                    'licence-type-psv' => [
                        'licence-type-psv' => $entity_data['licenceType'] 
                        ]
                    ];
        $this->assertSame($expected, $result);


    }
    
    /**
     * Test processLicenceTypeNI 
     */
    public function testProcessLicenceTypeNI()
    {

        $this->setUpMockController( [
            'processEdit',
            'redirect',
            'evaluateNextStep',
            '_getLicenceEntity'
        ]);
        $params['applicationId'] = 7;
        
        $valid_data['licence-type-ni']['licence_type'] = 'standard-national';
        $valid_data['version'] = 1;
        $licenceType = $valid_data['licence-type-ni']['licence_type'];

        $process_data = array(
            'id' => $params['applicationId'],
            'licenceType' =>  $licenceType,
            'version' => $valid_data['version'],
        );
                
        $mockRedirect = $this->getMock('\stdClass', array('toRoute'));
        $next_step = 'licence_type_psv';
        
        $mockForm = new \Zend\Form\Form();
        
        $entity_data = ['id' => $params['applicationId']];
        
        $this->controller->expects($this->any())
                ->method('_getLicenceEntity')
                ->will($this->returnValue($entity_data)); 
        
        $this->controller->expects($this->once())
                ->method('processEdit')
                ->with($this->equalTo($process_data), $this->equalTo('Licence'));
        
        $this->controller->expects($this->once())
                ->method('evaluateNextStep')
                ->with($mockForm)
                ->will($this->returnValue($next_step));
        
        $mockRedirect->expects($this->once())
                ->method('toRoute')
                ->with($this->equalTo('selfserve/licence-type-complete'), 
                       $this->equalTo(['applicationId' => $params['applicationId'], 'step' => $next_step]));
           
        $this->controller->expects($this->once())
                ->method('redirect')
                ->will($this->returnValue($mockRedirect)); 
                
        $this->controller->processLicenceTypeNi($valid_data, $mockForm, $params);

    }
    
    /**
     * Test getLicenceTypeNiFormData 
     */
    public function testGetLicenceTypeNiFormData()
    {
        $this->setUpMockController( [
            '_getLicenceEntity'
        ]);
        
        $entity_data['version'] = 1;
        $entity_data['licenceType'] = 'standard-national';
        
        $this->controller->expects($this->any())
                ->method('_getLicenceEntity')
                ->will($this->returnValue($entity_data)); 
                
        $result = $this->controller->getLicenceTypeNiFormData();
        
        $expected = ['version' => $entity_data['version'], 
                    'licence-type-ni' => [
                        'licence_type' => $entity_data['licenceType'] 
                        ]
                    ];
        $this->assertSame($expected, $result);


    }
    
    /**
     * Method to test completeAction
     */
    public function testCompleteAction()
    {
        $this->setUpMockController( [
            'params',
            'redirect'
        ]);
        $mockRedirect = $this->getMock('\stdClass', array('toRoute'));

        $mockParams = $this->getMock('\stdClass', array('fromRoute'));
        $applicationId = 7;
        $next_step = 'business-type';
        $mockParams->expects($this->once())
            ->method('fromRoute')
            ->with($this->equalTo('applicationId'))
            ->will($this->returnValue($applicationId));
                
        $this->controller->expects($this->once())
                ->method('params')
                ->will($this->returnValue($mockParams));   
        
        $mockRedirect->expects($this->once())
                ->method('toRoute')
                ->with($this->equalTo('selfserve/business-type'), 
                       $this->equalTo(['applicationId' => $applicationId, 'step' => $next_step]));
           
        $this->controller->expects($this->once())
                ->method('redirect')
                ->will($this->returnValue($mockRedirect));
        
        $this->controller->completeAction();
    }    
    
}
