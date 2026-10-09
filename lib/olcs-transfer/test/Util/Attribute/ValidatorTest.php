<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Util\Attribute;

use Dvsa\Olcs\Transfer\Util\Attribute\Validator;

/**
 * Validator test
 */
final class ValidatorTest extends \PHPUnit\Framework\TestCase
{
    public function testGetNameNoOptions()
    {
        $sut = new Validator('NotEmpty');

        $this->assertSame('NotEmpty', $sut->getName());
        $this->assertNull($sut->getOptions());
    }

    public function testGetNameWithOptions()
    {
        $options = ['min' => 1, 'max' => 10];

        $sut = new Validator('StringLength', $options);

        $this->assertSame('StringLength', $sut->getName());
        $this->assertSame($options, $sut->getOptions());
    }
}

