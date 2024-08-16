<?php

namespace Dvsa\Olcs\Api\Entity;

/**
 * Deletable interface
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
interface DeletableInterface
{
    public function getId();
    public function canDelete();
}
