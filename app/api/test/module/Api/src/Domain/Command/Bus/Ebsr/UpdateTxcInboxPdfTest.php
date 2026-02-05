<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Command\Bus\Ebsr;

use Dvsa\Olcs\Api\Domain\Command\Bus\Ebsr\UpdateTxcInboxPdf;

/**
 * Update Txc Inbox PDF Test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class UpdateTxcInboxPdfTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $id = 1;
        $document = 2;
        $pdfType = 'pdf type';

        $command = UpdateTxcInboxPdf::create(
            [
                'id' => $id,
                'document' => $document,
                'pdfType' => $pdfType,
            ]
        );

        $this->assertEquals($id, $command->getId());
        $this->assertEquals($document, $command->getDocument());
        $this->assertEquals($pdfType, $command->getPdfType());
    }
}
