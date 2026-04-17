<?php

declare(strict_types=1);

/**
 * Email Bulk send cmd test
 *
 * @author Andy Newton <andy@vitri.ltd>
 */

namespace Dvsa\OlcsTest\Api\Domain\Command\BulkSend;

use Dvsa\Olcs\Api\Domain\Command\BulkSend\Email;

class EmailTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $command = Email::create(
            [
                'user' => 291,
                'templateName' => 'gv-ni-standard',
                'documentIdentifier' => '/some/path/doc.rtf'
            ]
        );

        $this->assertEquals(291, $command->getUser());
        $this->assertEquals('gv-ni-standard', $command->getTemplateName());
        $this->assertEquals('/some/path/doc.rtf', $command->getDocumentIdentifier());
    }
}
