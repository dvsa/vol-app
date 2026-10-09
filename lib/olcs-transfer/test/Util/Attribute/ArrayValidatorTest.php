<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Util\Attribute;

use Dvsa\Olcs\Transfer\Util\Attribute\ArrayValidator;
use Dvsa\Olcs\Transfer\Util\Attribute\Validator;

/**
 * ArrayValidator test
 */
final class ArrayValidatorTest extends \PHPUnit\Framework\TestCase
{
    public function testIsInstanceOfValidator()
    {
        $sut = new ArrayValidator('NotEmpty');

        $this->assertInstanceOf(Validator::class, $sut);
        $this->assertSame('NotEmpty', $sut->getName());
    }
}

