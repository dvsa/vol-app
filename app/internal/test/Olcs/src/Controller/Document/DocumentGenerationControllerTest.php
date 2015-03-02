<?php

/**
 * Document generation controller tests
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace OlcsTest\Controller\Document;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

use Mockery as m;

/**
 * Document generation controller tests
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class DocumentGenerationControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp($extraParams = array())
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../../config/application.config.php'
        );
        $this->controller = $this->getMock(
            '\Olcs\Controller\Document\DocumentGenerationController',
            array_merge(
                array(
                    'makeRestCall',
                    'params',
                    'getFromRoute',
                    'getForm',
                    'loadScripts',
                    'getServiceLocator',
                    'getRequest',
                    'redirect',
                    'getLoggedInUser',
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

        $this->controller->expects($this->any())
             ->method('getServiceLocator')
             ->will($this->returnValue($mockServiceLocator));

        $query = new \Zend\Stdlib\Parameters();
        $request = $this->getMock('\stdClass', ['getQuery', 'isXmlHttpRequest', 'isPost', 'getPost']);
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

        parent::setUp();
    }

    public function testGenerateActionWithGetAndNoTmpData()
    {
        $paramValues = [
            'type' => 'licence',
            'tmpId' => null
        ];
        $this->controller->expects($this->any())
            ->method('params')
            ->will(
                $this->returnCallback(
                    function ($key) use ($paramValues) {
                        return $paramValues[$key];
                    }
                )
            );

        $response = $this->controller->generateAction();

        $variables = $response->getVariables();

        $this->assertEquals('Generate letter', $variables['pageTitle']);
    }

    public function testGenerateActionWithGetAndTmpData()
    {
        $paramValues = [
            'type' => 'licence',
            'tmpId' => 'tmp_123'
        ];
        $this->controller->expects($this->any())
            ->method('params')
            ->will(
                $this->returnCallback(
                    function ($key) use ($paramValues) {
                        return $paramValues[$key];
                    }
                )
            );

        $this->contentStoreMock = $this->getMock('\stdClass', ['readMeta']);

        $metaString = json_encode(
            array(
                'details' => array(
                    'category' => 3
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

        $this->contentStoreMock->expects($this->once())
            ->method('readMeta')
            ->will($this->returnValue($meta));

        $this->fileStoreMock = $this->getMock('\stdClass', ['remove']);

        $this->fileStoreMock->expects($this->once())
            ->method('remove');

        $response = $this->controller->generateAction();

        $variables = $response->getVariables();

        $this->assertEquals('Generate letter', $variables['pageTitle']);
    }

    public function testGenerateActionWithPostInvokesProcessGenerate()
    {
        $this->setUp(['processGenerate']);

        $this->request->expects($this->any())
            ->method('isPost')
            ->will($this->returnValue(true));

        $postData = array(
            'details' => array(
                'category' => 1,
                'documentSubCategory' => 1,
                'documentTemplate' => 888,
            )
        );
        $this->request->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue($postData));

        $paramValues = [
            'type' => 'licence',
            'tmpId' => null
        ];
        $this->controller->expects($this->any())
            ->method('params')
            ->will(
                $this->returnCallback(
                    function ($key) use ($paramValues) {
                        return $paramValues[$key];
                    }
                )
            );

        $this->controller->expects($this->once())
            ->method('processGenerate');

        $this->controller->generateAction();
    }

    public function processGenerateProvider()
    {
        return [
            "Licence letter" => [
                'licence',
                ['type' => 'licence'],
                'licence/documents',
                [],
            ],
            "Application letter" => [
                'application',
                ['type' => 'application'],
                'lva-application/documents',
                ['licence' => 7],
            ],
            "Case letter" => [
                'case',
                ['type' => 'case'],
                'case_licence_docs_attachments',
                ['licence' => 7],
            ],
            "Case letter with entity" => [
                'case',
                ['type' => 'case', 'entityType' => 'testEntity', 'entityId' => 123],
                'case_licence_docs_attachments/entity',
                ['licence' => 7, 'entityType' => 'testEntity', 'entityId' => 123, 'testEntity' => 123],
            ],
            "Bus Registration letter" => [
                'busReg',
                ['type' => 'busReg', 'licence' => 7],
                'licence/bus-docs',
                ['licence' => 7],
            ],
        ];
    }

    /**
     * @dataProvider processGenerateProvider
     */
    public function testProcessGenerate($docType, $routeParams, $redirectRoute, $extraQueryData)
    {
        $fromRoute = $this->getMock('\stdClass', ['fromRoute']);
        $fromRoute->expects($this->any())
            ->method('fromRoute')
            ->will($this->returnValue($routeParams));

        $this->controller->expects($this->any())
            ->method('params')
            ->will($this->returnValue($fromRoute));

        $data = array(
            'details' => array(
                'documentTemplate' => 999,
            ),
            'bookmarks' => array()
        );

        $file = new \Dvsa\Jackrabbit\Data\Object\File();
        $file->setMimeType('application/rtf');
        $file->setContent('dummy content');

        $this->contentStoreMock = $this->getMock('\stdClass', ['read']);
        $this->contentStoreMock->expects($this->once())
            ->method('read')
            ->with('a-fake-template')
            ->will($this->returnValue($file));

        $this->documentMock = $this->getMock(
            '\stdClass',
            ['getBookmarkQueries', 'populateBookmarks']
        );

        $this->controller->expects($this->any())
            ->method('getLoggedInUser')
            ->will($this->returnValue(123));

        $queryData = array_merge(
            $data,
            array(
                'type' => $docType,
                'user' => 123
            ),
            $extraQueryData
        );

        $mockQuery = ['foo' => 'bar'];

        $this->documentMock->expects($this->once())
            ->method('getBookmarkQueries')
            ->with($file, $queryData)
            ->willReturn($mockQuery);

        $resultData = array(
            'fake_bookmark' => 'dummy'
        );
        $this->documentMock->expects($this->once())
            ->method('populateBookmarks')
            ->with($file, $resultData)
            ->will($this->returnValue('replaced content'));

        $this->fileStoreMock = $this->getMock(
            '\stdClass',
            [
                'setFile',
                'upload'
            ]
        );

        $fileData = [
            'content' => 'replaced content',
            'meta' => [
                'data' => '{"details":{"documentTemplate":999},"bookmarks":[]}'
            ]
        ];
        $this->fileStoreMock->expects($this->once())
            ->method('setFile')
            ->with($fileData);

        $storedFile = $this->getMock('\stdClass', ['getIdentifier']);
        $storedFile->expects($this->once())
            ->method('getIdentifier')
            ->willReturn('tmp-filename');

        $this->fileStoreMock->expects($this->once())
            ->method('upload')
            ->with('tmp')
            ->will($this->returnValue($storedFile));

        $redirect = $this->getMock('\stdClass', ['toRoute']);

        $redirect->expects($this->once())
            ->method('toRoute')
            ->with(
                $redirectRoute . '/finalise',
                array_merge(['tmpId' => 'tmp-filename'], $routeParams)
            );

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

        $this->controller->processGenerate($data);
    }

    /**
     * @dataProvider processGenerateProvider
     */
    public function testProcessGenerateNoBookmarkData($docType, $routeParams, $redirectRoute, $extraQueryData)
    {
        $fromRoute = $this->getMock('\stdClass', ['fromRoute']);
        $fromRoute->expects($this->any())
            ->method('fromRoute')
            ->will($this->returnValue($routeParams));

        $this->controller->expects($this->any())
            ->method('params')
            ->will($this->returnValue($fromRoute));

        $data = array(
            'details' => array(
                'documentTemplate' => 999,
            ),
            'bookmarks' => array()
        );

        $file = new \Dvsa\Jackrabbit\Data\Object\File();
        $file->setMimeType('application/rtf');
        $file->setContent('dummy content');

        $this->contentStoreMock = $this->getMock('\stdClass', ['read']);
        $this->contentStoreMock->expects($this->once())
            ->method('read')
            ->with('a-fake-template')
            ->will($this->returnValue($file));

        $this->documentMock = $this->getMock(
            '\stdClass',
            ['getBookmarkQueries', 'populateBookmarks']
        );

        $this->controller->expects($this->any())
            ->method('getLoggedInUser')
            ->will($this->returnValue(123));

        $queryData = array_merge(
            $data,
            array(
                'type' => $docType,
                'user' => 123
            ),
            $extraQueryData
        );

        $this->documentMock->expects($this->once())
            ->method('getBookmarkQueries')
            ->with($file, $queryData)
            ->willReturn(null);

        $resultData = array();

        $this->documentMock->expects($this->once())
            ->method('populateBookmarks')
            ->with($file, $resultData)
            ->will($this->returnValue('replaced content'));

        $this->fileStoreMock = $this->getMock(
            '\stdClass',
            [
                'setFile',
                'upload'
            ]
        );

        $fileData = [
            'content' => 'replaced content',
            'meta' => [
                'data' => '{"details":{"documentTemplate":999},"bookmarks":[]}'
            ]
        ];
        $this->fileStoreMock->expects($this->once())
            ->method('setFile')
            ->with($fileData);

        $storedFile = $this->getMock('\stdClass', ['getIdentifier']);
        $storedFile->expects($this->once())
            ->method('getIdentifier')
            ->willReturn('tmp-filename');

        $this->fileStoreMock->expects($this->once())
            ->method('upload')
            ->with('tmp')
            ->will($this->returnValue($storedFile));

        $redirect = $this->getMock('\stdClass', ['toRoute']);

        $redirect->expects($this->once())
            ->method('toRoute')
            ->with(
                $redirectRoute . '/finalise',
                array_merge(['tmpId' => 'tmp-filename'], $routeParams)
            );

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

        $this->controller->processGenerate($data);
    }

    public function testProcessGenerateException()
    {
        $sut = m::mock('\Olcs\Controller\Document\DocumentGenerationController')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $data = m::mock();

        $sut->shouldReceive('_processGenerate')->once()->with($data)->andThrow(new \ErrorException);
        $sut->shouldReceive('addErrorMessage')->once()->with('Unable to generate the document');

        $sut->processGenerate($data);
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
            case 'SubCategory':
                return $this->mockSubCategory($data);
            case 'DocTemplate':
                return $this->mockDocTemplate($data);
            case 'BookmarkSearch':
                return $this->mockBookmarkSearch($data);
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
            default:
                throw new \Exception("Service Locator " . $service . " not mocked");
        }
    }

    public function testListTemplateBookmarksActionWithNoIdReturnsEmptyForm()
    {
        $this->controller->expects($this->once())
            ->method('params')
            ->with('id')
            ->will($this->returnValue(null));

        $view = $this->controller->listTemplateBookmarksAction();
        $form = $view->getVariable('form');
        $bookmarks = $form->get('bookmarks');

        $this->assertCount(1, $form->getFieldsets());
        $this->assertCount(0, $bookmarks->getElements());
    }

    public function testListTemplateBookmarksActionWithIdReturnsCorrectForm()
    {
        $this->controller->expects($this->once())
            ->method('params')
            ->with('id')
            ->will($this->returnValue(123));

        $view = $this->controller->listTemplateBookmarksAction();
        $form = $view->getVariable('form');
        $bookmarks = $form->get('bookmarks');

        $this->assertCount(1, $form->getFieldsets());
        $this->assertCount(2, $bookmarks->getElements());

        $bookmark = $bookmarks->get('sample_bookmark');
        $options = $bookmark->getValueOptions();

        $this->assertEquals(
            [
                1 => 'A paragraph',
                2 => 'Another paragraph'
            ],
            $options
        );
    }

    public function testDownloadTmpAction()
    {
        $this->fileStoreMock = $this->getMock('\stdClass', ['download']);

        $this->controller->expects($this->at(1))
            ->method('params')
            ->with('id')
            ->will($this->returnValue('abc123'));

        $this->controller->expects($this->at(2))
            ->method('params')
            ->with('filename')
            ->will($this->returnValue('a-file.rtf'));

        $this->fileStoreMock->expects($this->once())
            ->method('download')
            ->with('abc123', 'a-file.rtf', 'tmp')
            ->will($this->returnValue('test return value'));

        $result = $this->controller->downloadTmpAction();

        $this->assertEquals('test return value', $result);
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
                    'subCategoryName' => 'A Sub Category',
                ], [
                    'id' => 20,
                    'subCategoryName' => 'Publishable Applications',
                ], [
                    'id' => 30,
                    'subCategoryName' => 'Another Sub Category',
                ],
            ]
        ];
    }

    private function mockDocTemplate($data)
    {
        if (!is_array($data)) {
            switch ($data) {
                case 123:
                    return [
                        'docTemplateBookmarks' => [
                            [
                                'docBookmark' => [
                                    'description' => 'A sample bookmark',
                                    'name' => 'sample_bookmark',
                                    'docParagraphBookmarks' => [
                                        [
                                            'docParagraph' => [
                                                'id' => 1,
                                                'paraTitle' => 'A paragraph'
                                            ]
                                        ], [
                                            'docParagraph' => [
                                                'id' => 2,
                                                'paraTitle' => 'Another paragraph'
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            [
                                'docBookmark' => [
                                    'description' => 'Another sample bookmark',
                                    'name' => 'another_sample_bookmark',
                                    'docParagraphBookmarks' => [
                                        [
                                            'docParagraph' => [
                                                'id' => 3,
                                                'paraTitle' => 'A third paragraph'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ];
                case 888:
                    return [
                        'docTemplateBookmarks' => [
                            [
                                'docBookmark' => [
                                    'description' => 'A sample bookmark',
                                    'name' => 'sample_bookmark',
                                    'docParagraphBookmarks' => [
                                        [
                                            'docParagraph' => [
                                                'id' => 1,
                                                'paraTitle' => 'A paragraph'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ];

                case 999:
                    return [
                        'document' => [
                            'identifier' => 'a-fake-template'
                        ]
                    ];
            }
        }

        return [
            'Results' => []
        ];
    }

    private function mockBookmarkSearch($data)
    {
        return [
            'fake_bookmark' => 'dummy'
        ];
    }
}
