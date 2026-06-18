<?php

namespace CommonTest\Service\Script;

use Common\Service\Script\ScriptFactory;
use Psr\Container\ContainerInterface;

class ScriptFactoryTest extends \PHPUnit\Framework\TestCase
{
    public $inlineScript;
    public $service;
    protected $config = [];

    /**
     * test before hook
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->config = [
            'local_scripts_path' => [__DIR__ . '/TestResources/']
        ];

        $this->inlineScript = new \Laminas\View\Helper\InlineScript();

        $vhm = $this->createMock(\Laminas\View\HelperPluginManager::class);
        $vhm->expects($this->any())
            ->method('get')
            ->with('inlineScript')
            ->will($this->returnValue($this->inlineScript));

        $valueMap = [
            ['ViewHelperManager', $vhm],
            ['Config', $this->config],
        ];

        $sl = $this->createMock(ContainerInterface::class);
        $sl->expects($this->any())
           ->method('get')
           ->will($this->returnValueMap($valueMap));

        $this->service = new ScriptFactory();
        $this->service->__invoke($sl, ScriptFactory::class);
    }

    public function testLoadFileWithNonExistentPath(): void
    {
        try {
            $this->service->loadFile('/foo/bar');
        } catch (\Exception $exception) {
            $this->assertEquals('Attempted to load invalid script file "/foo/bar"', $exception->getMessage());
            return;
        }

        $this->fail('Expected exception not thrown');
    }

    public function testLoadFileWithExistentPath(): void
    {
        $this->service->loadFile('stub');
        $jsArray = [];

        foreach ($this->inlineScript as $item) {
            $jsArray[] = $item->source;
        }

        $this->assertEquals(
            $jsArray,
            [
                "alert(\"I am a dummy fixture!\");\n"
            ]
        );
    }

    public function testLoadFilesWhereOneOrMoreDoesNotExist(): void
    {
        try {
            $this->service->loadFiles(['stub', 'foo']);
        } catch (\Exception $exception) {
            $this->assertEquals('Attempted to load invalid script file "foo"', $exception->getMessage());
            return;
        }

        $this->fail('Expected exception not thrown');
    }

    public function testLoadFilesWhereAllFilesExist(): void
    {
        $this->service->loadFiles(['stub', 'another_stub']);

        $jsArray = [];

        foreach ($this->inlineScript as $item) {
            $jsArray[] = $item->source;
        }

        $this->assertEquals(
            $jsArray,
            [
                "alert(\"I am a dummy fixture!\");\n",
                "alert(\"I am a stub!\");\n"
            ]
        );
    }
}
