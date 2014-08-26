<?php

/**
 * FinancialHistoryControllerTest
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace OlcsTest\Controller\Application\PreviousHistory;

use OlcsTest\Controller\Application\AbstractApplicationControllerTestCase;
use Common\Controller\Application\ApplicationController;

/**
 * FinancialHistoryControllerTest
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class FinancialHistoryControllerTest extends AbstractApplicationControllerTestCase
{
    protected $controllerName =  '\Common\Controller\Application\PreviousHistory\FinancialHistoryController';

    protected $defaultRestResponse = array();

    protected $mockedMethods = array('getUploader', 'getFileSizeValidator');

    /**
     * Test back button
     */
    public function testBackButton()
    {
        $this->setUpAction('index', null, array('form-actions' => array('back' => 'Back')));

        $response = $this->controller->indexAction();

        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test indexAction
     */
    public function testIndexAction()
    {
        $this->setUpAction('index');

        $response = $this->controller->indexAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test indexAction with submit
     */
    public function testIndexActionWithSubmit()
    {
        $this->setUpAction(
            'index',
            null,
            array(
                'data' => array(
                    'id' => 'Y',
                    'version' => 'Y',
                    'bankrupt' => 'Y',
                    'liquidation' => 'Y',
                    'receivership' => 'Y',
                    'administration' => 'Y',
                    'disqualified' => 'Y',
                    'insolvencyDetails' => str_repeat('a', 200),
                    'insolvencyConfirmation' => 'Y',
                    'file' => array(
                        'file-controls' => array(
                            'upload' => ''
                        )
                    )
                )
            ),
            array(
                'data' => array(
                    'file' => array(
                        'file-controls' => array(
                            'file' => array(
                                'tmp_name' => '',
                                'name' => '',
                                'type' => '',
                                'error' => 4
                            )
                        )
                    )
                )
            )
        );

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->indexAction();

        // Make sure we get a view not a response
        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test indexAction with file upload
     */
    public function testIndexActionWithFileUpload()
    {
        $file = array(
            'tmp_name' => 'ngipushfdlgjk',
            'name' => 'ajkhdfjklah',
            'type' => 'image/png',
            'error' => 0
        );

        $this->setUpAction(
            'index',
            null,
            array(
                'data' => array(
                    'file' => array(
                        'file-controls' => array(
                            'upload' => 'Upload'
                        )
                    )
                )
            ),
            array(
                'data' => array(
                    'file' => array(
                        'file-controls' => array(
                            'file' => $file
                        )
                    )
                )
            )
        );

        $mockUploader = $this->getMock('\Common\Service\File\DiskStoreFileUploader', array('upload'));

        $mockUploader->expects($this->once())
            ->method('upload');

        $this->controller->expects($this->any())
            ->method('getUploader')
            ->will($this->returnValue($mockUploader));

        $mockValidator = $this->getMock('\stdClass', array('isValid'));
        $mockValidator->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $this->controller->expects($this->any())
            ->method('getFileSizeValidator')
            ->will($this->returnValue($mockValidator));

        $this->controller->setEnabledCsrf(false);
        $response = $this->controller->indexAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Mock the rest call
     *
     * @param string $service
     * @param string $method
     * @param array $data
     * @param array $bundle
     */
    protected function mockRestCalls($service, $method, $data = array(), $bundle = array())
    {
        if ($service == 'Application' && $method == 'GET' && $bundle == ApplicationController::$licenceDataBundle) {

            return $this->getLicenceData('goods');
        }

        if ($service == 'ApplicationCompletion' && $method == 'GET') {

            return $this->getApplicationCompletionData();
        }

        $dataBundle = array(
            'properties' => array(
                'id',
                'version',
                'bankrupt',
                'liquidation',
                'receivership',
                'administration',
                'disqualified',
                'insolvencyDetails',
                'insolvencyConfirmation'
            ),
            'children' => array(
                'documents' => array(
                    'properties' => array(
                        'id',
                        'version',
                        'filename',
                        'identifier',
                        'size'
                    )
                )
            )
        );

        if ($service == 'Application' && $method == 'GET' && $bundle == $bundle) {

            return array(
                'id' => 1,
                'version' => 1,
                'bankrupt' => 1,
                'liquidation' => 1,
                'receivership' => 1,
                'administration' => 1,
                'disqualified' => 1,
                'insolvencyDetails' => str_repeat('a', 200),
                'insolvencyConfirmation' => 1,
                'documents' => array(
                    array(
                        'id' => 1,
                        'version' => 1,
                        'filename' => 'Test.png',
                        'identifier' => 'ajfhljkdsafhflksdjf',
                        'size' => 50505
                    )
                )
            );
        }
    }
}
