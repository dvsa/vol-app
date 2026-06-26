<?php

namespace CommonTest\Service\Table\Type;

use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\TableBuilder;
use Laminas\ServiceManager\ServiceManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Service\Table\Type\Link;
use Common\Service\Helper\StackHelperService;

class LinkTest extends MockeryTestCase
{
    protected $sut;

    protected $table;

    protected $sm;

    #[\Override]
    protected function setUp(): void
    {
        $this->sm = new ServiceManager();

        $this->table = m::mock(TableBuilder::class);
        $this->table->expects('getServiceLocator')
            ->andReturn($this->sm);

        $this->sut = new Link($this->table);
    }

    public function testRender(): void
    {
        $data = [
            'some' => [
                'id' => 123
            ]
        ];
        $column = [
            'route' => 'foo',
            'params' => [
                'id' => '{some->id}'
            ]
        ];
        $formattedContent = '<a href="[LINK]">Some Link</a>';
        $expected = '<a href="URL">Some Link</a>';

        // Mocks
        $urlHelper = m::mock(UrlHelperService::class);
        $this->sm->setService('Helper\Url', $urlHelper);
        // @NOTE We use the real stack helper here, as it's a useful component test
        // and is only a tiny utility class that is also fully tested elsewhere
        $this->sm->setService('Helper\Stack', new StackHelperService());

        $urlHelper->shouldReceive('fromRoute')
            ->once()
            ->with('foo', ['id' => 123])
            ->andReturn('URL');

        $this->assertEquals($expected, $this->sut->render($data, $column, $formattedContent));
    }
}
