<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Util\Attribute;

use Dvsa\Olcs\Transfer\Util\Attribute\Partial;
use Laminas\Form\Annotation\ComposedObject;

/**
 * Partial test
 */
final class PartialTest extends \PHPUnit\Framework\TestCase
{
    public function testGetComposedObjectTargetObject()
    {
        $sut = new Partial(\stdClass::class);

        $this->assertSame(\stdClass::class, $sut->getComposedObject());
    }
}

