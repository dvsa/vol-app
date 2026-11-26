<?php

declare(strict_types=1);

/**
 * SearchDateRangeFieldset Test
 *
 * @author Valtech <uk@valtech.co.uk>
 */

namespace OlcsTest\Form\Element;

use Olcs\Form\Element\SearchDateRangeFieldset;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * SearchDateRangeFieldset Test
 *
 * @author Valtech <uk@valtech.co.uk>
 */
final class SearchDateRangeFieldsetTest extends TestCase
{
    public function testDateRangesValidateRawParts(): void
    {
        $range = new \Common\Data\Object\Search\Aggregations\DateRange\PublishedDateFrom();
        $service = m::mock(\Common\Service\Data\Search\Search::class);
        $service->shouldReceive('setIndex')->with('publication')->once();
        $service->shouldReceive('getDateRanges')->once()->andReturn([$range]);
        $fieldset = new SearchDateRangeFieldset('dateRanges', ['index' => 'publication']);
        $fieldset->setSearchService($service);
        $fieldset->init();
        $form = new \Laminas\Form\Form();
        $form->add($fieldset);
        $field = $range->getKey();
        $form->setData(['dateRanges' => [$field => ['day' => '12', 'month' => '', 'year' => '2020']]]);
        self::assertFalse($form->isValid());
        self::assertArrayHasKey('dateIncomplete', $form->getMessages()['dateRanges'][$field]);
        self::assertSame('12', $fieldset->get($field)->getDayElement()->getValue());
        $form->setData(['dateRanges' => [$field => ['day' => '', 'month' => '', 'year' => '']]]);
        self::assertTrue($form->isValid());
        $form->setData(['dateRanges' => [$field => ['day' => '12', 'month' => '03', 'year' => '2020']]]);
        self::assertTrue($form->isValid());
        self::assertSame('2020-03-12', $form->getData()['dateRanges'][$field]);
    }

    public function testSearchAwareTraitByProxy(): void
    {
        $service = m::mock(\Common\Service\Data\Search\Search::class);

        $sut = new SearchDateRangeFieldset();

        $this->assertSame($service, $sut->setSearchService($service)->getSearchService());
    }

    public function initTest(): void
    {
        $filter = m::mock('stdClass');
        $filter->shouldReceive('getKey')->twice()->andReturn('Key');
        $filter->shouldReceive('getTitle')->twice()->andReturn('Title');
        $filters = [$filter, $filter];

        $service = m::mock(\Common\Service\Data\Search\Search::class);
        $service->shouldReceive('getFilters')->withNoArgs()->andReturn($filters);

        $sut = new SearchDateRangeFieldset();

        $this->assertCount($sut->count(), 2);
    }
}
