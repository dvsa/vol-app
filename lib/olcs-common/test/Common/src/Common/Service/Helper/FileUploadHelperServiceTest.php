<?php

namespace CommonTest\Common\Service\Helper;

use Common\Exception\File\InvalidMimeException;
use Common\Service\AntiVirus\Scan;
use Common\Service\Helper\UrlHelperService;
use Psr\Container\ContainerInterface;
use Laminas\Form\ElementInterface;
use Laminas\Form\Form;
use Laminas\Http\Request;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Service\Helper\FileUploadHelperService;
use Mockery as m;
use Olcs\Logging\Log\Logger;

/**
 * @covers \Common\Service\Helper\FileUploadHelperService
 */
class FileUploadHelperServiceTest extends MockeryTestCase
{
    /**
     * @var \Mockery\LegacyMockInterface
     */
    public $mockSm;
    /** @var  FileUploadHelperService */
    private $sut;

    /** @var  m\MockInterface */
    private $mockRequest;

    /** @var  \Laminas\Form\FormInterface | m\MockInterface */
    private $mockForm;

    private $mockScan;

    private $mockUrlHelper;

    #[\Override]
    protected function setUp(): void
    {
        $this->mockRequest = m::mock(Request::class);
        $this->mockForm = m::mock(Form::class);
        $this->mockScan = m::mock(Scan::class);
        $this->mockUrlHelper = m::mock(UrlHelperService::class);

        $this->mockSm = m::mock(ContainerInterface::class);

        $this->sut = new FileUploadHelperService($this->mockUrlHelper, $this->mockScan);
        $this->sut->setRequest($this->mockRequest);
        $this->sut->setForm($this->mockForm);
        self::setupLogger();
    }

    public function testSetGetForm(): void
    {
        $this->assertEquals(
            'fakeForm',
            $this->sut->setForm('fakeForm')->getForm()
        );
    }

    public function testSetGetSelector(): void
    {
        $this->assertEquals(
            'fakeSelector',
            $this->sut->setSelector('fakeSelector')->getSelector()
        );
    }

    public function testSetGetCountSelector(): void
    {
        $this->assertEquals(
            'fakeCountSelector',
            $this->sut->setCountSelector('fakeCountSelector')->getCountSelector()
        );
    }

    public function testSetGetUploadCallback(): void
    {
        $this->assertEquals(
            'fakeUploadCallback',
            $this->sut->setUploadCallback('fakeUploadCallback')->getUploadCallback()
        );
    }

    public function testSetGetDeleteCallback(): void
    {
        $callback = function (): void {
        };
        $this->assertEquals(
            $callback,
            $this->sut->setDeleteCallback($callback)->getDeleteCallback()
        );
    }

    public function testSetGetLoadCallback(): void
    {
        $callback = function (): void {
        };
        $this->assertEquals(
            $callback,
            $this->sut->setLoadCallback($callback)->getLoadCallback()
        );
    }

    public function testSetGetRequest(): void
    {
        $this->assertEquals(
            'fakeRequest',
            $this->sut->setRequest('fakeRequest')->getRequest()
        );
    }

    public function testProcessWithGetRequestAndNoLoadCallback(): void
    {
        $this->mockRequest->shouldReceive('isPost')->andReturn(false);

        $this->assertFalse($this->sut->process());
    }

    public function testProcessWithGetRequestPopulatesFileCount(): void
    {
        $this->mockRequest->shouldReceive('isPost')->andReturn(false);

        $this->sut->setCountSelector('my-hidden-field');
        $this->sut->setSelector('my-files');

        $this->sut->setLoadCallback(
            static fn() => ['array-of-files']
        );

        $fieldset = m::mock(ElementInterface::class) // multiple file upload fieldset
        ->shouldReceive('get')
            ->with('list')
            ->andReturn(
                m::mock(ElementInterface::class)
                    ->shouldReceive('setFiles')
                    ->with(['array-of-files'], $this->mockUrlHelper)
                    ->getMock()
            )
            ->getMock();

        $fileCountfield = m::mock(ElementInterface::class)->shouldReceive('setValue')->with(1)->getMock();

        $this->mockForm
            ->shouldReceive('get')
            ->with('my-files')
            ->andReturn($fieldset)
            ->shouldReceive('get')
            ->with('my-hidden-field')
            ->andReturn($fileCountfield)
            ->getMock();

        $this->assertFalse($this->sut->process());
    }

    public function testProcessWithGetRequestAndNotCallableLoadCallback(): void
    {
        $this->mockRequest->shouldReceive('isPost')->andReturn(false);

        $this->sut->setLoadCallback(true); // not callable... obviously

        try {
            $this->sut->process();
        } catch (\Common\Exception\ConfigurationException $configurationException) {
            $this->assertEquals('Load data callback is not callable', $configurationException->getMessage());
            return;
        }

        $this->fail('Expected exception not raised');
    }

    public function testProcessWithPostAndValidFileUpload(): void
    {
        $file = tempnam(sys_get_temp_dir(), "fuhs");
        touch($file);

        $this->mockVirusScan($file, true);

        //  mock request
        $postData = [
            'my-file' => [
                'file-controls' => [
                    'upload' => true
                ]
            ]
        ];

        $fileData = [
            'my-file' => [
                'file-controls' => [
                    'file' => [
                        'error' => 0,
                        'tmp_name' => $file,
                        'name' => 'testfile.zip',
                    ]
                ]
            ]
        ];

        $this->mockRequest
            ->shouldReceive('isPost')->andReturn(true)
            ->shouldReceive('getPost')->andReturn($postData)
            ->shouldReceive('getFiles')->andReturn($fileData);

        $this->sut->setSelector('my-file');
        $this->sut->setUploadCallback(
            function ($data) use ($file) {
                $expected = [
                    'error' => 0,
                    'tmp_name' => $file,
                    'name' => 'testfile.zip',
                ];
                $this->assertEquals($expected, $data);
            }
        );

        $this->assertEquals(true, $this->sut->process());

        unlink($file);
    }

    private function mockVirusScan(string|false $file, bool $isClean): void
    {
        $this->mockScan
            ->shouldReceive('isEnabled')->withNoArgs()->andReturnTrue()
            ->shouldReceive('isClean')->with($file)->once()->andReturn($isClean)
            ->getMock();
    }

    /**
     * @dataProvider fileUploadProvider
     */
    public function testProcessWithPostAndInvalidFileUpload($error, $message): void
    {
        $file = tempnam("/tmp", "fuhs");
        touch($file);

        $postData = [
            'my-file' => [
                'file-controls' => [
                    'upload' => true
                ]
            ]
        ];

        $fileData = [
            'my-file' => [
                'file-controls' => [
                    'file' => [
                        'error' => $error,
                        'tmp_name' => $file,
                        'name' => 'testfile.zip',
                    ]
                ]
            ]
        ];

        $this->mockRequest
            ->shouldReceive('isPost')->andReturn(true)
            ->shouldReceive('getPost')->andReturn($postData)
            ->shouldReceive('getFiles')->andReturn($fileData);

        $this->mockForm
            ->shouldReceive('setMessages')
            ->once()
            ->with(
                [
                    'my-file' => [
                        '__messages__' => [$message]
                    ]

                ]
            );

        $this->sut->setSelector('my-file');
        $this->sut->setUploadCallback(
            static function () {
            }
        );

        static::assertEquals(false, $this->sut->process());

        unlink($file);
    }

    /**
     * @return (int|string)[][]
     *
     * @psalm-return list{list{3, 'message.file-upload-error.3'}, list{4, 'message.file-upload-error.4'}, list{1, 'message.file-upload-error.1'}, list{6, 'message.file-upload-error.6'}}
     */
    public function fileUploadProvider(): array
    {
        return [
            [UPLOAD_ERR_PARTIAL, 'message.file-upload-error.' . UPLOAD_ERR_PARTIAL],
            [UPLOAD_ERR_NO_FILE, 'message.file-upload-error.' . UPLOAD_ERR_NO_FILE],
            [UPLOAD_ERR_INI_SIZE, 'message.file-upload-error.' . UPLOAD_ERR_INI_SIZE],
            [UPLOAD_ERR_NO_TMP_DIR, 'message.file-upload-error.' . UPLOAD_ERR_NO_TMP_DIR],
        ];
    }

    public function testProcessWithPostFileMissing(): void
    {
        $file = 'foo';

        $postData = [
            'my-file' => [
                'file-controls' => [
                    'upload' => true
                ]
            ]
        ];
        $fileData = [
            'my-file' => [
                'file-controls' => [
                    'file' => [
                        'error' => UPLOAD_ERR_OK,
                        'tmp_name' => $file,
                        'name' => 'testfile.zip',
                    ]
                ]
            ]
        ];

        $this->mockRequest
            ->shouldReceive('isPost')->andReturn(true)
            ->shouldReceive('getPost')->andReturn($postData)
            ->shouldReceive('getFiles')->andReturn($fileData);

        $this->mockForm->shouldReceive('setMessages')
            ->once()
            ->with(
                [
                    'my-file' => [
                        '__messages__' => ['message.file-upload-error.missing']
                    ]

                ]
            );

        $this->sut->setSelector('my-file');
        $this->sut->setUploadCallback(
            function ($data) use ($file) {
                $expected = [
                    'error' => 0,
                    'tmp_name' => $file,
                    'name' => 'testfile.zip',
                ];
                $this->assertEquals($expected, $data);
            }
        );

        $this->assertEquals(false, $this->sut->process());
    }

    public function testProcessWithPostFileLengthTooLong(): void
    {
        $file = 'foo';

        $postData = [
            'my-file' => [
                'file-controls' => [
                    'upload' => true
                ]
            ]
        ];

        $fileName = str_repeat('abcde', 40) . '.zip';

        $fileData = [
            'my-file' => [
                'file-controls' => [
                    'file' => [
                        'error' => UPLOAD_ERR_OK,
                        'tmp_name' => $file,
                        'name' => $fileName
                    ]
                ]
            ]
        ];

        $this->mockRequest
            ->shouldReceive('isPost')->andReturn(true)
            ->shouldReceive('getPost')->andReturn($postData)
            ->shouldReceive('getFiles')->andReturn($fileData);

        $this->mockForm->shouldReceive('setMessages')
            ->once()
            ->with(
                [
                    'my-file' => [
                        '__messages__' => [FileUploadHelperService::FILE_UPLOAD_ERR_FILE_LENGTH_TOO_LONG]
                    ]

                ]
            );

        $this->sut->setSelector('my-file');
        $this->sut->setUploadCallback(
            function ($data) use ($file) {
                $expected = [
                    'error' => 0,
                    'tmp_name' => $file,
                    'name' => 'testfile.zip',
                ];
                $this->assertEquals($expected, $data);
            }
        );

        $this->assertEquals(false, $this->sut->process());
    }

    public function testProcessWithPostFileWithVirus(): void
    {
        $file = __FILE__;

        $this->mockVirusScan($file, false);

        $this->mockForm
            ->shouldReceive('setMessages')
            ->once()
            ->with(
                [
                    'my-file' => [
                        '__messages__' => ['message.file-upload-error.virus']
                    ]

                ]
            );

        $postData = [
            'my-file' => [
                'file-controls' => [
                    'upload' => true
                ]
            ]
        ];
        $fileData = [
            'my-file' => [
                'file-controls' => [
                    'file' => [
                        'error' => UPLOAD_ERR_OK,
                        'tmp_name' => $file,
                        'name' => 'testfile.zip',
                    ]
                ]
            ]
        ];
        $this->mockRequest
            ->shouldReceive('isPost')->andReturn(true)
            ->shouldReceive('getPost')->andReturn($postData)
            ->shouldReceive('getFiles')->andReturn($fileData);

        $this->sut->setSelector('my-file');
        $this->sut->setUploadCallback(
            function ($data) use ($file) {
                $expected = [
                    'error' => 0,
                    'tmp_name' => $file,
                    'name' => 'testfile.zip',
                ];
                $this->assertEquals($expected, $data);
            }
        );

        $this->assertEquals(false, $this->sut->process());
    }

    /**
     * @dataProvider dpTestProcessWithPostFileUploadExpection
     */
    public function testProcessWithPostFileUploadExpection($exception, $expectErrMsg): void
    {
        $file = __FILE__;

        $postData = [
            'my-file' => [
                'file-controls' => [
                    'upload' => true,
                ],
            ],
        ];
        $fileData = [
            'my-file' => [
                'file-controls' => [
                    'file' => [
                        'error' => UPLOAD_ERR_OK,
                        'tmp_name' => $file,
                        'name' => 'testfile.zip',
                    ],
                ],
            ]
        ];

        $this->mockRequest
            ->shouldReceive('isPost')->andReturn(true)
            ->shouldReceive('getPost')->andReturn($postData)
            ->shouldReceive('getFiles')->andReturn($fileData);

        $this->mockForm
            ->shouldReceive('setMessages')
            ->once()
            ->with(
                [
                    'my-file' => [
                        '__messages__' => [$expectErrMsg],
                    ],
                ]
            );

        $this->mockVirusScan($file, true);

        $this->sut
            ->setSelector('my-file')
            ->setUploadCallback(
                static function () use ($exception) {
                    throw $exception;
                }
            );

        static::assertEquals(false, $this->sut->process());
    }

    /**
     * @return (InvalidMimeException|\Exception|string)[][]
     *
     * @psalm-return list{array{expection: \Exception, expect: 'message.file-upload-error.any'}, array{expection: InvalidMimeException, expect: 'ERR_MIME'}}
     */
    public function dpTestProcessWithPostFileUploadExpection(): array
    {
        return [
            [
                'expection' => new \Exception('any error'),
                'expect' => 'message.file-upload-error.any',
            ],
            [
                'expection' => new InvalidMimeException('any error'),
                'expect' => 'ERR_MIME',
            ],
        ];
    }

    public function testProcessWithPostAndFileDeletions(): void
    {
        $this->mockRequest->shouldReceive('isPost')->andReturn(true);

        $postData = [
            'my-file' => [
                'list' => [
                    'file1' => [
                        'remove' => true,
                        'id' => 123
                    ]
                ]
            ]
        ];

        $this->mockRequest->shouldReceive('getPost')
            ->andReturn($postData);

        $fieldset = m::mock(ElementInterface::class);
        $fieldset->shouldReceive('getName')
            ->andReturn('file1');

        $listElement = m::mock(ElementInterface::class);
        $listElement->shouldReceive('getFieldsets')
            ->andReturn([$fieldset])
            ->getMock()
            ->shouldReceive('remove')
            ->with('file1');

        $element = m::mock(ElementInterface::class);
        $element->shouldReceive('get')
            ->with('list')
            ->andReturn($listElement);

        $this->sut->setSelector('my-file');
        $this->sut->setDeleteCallback(
            function ($id) {
                $this->assertEquals(123, $id);
                return true;
            }
        );

        $this->sut->setCountSelector('my-hidden-field');

        $fileCountfield = m::mock(ElementInterface::class)
            ->shouldReceive('getValue')->andReturn('3')
            ->shouldReceive('setValue')->with(2)
            ->getMock();

        $this->mockForm
            ->shouldReceive('get')
            ->with('my-hidden-field')
            ->andReturn($fileCountfield);

        $this->mockForm
            ->shouldReceive('get')
            ->with('my-file')
            ->andReturn($element);

        $this->assertEquals(true, $this->sut->process());
    }

    public function testProcessWithPostAndFileDeletionsWithNoDeletionsToDelete(): void
    {
        $this->mockRequest->shouldReceive('isPost')->andReturn(true);

        $postData = [
            'my-file' => [
                'list' => [
                    'file1' => [
                        'remove' => true,
                        'id' => 123
                    ]
                ]
            ]
        ];

        $this->mockRequest->shouldReceive('getPost')
            ->andReturn($postData);

        $listElement = m::mock('\stdClass');
        $listElement->shouldReceive('getFieldsets')
            ->andReturn([])
            ->getMock()
            ->shouldReceive('remove')
            ->with('file1');

        $element = m::mock(ElementInterface::class);
        $element->shouldReceive('get')
            ->with('list')
            ->andReturn($listElement);

        $this->sut->setSelector('my-file');
        $this->sut->setDeleteCallback(
            static function () {
            }
        );

        $this->mockForm->shouldReceive('get')->andReturn($element);

        $this->assertEquals(false, $this->sut->process());
    }

    public function testProcessWithPostAndFileDeletionsWithNoList(): void
    {
        $this->mockRequest->shouldReceive('isPost')->andReturn(true);

        $postData = [
            'my-file' => []
        ];

        $this->mockRequest->shouldReceive('getPost')
            ->andReturn($postData);

        $this->sut->setSelector('my-file');
        $this->sut->setDeleteCallback(
            static function () {
            }
        );

        $this->assertEquals(false, $this->sut->process());
    }

    /**
     * Test upload big files. When post to big size then post and files are empty.
     */
    public function testProcessWithEmptyPostAndFiles(): void
    {
        $this->mockRequest
            ->shouldReceive('isPost')->andReturn(true)
            ->shouldReceive('getPost')->andReturn([])
            ->shouldReceive('getFiles')->andReturn(null);

        $this->mockForm
            ->shouldReceive('setMessages')
            ->once()
            ->with(
                [
                    'my-file' => [
                        '__messages__' => [FileUploadHelperService::FILE_UPLOAD_ERR_PREFIX . '1'],
                    ],
                ]
            );

        $this->sut
            ->setSelector('my-file')
            ->setUploadCallback(
                static function () {
                }
            );

        static::assertEquals(false, $this->sut->process());
    }

    public static function setupLogger(): void
    {
        Logger::setLogger(new \Psr\Log\NullLogger());
    }
}
