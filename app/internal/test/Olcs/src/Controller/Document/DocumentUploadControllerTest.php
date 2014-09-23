<?php

/**
 * Document upload controller tests
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace OlcsTest\Controller\Document;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Document upload controller tests
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class DocumentUploadControllerTest extends AbstractHttpControllerTestCase
{
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

        $this->controller->expects($this->any())
             ->method('getServiceLocator')
             ->will($this->returnValue($mockServiceLocator));

        $query = new \Zend\Stdlib\Parameters();
        $request = $this->getMock('\stdClass', ['isXmlHttpRequest', 'isPost', 'getPost', 'getFiles']);
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
                'getData', 'add'
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
                    'documentTemplate' => 1
                ),
                'bookmarks' => array()
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

    public function testFinaliseActionWithBackButtonPressed()
    {
        $this->controller->expects($this->once())
            ->method('isButtonPressed')
            ->with('back')
            ->will($this->returnValue(true));

        $fromRoute = $this->getMock('\stdClass', ['fromRoute']);
        $fromRoute->expects($this->any())
            ->method('fromRoute')
            ->will($this->returnValue(array('type' => 'licence')));

        $this->controller->expects($this->once())
            ->method('params')
            ->will($this->returnValue($fromRoute));

        $redirect = $this->getMock('\stdClass', ['toRoute']);

        $redirect->expects($this->once())
            ->method('toRoute')
            ->with('licence/documents/generate', ['type' => 'licence']);

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

        $this->controller->finaliseAction();
    }

    public function testFinaliseActionWithPostInvokesProcessGenerate()
    {
        $this->setUp(['processUpload']);

        $fromRoute = $this->getMock('\stdClass', ['fromRoute']);
        $fromRoute->expects($this->any())
            ->method('fromRoute')
            ->will(
                $this->returnValue(
                    array(
                        'type' => 'licence',
                        'tmpId' => 'a-temp-file'
                    )
                )
            );

        $this->controller->expects($this->at(0))
            ->method('params')
            ->will($this->returnValue($fromRoute));

        $this->request->expects($this->any())
            ->method('isPost')
            ->will($this->returnValue(true));

        $this->request->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue([]));

        $this->controller->expects($this->once())
            ->method('processUpload');

        $url = $this->getMock('\stdClass', ['fromRoute']);

        $url->expects($this->once())
            ->method('fromRoute')
            ->with('fetch_tmp_document', ['path' => 'a-temp-file']);

        $this->controller->expects($this->once())
            ->method('url')
            ->will($this->returnValue($url));

        $this->controller->finaliseAction();
    }

    public function testProcessUpload()
    {
        $fromRoute = $this->getMock('\stdClass', ['fromRoute']);
        $fromRoute->expects($this->any())
            ->method('fromRoute')
            ->will(
                $this->returnValue(
                    array(
                        'type' => 'licence',
                        'licence' => 1234,
                        'tmpId' => 'a-temp-file'
                    )
                )
            );

        $this->controller->expects($this->at(0))
            ->method('params')
            ->will($this->returnValue($fromRoute));

        /*
        $this->controller->expects($this->at(1))
            ->method('params')
            ->with('tmpId')
            ->will($this->returnValue('a-temp-file'));
         */

        $files = $this->getMock('\stdClass', ['toArray']);

        $file = array(
            'file' => array()
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
            ->with(array());

        $this->fileStoreMock->expects($this->once())
            ->method('upload')
            ->with('documents')
            ->will($this->returnValue('full-filename'));

        // @NOTE: needs fixing; should have the temp path on it
        $this->fileStoreMock->expects($this->once())
            ->method('remove')
            ->with('tmp/documents/');

        $files->expects($this->once())
            ->method('toArray')
            ->will($this->returnValue($file));
        $this->request->expects($this->once())
            ->method('getFiles')
            ->will($this->returnValue($files));

        $redirect = $this->getMock('\stdClass', ['toRoute']);

        $redirect->expects($this->once())
            ->method('toRoute')
            ->with('licence/documents', ['type' => 'licence', 'tmpId' => 'a-temp-file', 'licence' => 1234]);

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

        $this->controller->processUpload(array());
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
            case 'DocTemplate':
                return $this->mockDocTemplate($data);
            case 'BookmarkSearch':
                return $this->mockBookmarkSearch($data);
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
            default:
                throw new \Exception("Service Locator " . $service . " not mocked");
        }
    }

    private function mockCategory($data)
    {
        return [
            'id' => 3,
            'description' => 'Another Category',
        ];
    }

    private function mockSubCategory($data)
    {
        return [
            'id' => 2,
            'description' => 'A Sub Category',
        ];
    }

    private function mockDocTemplate($data)
    {
        return [
            'description' => 'A template'
        ];
    }

    private function mockBookmarkSearch($data)
    {
        return [
            'fake_bookmark' => 'dummy'
        ];
    }

    private function mockDocument($data)
    {
        $expected = array(
            'identifier' => 'full-filename',
            'description' => 'A template',
            'licence' => 'xxx',
            'fileExtension' => 'doc_rtf',
            'category' => 3,
            'documentSubCategory' => 2,
            'isDigital' => true,
            'isReadOnly' => true,
            'size' => 0
        );

        // $this->assertEquals($expected, $data);
    }
}
