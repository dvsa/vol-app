<?php

namespace Dvsa\OlcsTest\Email\Transport;

use Aws\S3\S3Client;
use Dvsa\Olcs\Email\Transport\MultiTransportOptions;
use Dvsa\Olcs\Email\Transport\S3File;
use Dvsa\Olcs\Email\Transport\S3FileOptions;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Mail\Transport\File;
use Laminas\Mail\Transport\Sendmail;

/**
 * Class MultiTransportOptionsTest
 */
class MultiTransportOptionsTest extends MockeryTestCase
{
    /**
     * @var MultiTransportOptions
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new MultiTransportOptions([], new S3FileOptions([], new S3Client([
            'region' => 'eu-west-1',
            'version' => 'latest'
        ])));
    }

    public function testSetGet()
    {
        $this->assertSame([], $this->sut->getTransport());

        $this->sut->setTransport([['type' => 'File'], ['type' => 'SendMail']]);

        $transports = $this->sut->getTransport();
        $this->assertInstanceOf(File::class, $transports[0]);
        $this->assertInstanceOf(Sendmail::class, $transports[1]);
    }

    public function testSetTransport()
    {
        $transports = [
            ['type' => 'SendMail'],
            ['type' => \Dvsa\Olcs\Email\Transport\S3File::class,
                'options' => [
                    'bucket' => 'devapp-olcs-pri-olcs-autotest-s3',
                    'key' => '/olcs.da.nonprod.dvsa.aws/email'
                ]
            ],
        ];

        $this->sut->setTransport($transports);
        $mailTransports = $this->sut->getTransport();

        foreach ($mailTransports as $mailTransport) {
            if ($mailTransport instanceof S3File) {
                $this->assertNotNull($mailTransport->getOptions());
            }
        }
    }
}
