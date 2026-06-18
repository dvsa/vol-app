<?php

namespace CommonTest\Service\AntVirus;

use Common\Service\AntiVirus\Scan;
use Psr\Container\ContainerInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

class ScanTest extends MockeryTestCase
{
    /**
     * @var Scan
     */
    private $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->sut = new Scan();
    }

    public function testInvoke(): void
    {
        $config = [
            'antiVirus' => [
                'cliCommand' => 'scanfile %s'
            ]
        ];
        $mockServiceManager = m::mock(ContainerInterface::class);
        $mockServiceManager->shouldReceive('get')->with('config')->once()->andReturn($config);

        $object = $this->sut->__invoke($mockServiceManager, Scan::class);

        $this->assertSame('scanfile %s', $object->getCliCommand());
    }

    public function testInvokeNoConfig(): void
    {
        $mockServiceManager = m::mock(ContainerInterface::class);
        $mockServiceManager->shouldReceive('get')->with('config')->once()->andReturn([]);

        $object = $this->sut->__invoke($mockServiceManager, Scan::class);

        $this->assertSame(null, $object->getCliCommand());
    }

    public function testIsEnabled(): void
    {
        $this->assertSame(false, $this->sut->isEnabled());
        $this->sut->setCliCommand('XXX');
        $this->assertSame(true, $this->sut->isEnabled());
    }

    public function testIsCleanMissingCommand(): void
    {
        $this->expectException(\Common\Exception\ConfigurationException::class);
        $this->sut->isClean('foo.bar');
    }

    public function testIsCleanMissingCommandReplacement(): void
    {
        $this->sut->setCliCommand('XXX');
        $this->expectException(
            \Common\Exception\ConfigurationException::class
        );
        $this->sut->isClean('foo.bar');
    }

    public function testIsCleanFileNotString(): void
    {
        $this->sut->setCliCommand('scan %s');
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->isClean(1);
    }

    public function testIsCleanFileNotExists(): void
    {
        $this->sut->setCliCommand('scan %s');
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->isClean('foo.bar');
    }

    public function testIsCleanOk(): void
    {
        $mockShell = m::mock(\Common\Filesystem\Shell::class);
        $mockShell->shouldReceive('fileperms')->with(__FILE__)->once()->andReturn(octdec(600));
        $mockShell->shouldReceive('chmod')->with(__FILE__, 0660)->once()->andReturn(true);
        $mockShell->shouldReceive('execute')->with('scan ' . __FILE__)->once()->andReturn(0);
        $mockShell->shouldReceive('chmod')->with(__FILE__, octdec(600))->once()->andReturn(true);
        $this->sut->setShell($mockShell);

        $this->sut->setCliCommand('scan %s');

        $result = $this->sut->isClean(__FILE__);
        $this->assertSame(true, $result);
    }

    public function testIsCleanFailed(): void
    {
        $mockShell = m::mock(\Common\Filesystem\Shell::class);
        $mockShell->shouldReceive('fileperms')->with(__FILE__)->once()->andReturn(octdec(644));
        $mockShell->shouldReceive('chmod')->with(__FILE__, 0660)->once()->andReturn(true);
        $mockShell->shouldReceive('execute')->with('scan ' . __FILE__)->once()->andReturn(1);
        $mockShell->shouldReceive('chmod')->with(__FILE__, octdec(644))->once()->andReturn(true);
        $this->sut->setShell($mockShell);

        $this->sut->setCliCommand('scan %s');

        $result = $this->sut->isClean(__FILE__);
        $this->assertSame(false, $result);
    }
}
