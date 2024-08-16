<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

/**
 * TransactionManagerInterface
 */
interface TransactionManagerInterface
{
    public function beginTransaction();

    public function commit();

    public function rollback();
}
