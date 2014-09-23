<?php

/**
 * Document controller tests
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace OlcsTest\Controller\Document;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Document controller tests
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class DocumentControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../../config/application.config.php'
        );
        $this->controller = $this->getMock(
            '\Olcs\Controller\Document\DocumentController',
            array(
                'makeRestCall',
                'params',
                'getServiceLocator',
                'notFoundAction'
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

        $this->contentStoreMock = $this->getMock('\stdClass', ['download']);

        parent::setUp();
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
        $this->controller->expects($this->once())
            ->method('params')
            ->with('path')
            ->will($this->returnValue('a-file'));

        $this->contentStoreMock->expects($this->once())
            ->method('download')
            ->with('tmp/documents/a-file', 'a-file.rtf')
            ->will($this->returnValue('test return value'));

        $result = $this->controller->downloadTmpAction();

        $this->assertEquals('test return value', $result);
    }

    public function testDownloadActionWithInvalidId()
    {
        $this->controller->expects($this->at(1))
            ->method('params')
            ->with('id')
            ->will($this->returnValue(123));

        $this->controller->expects($this->once())
            ->method('notFoundAction');

        $this->controller->downloadAction();
    }

    public function testDownloadActionWithFilenameMismatch()
    {
        $this->controller->expects($this->at(0))
            ->method('params')
            ->with('filename')
            ->will($this->returnValue('wrong-name.rtf'));

        $this->controller->expects($this->at(1))
            ->method('params')
            ->with('id')
            ->will($this->returnValue(456));

        $this->controller->expects($this->once())
            ->method('notFoundAction');

        $this->controller->downloadAction();
    }

    public function testDownloadActionWithValidDetails()
    {
        $this->controller->expects($this->at(0))
            ->method('params')
            ->with('filename')
            ->will($this->returnValue('my-document.rtf'));

        $this->controller->expects($this->at(1))
            ->method('params')
            ->with('id')
            ->will($this->returnValue(456));

        $this->contentStoreMock->expects($this->once())
            ->method('download')
            ->with('documents/abc_123', 'my-document.rtf')
            ->will($this->returnValue('test return value'));

        $this->controller->downloadAction();
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
        case 'DocTemplate':
            return $this->mockDocTemplate($data);
        case 'Document':
            return $this->mockDocument($data);
        default:
            throw new \Exception("Service call " . $service . " not mocked");
        }
    }

    public function mockServiceLocator($service)
    {
        $fileUploaderMock = $this->getMock('\stdClass', ['getUploader']);
        $fileUploaderMock->expects($this->any())
            ->method('getUploader')
            ->will($this->returnValue($this->contentStoreMock));

        switch ($service) {
        case 'FileUploader':
            return $fileUploaderMock;
        }
    }

    private function mockDocTemplate($data)
    {
        switch ($data['id']) {
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
        }
    }

    private function mockDocument($data)
    {
        switch ($data['id']) {
        case 123:
            return null;
        case 456:
            return [
                'identifier' => 'abc_123',
                'filename' => 'my-document.rtf'
            ];
        }
    }
}
