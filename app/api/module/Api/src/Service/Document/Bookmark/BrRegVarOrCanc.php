<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

/**
 * BrRegVarOrCanc Bookmark
 */
class BrRegVarOrCanc extends AbstractBrRegVarOrCanc
{
    protected $renderNew = 'commence';
    protected $renderVar = 'vary';
    protected $renderCanc = 'cancel';
}
