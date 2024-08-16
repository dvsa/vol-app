<?php

/**
 * ByDocumentStoreId test
 */

namespace Dvsa\OlcsTest\Api\Domain\Query\Document;

use Dvsa\Olcs\Api\Domain\Query\Document\ByDocumentStoreId;

class ByDocumentStoreIdTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $documentStoreId = 'ABC';

        $query = ByDocumentStoreId::create(
            [
                'documentStoreId' => $documentStoreId,
            ]
        );

        $this->assertSame($documentStoreId, $query->getDocumentStoreId());
        $this->assertSame(
            [
                'documentStoreId' => $documentStoreId,
            ],
            $query->getArrayCopy()
        );
    }
}
