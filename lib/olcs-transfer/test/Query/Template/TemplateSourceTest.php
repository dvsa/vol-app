<?php

namespace Dvsa\OlcsTest\Transfer\Query\Template;

use Dvsa\Olcs\Transfer\Query\Template\TemplateSource;
use PHPUnit\Framework\TestCase;

/**
 * TemplateSource Test
 */
class TemplateSourceTest extends TestCase
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
