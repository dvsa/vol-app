<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Util\Attribute;

use Dvsa\Olcs\Transfer\Util\Attribute\Filter;

/**
 * Filter test
 */
final class FilterTest extends \PHPUnit\Framework\TestCase
{
    public function testGetNameNoOptions()
    {
        $sut = new Filter('StringTrim');

        $this->assertSame('StringTrim', $sut->getName());
        $this->assertNull($sut->getOptions());
    }

    public function testGetNameWithOptions()
    {
        $options = ['charlist' => ' '];

        $sut = new Filter('StringTrim', $options);

        $this->assertSame('StringTrim', $sut->getName());
        $this->assertSame($options, $sut->getOptions());
    }
}

