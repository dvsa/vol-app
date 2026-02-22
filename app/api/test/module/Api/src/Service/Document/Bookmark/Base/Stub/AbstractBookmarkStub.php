<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark\Base\Stub;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\AbstractBookmark;

class AbstractBookmarkStub extends AbstractBookmark
{
    public const PREFORMATTED = 'unit_Preformatted';
    public const TYPE = 'static';

    public function getToken(): mixed
    {
        return $this->token;
    }

    public function render(): void
    {
    }
}
