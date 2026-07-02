<?php

namespace Dvsa\OlcsTest\Transfer\Command\Template;

use Dvsa\Olcs\Transfer\Command\Template\UpdateTemplateSource;
use PHPUnit\Framework\TestCase;

/**
 * Update template source test
 */
class UpdateTemplateSourceTest extends TestCase
{
    public function testStructure()
    {
        $id = 'cy_GB';
        $source = '{{ var1 }} test {{ var2 }}';

        $data = [
            'id' => $id,
            'source' => $source,
        ];

        $command = UpdateTemplateSource::create($data);

        $this->assertEquals($id, $command->getId());
        $this->assertEquals($source, $command->getSource());

        $this->assertEquals(
            $data,
            $command->getArrayCopy()
        );
    }
}
