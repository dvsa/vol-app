<?php

namespace Dvsa\Olcs\Api\Entity;

/**
 * Reopenable interface
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
interface ReopenableInterface
{
    public function reopen();
    public function canReopen();
}
