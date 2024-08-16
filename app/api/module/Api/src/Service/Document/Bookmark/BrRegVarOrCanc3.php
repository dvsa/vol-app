<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

/**
 * BrRegVarOrCanc3 Bookmark
 */
class BrRegVarOrCanc3 extends BrRegVarOrCanc
{
    protected $renderNew = 'register';
    protected $renderVar = 'vary';
    protected $renderCanc = 'cancel';
}
