<?php

/**
 * Document upload controller tests
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace OlcsTest\Controller\Document;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Dvsa\Olcs\Api\Service\File\Exception as FileException;

/**
 * Document upload controller tests
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class DocumentFinaliseControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp($extraParams = array())
    {
        $this->markTestSkipped();

        $this->setApplicationConfig(
            include __DIR__.'/../../../../../config/application.config.php'
        );
        $this->controller = $this->getMock(
            '\Olcs\Controller\Document\DocumentFinaliseController',
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

        $this->controller->expects($this->any())
            ->method('makeRestCall')
            ->will($this->returnCallback(array($this, 'mockRestCall')));

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
            ->with(
                'fetch_tmp_document',
                [
                    'id' => 'a-temp-file',
                    'filename' => 'A_template.rtf'
                ]
            );

        $this->controller->expects($this->once())
            ->method('url')
            ->will($this->returnValue($url));

        $this->controller->finaliseAction();
    }

    public function testProcessUploadWithFileError()
    {
        $fromRoute = $this->getMock('\stdClass', ['fromRoute']);
        $fromRoute->expects($this->any())
            ->method('fromRoute')
            ->will(
                $this->returnValue(
                    array(
                        'type' => 'licence',
                    )
                )
            );

        $this->controller->expects($this->at(0))
            ->method('params')
            ->will($this->returnValue($fromRoute));

        $files = $this->getMock('\stdClass', ['toArray']);

        $file = array(
            'file' => array(
                'error' => 1
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
            ->with('licence/documents/finalise', ['type' => 'licence']);

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

        $this->controller->processUpload(array());
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
                        'tmpId' => 'a-temp-file'
                    )
                )
            );

        $this->controller->expects($this->at(0))
            ->method('params')
            ->will($this->returnValue($fromRoute));

        $files = $this->getMock('\stdClass', ['toArray']);

        $file = array(
            'file' => array(
                'error' => 0
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
            ->with(array('error' => 0));

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

        $this->request->expects($this->once())
            ->method('getFiles')
            ->will($this->returnValue($files));

        $redirect = $this->getMock('\stdClass', ['toRoute']);

        $redirect->expects($this->once())
            ->method('toRoute')
            ->with('licence/documents/finalise', ['type' => 'licence', 'tmpId' => 'a-temp-file', 'licence' => 1234]);

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

        $this->controller->processUpload(array());
    }

    public function processUploadProvider()
    {
        return [
            "Licence letter" => [
                'licence',
                [
                    'type'    => 'licence',
                    'licence' => 1234,
                ],
                'licence/documents',
            ],
            "Application letter" => [
                'application',
                [
                    'type'        => 'application',
                    'application' => 1234,
                ],
                'lva-application/documents',
            ],
            "Case letter" => [
                'case',
                [
                    'type'  => 'case',
                    'case'  => 1234,
                ],
                'case_licence_docs_attachments',
            ],
            "Bus Registration letter" => [
                'busReg',
                [
                    'type'     => 'busReg',
                    'busRegId' => 1234,
                    'licence'  => 7,
                ],
                'licence/bus-docs',
            ],
        ];
    }
    /**
     * @dataProvider processUploadProvider
     */
    public function testProcessUpload($docType, $routeParams, $redirectRoute)
    {
        $test = $this;
        $this->controller->expects($this->any())
            ->method('makeRestCall')
            ->will(
                $this->returnCallback(
                    function ($service, $method, $data, $bundle) use ($docType, $test) {
                        // pass an extra param to makeRestCall so we can use correct document mock
                        return $test->mockRestCall($service, $method, $data, $bundle, $docType);
                    }
                )
            );

        $params = array_merge(
            ['tmpId' => 'a-temp-file'],
            $routeParams
        );
        $fromRoute = $this->getMock('\stdClass', ['fromRoute']);
        $fromRoute->expects($this->any())
            ->method('fromRoute')
            ->will(
                $this->returnCallback(
                    function ($key = null) use ($params) {
                        if (is_null($key)) {
                            return $params;
                        }
                        return $params[$key];
                    }
                )
            );
        $this->controller->expects($this->any())
            ->method('params')
            ->will(
                $this->returnCallback(
                    function ($key = null) use ($fromRoute, $params) {
                        if (is_null($key)) {
                            return $fromRoute;
                        }
                        return $params[$key];
                    }
                )
            );

        $files = $this->getMock('\stdClass', ['toArray']);

        $file = array(
            'file' => array(
                'error' => 0
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
            ->with(array('error' => 0));

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

        $this->fileStoreMock->expects($this->once())
            ->method('remove')
            ->with('a-temp-file', 'tmp');

        $files->expects($this->once())
            ->method('toArray')
            ->will($this->returnValue($file));

        $this->request->expects($this->once())
            ->method('getFiles')
            ->will($this->returnValue($files));

        $redirect = $this->getMock('\stdClass', ['toRoute']);

        $redirect->expects($this->once())
            ->method('toRoute')
            ->with(
                $redirectRoute,
                array_merge(['tmpId' => 'a-temp-file'], $routeParams)
            );

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
    public function mockRestCall($service, $method, $data = array(), $bundle = array(), $type = null)
    {
        switch ($service) {
            case 'Category':
                return $this->stubCategory($data);
            case 'SubCategory':
                return $this->stubSubCategory($data);
            case 'DocTemplate':
                return $this->stubDocTemplate($data);
            case 'BookmarkSearch':
                return $this->stubBookmarkSearch($data);
            case 'Document':
                return $this->mockDocument($data, $type);
            case 'Entity\Application':
                $eaMock = $this->getMock('\StdClass', ['getLicenceIdForApplication']);
                $eaMock->expects($this->any())
                    ->method('getLicenceIdForApplication')
                    ->will($this->returnValue(7));
                return $eaMock;
            default:
                throw new \Exception("Service call " . $service . " not mocked");
        }
    }

    public function mockServiceLocator($service)
    {
        switch ($service) {
            case 'FileUploader':
                return $this->fileStoreMock;
            case 'ContentStore':
                return $this->contentStoreMock;
            case 'Document':
                return $this->documentMock;
            case 'Entity\Application':
                $eaMock = $this->getMock('\StdClass', ['getLicenceIdForApplication']);
                $eaMock->expects($this->any())
                    ->method('getLicenceIdForApplication')
                    ->will($this->returnValue(7));
                return $eaMock;
            case 'DataServiceManager':
                $caseMock = $this->getMock('\StdClass', ['fetchCaseData']);
                $caseMock->expects($this->any())
                    ->method('fetchCaseData')
                    ->will(
                        $this->returnValue(
                            [
                                'id' => 1234,
                                'caseType' => [
                                    'id' => 'case_t_lic'
                                ],
                                'licence' => [ 'id' => 7 ]
                            ]
                        )
                    );
                $dsMock = $this->getMock('\StdClass', ['get']);
                $dsMock->expects($this->any())
                    ->method('get')
                    ->with('Olcs\Service\Data\Cases')
                    ->will($this->returnValue($caseMock));
                return $dsMock;
            case 'Helper\Date':
                $mock = $this->getMock('\stdClass', ['getDate']);
                $mock->expects($this->any())
                    ->method('getDate')
                    ->willReturn('2014-01-01 00:00:00');
                return $mock;
            default:
                throw new \Exception("Service Locator " . $service . " not mocked");
        }
    }

    private function stubCategory($data)
    {
        return [
            'id' => 3,
            'description' => 'Another Category',
        ];
    }

    private function stubSubCategory($data)
    {
        return [
            'id' => 2,
            'subCategoryName' => 'A Sub Category',
        ];
    }

    private function stubDocTemplate($data)
    {
        return [
            'description' => 'A template'
        ];
    }

    private function stubBookmarkSearch($data)
    {
        return [
            'fake_bookmark' => 'dummy'
        ];
    }

    private function mockDocument($data, $type)
    {
        $this->assertStringEndsWith('A_template.rtf', $data['filename']);
        $this->assertStringStartsWith('2014-01-01', $data['issuedDate']);

        unset($data['filename']);
        unset($data['issuedDate']);

        $expected = array(
            'identifier' => 'full-filename',
            'description' => 'A template',
            'category' => 3,
            'subCategory' => 2,
            'isExternal' => false,
            'size' => 1234
        );

        switch ($type) {
            case 'licence':
                $extra = ['licence' => 1234];
                break;
            case 'application':
                $extra = ['licence' => 7, 'application' => 1234];
                break;
            case 'case':
                $extra = ['licence' => 7, 'case' => 1234];
                break;
            case 'busReg':
                $extra = ['licence' => 7, 'busReg' => 1234];
                break;
            default:
                $extra = [];
        }
        $expected = array_merge($expected, $extra);

        $this->assertEquals($expected, $data);
        return $data;
    }
}
