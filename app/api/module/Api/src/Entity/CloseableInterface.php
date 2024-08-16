<?php

namespace Dvsa\Olcs\Api\Entity;

/**
 * Closeable interface
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
interface CloseableInterface
{
    public function close();
    public function canClose();
    public function isClosed();
}
