<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Config;

use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Document\CanAccessDocumentWithId;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Document\CanDeleteDocumentWithId;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Document\CanDownloadDocumentWithId;
use Dvsa\Olcs\Api\Domain\Validation\Validators\CanDownloadDocument;
use PHPUnit\Framework\TestCase;

final class DocumentDownloadValidationTest extends TestCase
{
    public function testOnlyDownloadUsesTheDownloadRule(): void
    {
        $map = require __DIR__ . '/../../../../../module/Api/config/validation-map/document.config.php';

        $this->assertSame(CanDownloadDocumentWithId::class, $map[QueryHandler\Document\Download::class]);
        $this->assertSame(CanDeleteDocumentWithId::class, $map[CommandHandler\Document\DeleteDocument::class]);
        $this->assertSame(CanAccessDocumentWithId::class, $map[CommandHandler\Document\PrintLetter::class]);
        $this->assertSame(CanAccessDocumentWithId::class, $map[QueryHandler\Document\PrintLetter::class]);
        $this->assertNotContains(
            CanDownloadDocumentWithId::class,
            array_diff_key($map, [QueryHandler\Document\Download::class => true]),
        );
    }

    public function testTheDownloadRuleIsRegistered(): void
    {
        $validators = require __DIR__ . '/../../../../../module/Api/config/validators.config.php';

        $this->assertSame(CanDownloadDocument::class, $validators['factories']['canDownloadDocument']);
    }
}
