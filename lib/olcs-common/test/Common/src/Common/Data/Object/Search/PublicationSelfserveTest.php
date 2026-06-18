<?php

namespace CommonTest\Common\Data\Object\Search;

use Common\Data\Object\Search\Aggregations\DateRange\PublishedDateFrom;
use Common\Data\Object\Search\Aggregations\DateRange\PublishedDateTo;
use Common\Data\Object\Search\Aggregations\Terms\LicenceType;
use Common\Data\Object\Search\Aggregations\Terms\TrafficArea;
use Common\Data\Object\Search\Aggregations\Terms\GoodsOrPsv;
use Common\Data\Object\Search\Aggregations\Terms\PublicationType;
use Common\Data\Object\Search\Aggregations\Terms\PublicationSection;

/**
 * @covers \Common\Data\Object\Search\PublicationSelfserve
 */
class PublicationSelfserveTest extends SearchAbstractTest
{
    protected $class = \Common\Data\Object\Search\PublicationSelfserve::class;

    /** @var  \Common\Data\Object\Search\PublicationSelfserve */
    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->sut = new $this->class();

        parent::setUp();
    }

    public function testGetDateRanges(): void
    {
        $dateRanges = $this->sut->getDateRanges();

        $this->assertCount(2, $dateRanges);

        $this->assertInstanceOf(PublishedDateFrom::class, $dateRanges[0]);
        $this->assertInstanceOf(PublishedDateTo::class, $dateRanges[1]);
    }

    #[\Override]
    public function testGetFilters(): void
    {
        $filters = $this->sut->getFilters();

        $this->assertCount(5, $filters);

        $this->assertInstanceOf(LicenceType::class, $filters[0]);
        $this->assertInstanceOf(TrafficArea::class, $filters[1]);
        $this->assertInstanceOf(GoodsOrPsv::class, $filters[2]);
        $this->assertInstanceOf(PublicationType::class, $filters[3]);
        $this->assertInstanceOf(PublicationSection::class, $filters[4]);
    }
}
