<?php

/**
 * Document generation controller tests
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace OlcsTest\Controller\Document;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

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
                    'getForm',
                    'loadScripts',
                    'getServiceLocator',
                    'getRequest',
                    'redirect',
                    'getLoggedInUser',
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

        parent::setUp();
    }

    public function testGenerateActionWithGetAndNoTmpData()
    {
        $this->controller->expects($this->at(2))
            ->method('params')
            ->with('type')
            ->will($this->returnValue('licence'));

        $this->controller->expects($this->at(4))
            ->method('params')
            ->with('tmpId')
            ->will($this->returnValue(null));

        $response = $this->controller->generateAction();

        $variables = $response->getVariables();

        $this->assertEquals('Generate letter', $variables['pageTitle']);
    }

    public function testGenerateActionWithGetAndTmpData()
    {
        $this->controller->expects($this->at(2))
            ->method('params')
            ->with('type')
            ->will($this->returnValue('licence'));

        $this->controller->expects($this->at(4))
            ->method('params')
            ->with('tmpId')
            ->will($this->returnValue('tmp_123'));

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

        $this->controller->expects($this->any())
            ->method('params')
            ->with('type')
            ->will($this->returnValue('licence'));

        $this->controller->expects($this->once())
            ->method('processGenerate');

        $this->controller->generateAction();
    }

    public function testProcessGenerate()
    {
        $fromRoute = $this->getMock('\stdClass', ['fromRoute']);
        $fromRoute->expects($this->any())
            ->method('fromRoute')
            ->will($this->returnValue(array('type' => 'licence')));

        $this->controller->expects($this->once())
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
                'type' => 'licence',
                'user' => 123
            )
        );
        $this->documentMock->expects($this->once())
            ->method('getBookmarkQueries')
            ->with('application/rtf', 'dummy content', $queryData);

        $resultData = array(
            'fake_bookmark' => 'dummy'
        );
        $this->documentMock->expects($this->once())
            ->method('populateBookmarks')
            ->with('application/rtf', 'dummy content', $resultData)
            ->will($this->returnValue('replaced content'));

        $this->fileStoreMock = $this->getMock(
            '\stdClass',
            [
                'setFile',
                'upload'
            ]
        );

        $this->fileStoreMock->expects($this->once())
            ->method('setFile')
            ->with($file);

        $this->fileStoreMock->expects($this->once())
            ->method('upload')
            ->with('tmp/documents')
            ->will($this->returnValue('tmp-filename'));

        $redirect = $this->getMock('\stdClass', ['toRoute']);

        $redirect->expects($this->once())
            ->method('toRoute')
            ->with('licence/documents/finalise', ['tmpId' => 'tmp-filename']);

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

        $this->controller->processGenerate($data);
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

    private function mockDocTemplate($data)
    {
        if (isset($data['id'])) {
            switch ($data['id']) {
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
