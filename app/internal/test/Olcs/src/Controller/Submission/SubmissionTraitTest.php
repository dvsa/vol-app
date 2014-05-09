<?php

/**
 * Search controller form post tests
 *
 * @author adminmwc
 */

namespace OlcsTest\Controller\Submission;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class SubmissionTraitTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../'  . 'config/application.config.php'
        );
        $this->controller = $this->getMock(
            '\Olcs\Controller\Submission\SubmissionController',
            array(
                'getServiceLocator',
                'generateFormWithData',
                'generateForm',
                'params',
                'getParams',
                'makeRestCall',
                'setData',
                'createSubmission',
            )
        );
        $this->controller->routeParams = array();
        $this->licenceData = array(
            'id' => 7,
            'licenceType' => 'Standard National',
            'goodsOrPsv' => 'Psv'
        );
        
        parent::setUp();
    }
    
    public function testCreateSubmission()
    {
        /*$this->controller = $this->getMock(
            '\Olcs\Controller\Submission\SubmissionController',
            array(
                'makeRestCall',
                'getServiceLocator',
                'createSection'
            )
        );
        $this->licenceData['licenceType'] = 'blah';
        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->with('Licence', 'GET', array('id' => 7))
            ->will($this->returnValue($this->licenceData));
        
        $this->controller->submissionConfig = array(
                'sections' => array(
                    'case-summary-info' => null,
                    'transport-managers' => array(
                        'exclude' => array(
                            'column' => 'licenceType',
                            'values' => array(
                                'standard national',
                                'standard international'
                            )
                        )
                    )
                )
        );
        
        $this->controller->expects($this->once())
            ->method('createSection')
            ->with('case-summary-info', $this->controller->submissionConfig)
            ->will($this->returnValue(array()));
        
        $serviceLocator = $this->getMock('\stdClass', array('get'));
        
        $serviceLocator->expects($this->once())
            ->method('get')
            ->with('config')
            ->will(
                $this->returnValue(
                    array(
                        'submission_config' => array(
                            'sections' => array(
                                'case-summary-info' => null,
                                'transport-managers' => array(
                                    'exclude' => array(
                                        'column' => 'licenceType',
                                        'values' => array(
                                            'standard national',
                                            'standard international'
                                        )
                                    )
                                )
                            )
                        )
                    )
                )
            );
        
        $this->controller->expects($this->once())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceLocator));
        
        $this->controller->createSubmission(array('licence' => 7, 'case' => 54));*/
    }
}
