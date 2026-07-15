<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\Template;

use Dvsa\Olcs\Transfer\Query\Template\TemplateSource;
use PHPUnit\Framework\TestCase;

/**
 * TemplateSource Test
 */
final class TemplateSourceTest extends TestCase
{
    public function testStructure()
    {
        $id = 47;

        $data = [
            'id' => $id,
        ];

        $sut = TemplateSource::create($data);

        $this->assertEquals($id, $sut->getId());

        $this->assertEquals(
            $data,
            $sut->getArrayCopy()
        );
    }
}
