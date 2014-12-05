<?php

/**
 * Document upload controller tests
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace OlcsTest\Controller\Document;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Common\Service\File\Exception as FileException;

/**
 * Document upload controller tests
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class DocumentUploadControllerTest extends AbstractHttpControllerTestCase
{
    protected $controller;
    public function setUp($extraParams = array())
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../../config/application.config.php'
        );
        $this->controller = $this->getMock(
            '\Olcs\Controller\Document\DocumentUploadController',
            array_merge(
                array(
                    'makeRestCall',
                    'params',
                    'getForm',
                    'loadScripts',
                    'getServiceLocator',
                    'getRequest',
                    'redirect',
                    'url',
                    'isButtonPressed',
                    'getSearchForm'
                ),
                $extraParams
            )
        );

        $this->controller->expects($this->any())
            ->method('makeRestCall')
            ->will($this->returnCallback(array($this, 'mockRestCall')));

        $mockServiceLocator = $this->getMock('\stdClass', ['get']);
        $mockServiceLocator->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(array($this, 'mockServiceLocator')));

        $this->servicelocatorMock = $mockServiceLocator;

        $this->controller->expects($this->any())
             ->method('getServiceLocator')
             ->will($this->returnValue($mockServiceLocator));

        $query = new \Zend\Stdlib\Parameters();
        $request = $this->getMock('\stdClass', ['getQuery', 'isXmlHttpRequest', 'isPost', 'getPost', 'getFiles']);
        $request->expects($this->any())
            ->method('getQuery')
            ->will($this->returnValue($query));

        $this->query = $query;
        $this->request = $request;

        $this->controller->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $form = $this->getMock(
            '\stdClass',
            [
                'get', 'setValue', 'setValueOptions',
                'remove', 'setData', 'isValid',
                'getData', 'add', 'bind'
            ]
        );

        $form->expects($this->any())
            ->method('get')
            ->will($this->returnSelf());

        $form->expects($this->any())
            ->method('isValid')
            ->will($this->returnValue(true));

        $this->controller->expects($this->any())
            ->method('getForm')
            ->will($this->returnValue($form));

        $this->form = $form;

        $this->contentStoreMock = $this->getMock('\stdClass', ['readMeta']);

        $metaString = json_encode(
            array(
                'details' => array(
                    'category' => 3,
                    'documentSubCategory' => 2,
                    'description' => 'foo'
                )
            )
        );
        $meta = array(
            'exists' => true,
            'metadata' => array(
                'meta:data' => $metaString
            )
        );

        $this->contentStoreMock->expects($this->any())
            ->method('readMeta')
            ->will($this->returnValue($meta));

        parent::setUp();
    }


    public function processUploadProvider()
    {
        return [
            ['licence', 'licence/documents'],
            //['application', 'lva-application/documents'],
        ];
    }
    /**
     * @dataProvider processUploadProvider
     */
    public function testProcessUpload($docType, $redirectRoute)
    {
        $fromRoute = $this->getMock('\stdClass', ['fromRoute']);
        $fromRoute->expects($this->any())
            ->method('fromRoute')
            ->will(
                $this->returnValue(
                    array(
                        'type' => $docType,
                        $docType => 1234,
                        'tmpId' => 'full-filename'
                    )
                )
            );

        $this->controller->expects($this->at(0))
            ->method('params')
            ->will($this->returnValue($fromRoute));

        $files = $this->getMock('\stdClass', ['toArray']);

        $file = array(
            'details' => array(
                'description' => 'file description',
                'category' => 3,
                'documentSubCategory' => 2,
                'file' => array(
                    'name' => 'testfile',
                    'error' => 0
                )
            )
        );

        $this->fileStoreMock = $this->getMock(
            '\stdClass',
            [
                'setFile',
                'upload',
                'remove'
            ]
        );

        $this->fileStoreMock->expects($this->once())
            ->method('setFile')
            ->with(
                array(
                    'name' => 'testfile',
                    'error' => 0
                )
            );

        $storeFile = $this->getMock('\stdClass', ['getIdentifier', 'getExtension', 'getSize']);
        $storeFile->expects($this->any())
            ->method('getIdentifier')
            ->willReturn('full-filename');

        $storeFile->expects($this->any())
            ->method('getExtension')
            ->willReturn('rtf');

        $storeFile->expects($this->any())
            ->method('getSize')
            ->willReturn(1234);

        $this->fileStoreMock->expects($this->once())
            ->method('upload')
            ->will($this->returnValue($storeFile));

        // @NOTE: needs fixing; should have the temp path on it
        $this->fileStoreMock->expects($this->once())
            ->method('remove')
            ->with(null, 'tmp');

        $files->expects($this->once())
            ->method('toArray')
            ->will($this->returnValue($file));

        $this->request->expects($this->once())
            ->method('getFiles')
            ->will($this->returnValue($files));

        $redirect = $this->getMock('\stdClass', ['toRoute']);

        $redirect->expects($this->once())
            ->method('toRoute')
            ->with($redirectRoute, ['type' => $docType, 'tmpId' => 'full-filename', $docType => 1234]);

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

        $this->controller->processUpload($file);
    }

    public function testProcessUploadWithoutFileFails()
    {
        $fromRoute = $this->getMock('\stdClass', ['fromRoute']);
        $fromRoute->expects($this->any())
            ->method('fromRoute')
            ->will(
                $this->returnValue(
                    array(
                        'type' => 'licence',
                        'licence' => 1234,
                        'tmpId' => 'full-filename'
                    )
                )
            );

        $this->controller->expects($this->at(0))
            ->method('params')
            ->will($this->returnValue($fromRoute));

        $files = $this->getMock('\stdClass', ['toArray']);

        $file = array(
            'details' => array(
                'description' => 'file description',
                'category' => 3,
                'documentSubCategory' => 2
            )
        );

        $files->expects($this->once())
            ->method('toArray')
            ->will($this->returnValue($file));

        $this->request->expects($this->once())
            ->method('getFiles')
            ->will($this->returnValue($files));

        $redirect = $this->getMock('\stdClass', ['toRoute']);

        $redirect->expects($this->once())
            ->method('toRoute')
            ->with('licence/documents/upload', ['type' => 'licence', 'tmpId' => 'full-filename', 'licence' => 1234]);

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

        $this->controller->processUpload($file);
    }

    public function testProcessUploadWhenStoreThrowsException()
    {
        $fromRoute = $this->getMock('\stdClass', ['fromRoute']);
        $fromRoute->expects($this->any())
            ->method('fromRoute')
            ->will(
                $this->returnValue(
                    array(
                        'type' => 'licence',
                        'licence' => 1234,
                        'tmpId' => 'full-filename'
                    )
                )
            );

        $this->controller->expects($this->at(0))
            ->method('params')
            ->will($this->returnValue($fromRoute));

        $files = $this->getMock('\stdClass', ['toArray']);

        $this->request->expects($this->once())
            ->method('getFiles')
            ->will($this->returnValue($files));

        $file = array(
            'details' => array(
                'description' => 'file description',
                'category' => 3,
                'documentSubCategory' => 2,
                'file' => array(
                    'name' => 'testfile',
                    'error' => 0
                )
            )
        );

        $this->fileStoreMock = $this->getMock(
            '\stdClass',
            [
                'setFile',
                'upload'
            ]
        );

        $this->fileStoreMock->expects($this->once())
            ->method('setFile')
            ->with(
                array(
                    'name' => 'testfile',
                    'error' => 0
                )
            );

        $storeFile = $this->getMock('\stdClass', ['getIdentifier', 'getExtension', 'getSize']);
        $storeFile->expects($this->any())
            ->method('getIdentifier')
            ->willReturn('full-filename');

        $storeFile->expects($this->any())
            ->method('getExtension')
            ->willReturn('rtf');

        $storeFile->expects($this->any())
            ->method('getSize')
            ->willReturn(1234);

        $this->fileStoreMock->expects($this->once())
            ->method('upload')
            ->will($this->throwException(new FileException()));

        $files->expects($this->once())
            ->method('toArray')
            ->will($this->returnValue($file));

        $redirect = $this->getMock('\stdClass', ['toRoute']);

        $redirect->expects($this->once())
            ->method('toRoute')
            ->with('licence/documents/upload', ['type' => 'licence', 'tmpId' => 'full-filename', 'licence' => 1234]);

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

        $this->controller->processUpload($file);
    }

    public function testUploadAction()
    {
        $fromRoute = $this->getMock('\stdClass', ['fromRoute']);
        $fromRoute->expects($this->any())
            ->method('fromRoute')
            ->will(
                $this->returnValue(
                    'licence'
                )
            );

        $this->controller->expects($this->any())
            ->method('params')
            ->will($this->returnValue($fromRoute));

        $response = $this->controller->uploadAction();

        $variables = $response->getVariables();

        $this->assertEquals('Upload document', $variables['pageTitle']);
    }

    public function testUploadActionWithPostInvokesProcessUpload()
    {
        $this->setUp(['processUpload']);

        $this->request->expects($this->any())
            ->method('isPost')
            ->will($this->returnValue(true));

        $postData = array(
            'details' => array(
                'description' => 'file description',
                'category' => 3,
                'documentSubCategory' => 2,
                'file' => array(
                    'name' => 'testfile',
                    'error' => 0
                )
            )
        );
        $this->request->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue($postData));

        $fromRoute = $this->getMock('\stdClass', ['fromRoute']);
        $fromRoute->expects($this->any())
            ->method('fromRoute')
            ->will(
                $this->returnValue(
                    'licence'
                )
            );

        $this->controller->expects($this->any())
            ->method('params')
            ->will($this->returnValue($fromRoute));

        $this->controller->expects($this->once())
            ->method('processUpload');

        $this->controller->uploadAction();
    }

    /**
     * Mock a given rest call
     *
     * @param string $service
     * @param string $method
     * @param array $data
     * @param array $bundle
     */
    public function mockRestCall($service, $method, $data = array(), $bundle = array())
    {
        switch ($service) {
            case 'Category':
                return $this->mockCategory($data);
            case 'DocumentSubCategory':
                return $this->mockSubCategory($data);
            case 'Document':
                return $this->mockDocument($data);
            default:
                throw new \Exception("Service call " . $service . " not mocked");
        }
    }

    public function mockServiceLocator($service)
    {
        switch ($service) {
            case 'FileUploader':
                $fileUploaderMock = $this->getMock('\stdClass', ['getUploader']);
                $fileUploaderMock->expects($this->any())
                    ->method('getUploader')
                    ->will($this->returnValue($this->fileStoreMock));
                return $fileUploaderMock;
            case 'ContentStore':
                return $this->contentStoreMock;
            case 'Document':
                return $this->documentMock;
            case 'DataServiceManager':
                return $this->servicelocatorMock;
            case 'Olcs\Service\Data\DocumentSubCategory':
                return $this->getMock('Olcs\Service\Data\DocumentSubCategory');
            case 'Entity\Application':
                $eaMock = $this->getMock('\StdClass', ['getLicenceIdForApplication']);
                $eaMock->expects($this->any())
                    ->method('getLicenceIdForApplication')
                    ->will($this->returnValue(7));
                return $eaMock;
            default:
                throw new \Exception("Service Locator " . $service . " not mocked");
        }
    }

    private function mockCategory($data)
    {
        return [
            'Results' => [
                [
                    'id' => 1,
                    'description' => 'A Category',
                ], [
                    'id' => 2,
                    'description' => 'Licensing',
                ], [
                    'id' => 3,
                    'description' => 'Another Category',
                ],
            ]
        ];
    }

    private function mockSubCategory($data)
    {
        return [
            'Results' => [
                [
                    'id' => 10,
                    'description' => 'A Sub Category',
                ], [
                    'id' => 20,
                    'description' => 'Publishable Applications',
                ], [
                    'id' => 30,
                    'description' => 'Another Sub Category',
                ],
            ]
        ];
    }

    private function mockDocFile($data)
    {
        return [
            'description' => 'file description'
        ];
    }

    private function mockDocument($data)
    {
        $this->assertStringEndsWith('testfile.rtf', $data['filename']);
        $this->assertStringStartsWith(date('Y-m-d'), $data['issuedDate']);

        unset($data['filename']);
        unset($data['issuedDate']);

        $expected = array(
            'identifier' => 'full-filename',
            'description' => 'file description',
            'licence' => 1234,
            'fileExtension' => 'doc_rtf',
            'category' => 3,
            'documentSubCategory' => 2,
            'isDigital' => true,
            'isReadOnly' => true,
            'size' => 1234
        );

         $this->assertEquals($expected, $data);
    }
}
