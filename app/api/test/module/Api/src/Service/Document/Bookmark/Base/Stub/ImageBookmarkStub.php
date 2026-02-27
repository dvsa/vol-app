<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark\Base\Stub;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\ImageBookmark;

class ImageBookmarkStub extends ImageBookmark
{
    #[\Override]
    public function getImage($name, $width = null, $height = null)
    {
        return parent::getImage($name, $width, $height);
    }

    public function getQuery(array $data): void
    {
    }

    public function render(): void
    {
    }
}
