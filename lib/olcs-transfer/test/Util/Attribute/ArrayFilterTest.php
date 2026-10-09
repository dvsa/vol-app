<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Util\Attribute;

use Dvsa\Olcs\Transfer\Util\Attribute\ArrayFilter;
use Dvsa\Olcs\Transfer\Util\Attribute\Filter;

/**
 * ArrayFilter test
 */
final class ArrayFilterTest extends \PHPUnit\Framework\TestCase
{
    public function testIsInstanceOfFilter()
    {
        $sut = new ArrayFilter('StringTrim');

        $this->assertInstanceOf(Filter::class, $sut);
        $this->assertSame('StringTrim', $sut->getName());
    }
}
