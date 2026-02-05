<?php

declare(strict_types=1);

/**
 * Leter Bulk send cmd test
 *
 * @author Andy Newton <andy@vitri.ltd>
 */

namespace Dvsa\OlcsTest\Api\Domain\Command\BulkSend;

use Dvsa\Olcs\Api\Domain\Command\BulkSend\Letter;

class LetterTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $command = Letter::create(
            [
                'user' => 291,
                'templateSlug' => 'a-template-slug',
                'documentIdentifier' => '/some/path/doc.rtf'
            ]
        );

        $this->assertEquals(291, $command->getUser());
        $this->assertEquals('a-template-slug', $command->getTemplateSlug());
        $this->assertEquals('/some/path/doc.rtf', $command->getDocumentIdentifier());
    }
}
