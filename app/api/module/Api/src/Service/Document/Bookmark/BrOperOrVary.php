<?php

namespace Dvsa\Olcs\Api\Service\Document\Bookmark;

/**
 * BrOperOrVary Bookmark
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BrOperOrVary extends AbstractBrRegOrVary
{
    protected $renderReg = 'operate this service';
    protected $renderVary = 'vary this registration';
}
