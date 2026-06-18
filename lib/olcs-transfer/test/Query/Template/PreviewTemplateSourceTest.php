<?php

namespace Dvsa\OlcsTest\Transfer\Query\Template;

use Dvsa\Olcs\Transfer\Query\Template\PreviewTemplateSource;
use PHPUnit\Framework\TestCase;

/**
 * PreviewTemplateSource Test
 */
class PreviewTemplateSourceTest extends TestCase
{
    public function testStructure()
    {
        $id = 47;
        $source = '{{ var1 }} test {{ var2 }}';

        $data = [
            'id' => $id,
            'source' => $source,
        ];

        $sut = PreviewTemplateSource::create($data);

        $this->assertEquals($id, $sut->getId());
        $this->assertEquals($source, $sut->getSource());

        $this->assertEquals(
            $data,
            $sut->getArrayCopy()
        );
    }
}
