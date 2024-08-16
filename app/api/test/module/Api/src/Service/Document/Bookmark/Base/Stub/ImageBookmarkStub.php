<?php

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark\Base\Stub;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\ImageBookmark;

class ImageBookmarkStub extends ImageBookmark
{
    public function getImage($name, $width = null, $height = null)
    {
        return parent::getImage($name, $width, $height);
    }

    public function getQuery(array $data)
    {
    }

    public function render()
    {
    }
}
