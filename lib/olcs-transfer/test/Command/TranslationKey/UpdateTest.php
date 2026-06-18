<?php

namespace Dvsa\OlcsTest\Transfer\Command\TranslationKey;

use Dvsa\Olcs\Transfer\Command\TranslationKey\Update;

/**
 * Update test
 */
class UpdateTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'id' => 'STRID',
            'translationsArray' => [
                'en_GB' => 'English',
                'cy_GB' => 'Welsh',
                'en_NI' => 'English (NI)',
                'cy_NI' => 'Welsh (NI)'
            ]
        ];

        $command = Update::create($data);

        $this->assertEquals($data['id'], $command->getId());
        $this->assertEquals($data['translationsArray'], $command->getTranslationsArray());
    }
}
