<?php

namespace CommonTest\Common\Data\Object\Search;

use Common\Data\Object\Search\InternalSearchAbstract;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class SearchAbstractTest
 * @package CommonTest\Data\Object\Search
 */
abstract class SearchAbstractTest extends MockeryTestCase
{
    protected $class = '';

    /** @var InternalSearchAbstract */
    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->sut = new $this->class();
    }

    public function testGetTableConfig(): void
    {
        $this->assertIsArray($this->sut->getTableConfig());
        $this->assertArrayHasKey('variables', $this->sut->getTableConfig());
        $this->assertArrayHasKey('settings', $this->sut->getTableConfig());
        $this->assertArrayHasKey('attributes', $this->sut->getTableConfig());
        $this->assertArrayHasKey('columns', $this->sut->getTableConfig());
    }

    public function testGetNavigation(): void
    {
        $this->assertIsArray($this->sut->getNavigation());
        $this->assertArrayHasKey('label', $this->sut->getNavigation());
        $this->assertArrayHasKey('route', $this->sut->getNavigation());
        $this->assertArrayHasKey('params', $this->sut->getNavigation());
    }

    public function testGetTitle(): void
    {
        $this->assertIsString($this->sut->getTitle());
    }

    public function testGetKey(): void
    {
        $this->assertIsString($this->sut->getKey());
    }

    public function testGetSearchIndices(): void
    {
        $this->assertIsString($this->sut->getSearchIndices());
    }

    public function testGetDisplayGroup(): void
    {
        $this->assertIsString($this->sut->getDisplayGroup());
    }

    public function testGetFilters(): void
    {
        $this->assertIsArray($this->sut->getFilters());
    }
}
