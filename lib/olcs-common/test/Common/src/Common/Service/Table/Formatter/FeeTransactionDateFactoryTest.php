<?php

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Helper\StackHelperService;
use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\FeeTransactionDate;
use Common\Service\Table\Formatter\FeeTransactionDateFactory;
use Common\Service\Table\Formatter\StackValue;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Psr\Container\ContainerInterface;

/**
 * FeeTransactionDateTest builds the formatter directly with the correct arguments, so it cannot see
 * how the factory assembles it. The factory passed (Date, StackValue) into a constructor declared
 * (StackValue, Date) and nothing noticed, because nothing resolved it.
 *
 * Both constructor parameters are typed, so reaching an instance at all is the regression assertion.
 * format() is driven as well so the argument *order* is proven by behaviour rather than by types
 * alone — two parameters of the same type would swap silently.
 */
final class FeeTransactionDateFactoryTest extends TestCase
{
    public function testInvoke(): void
    {
        $sut = new FeeTransactionDateFactory();

        $service = $sut->__invoke($this->container(), FeeTransactionDate::class);

        $this->assertInstanceOf(FeeTransactionDate::class, $service);
    }

    public function testInvokeAssemblesAFormatterThatResolvesThenFormats(): void
    {
        $sut = new FeeTransactionDateFactory();

        $service = $sut->__invoke($this->container(), FeeTransactionDate::class);

        // The stack value has to be resolved first and the result handed to the date formatter. With
        // the arguments the other way round the value never reaches the date formatter at all.
        $this->assertSame(
            '01/09/2015',
            $service->format(['child' => ['someDate' => '2015-09-01']], ['stack' => 'child->someDate'])
        );
    }

    private function container(): ContainerInterface
    {
        $container = m::mock(ContainerInterface::class);
        $container->expects('get')->with(Date::class)->andReturn(new Date());
        $container->expects('get')->with(StackValue::class)->andReturn(new StackValue(new StackHelperService()));

        return $container;
    }
}
