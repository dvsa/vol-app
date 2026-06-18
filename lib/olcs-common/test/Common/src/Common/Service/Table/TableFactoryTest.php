<?php

namespace CommonTest\Service\Table;

use Common\Rbac\Service\Permission;
use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\Formatter\FormatterPluginManager;
use Common\Service\Table\TableBuilder;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Translation\TranslatorDelegator;
use Laminas\ServiceManager\ServiceManager;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

class TableFactoryTest extends MockeryTestCase
{
    public function testInvoke(): void
    {
        $mockPermissionService = m::mock(Permission::class);
        $mockTranslator = m::mock(TranslatorDelegator::class);
        $urlHelperService = m::mock(UrlHelperService::class);
        $formatterPluginManager = m::mock(FormatterPluginManager::class);
        $config = ['config1', 'config2'];

        $serviceLocator = m::mock(ServiceManager::class);
        $serviceLocator->expects('get')->with('Config')->andReturn($config);
        $serviceLocator->expects('get')->with(Permission::class)->andReturn($mockPermissionService);
        $serviceLocator->expects('get')->with('translator')->andReturn($mockTranslator);
        $serviceLocator->expects('get')->with('Helper\Url')->andReturn($urlHelperService);
        $serviceLocator->expects('get')->with(FormatterPluginManager::class)->andReturn($formatterPluginManager);

        $tableFactory = new TableFactory();

        $table = $tableFactory->__invoke($serviceLocator, TableBuilder::class)->getTableBuilder();

        $this->assertTrue($table instanceof TableBuilder);
    }

    /**
     * Test buildTable
     */
    public function testBuildTable(): void
    {
        $name = 'foo';
        $data = ['foo' => 'var'];
        $params = ['cake' => 'bbar'];
        $render = true;

        $mockTable = $this->createPartialMock(TableBuilder::class, ['buildTable']);

        $mockTable->expects($this->once())
            ->method('buildTable')
            ->with($name, $data, $params, $render)
            ->willReturn('TABLE');

        $tableFactory = $this->createPartialMock(TableFactory::class, ['getTableBuilder']);

        $tableFactory->expects($this->once())
            ->method('getTableBuilder')
            ->willReturn($mockTable);

        $result = $tableFactory->buildTable($name, $data, $params, $render);
        $this->assertEquals('TABLE', $result);
    }
}
