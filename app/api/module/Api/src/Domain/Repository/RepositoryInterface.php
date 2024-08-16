<?php

/**
 * Repository Interface
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

/**
 * Repository Interface
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
interface RepositoryInterface extends ReadonlyRepositoryInterface
{
    public function save($entity);

    public function delete($entity);
}
