<?php

/**
 * Class TransactionManager
 * @package Dvsa\Olcs\Api\Domain\Repository
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\EntityManagerInterface;

/**
 * Class TransactionManager
 * @package Dvsa\Olcs\Api\Domain\Repository
 */
final readonly class TransactionManager implements TransactionManagerInterface
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function beginTransaction()
    {
        $this->em->beginTransaction();
    }

    public function commit()
    {
        $this->em->commit();
    }

    public function rollback()
    {
        $this->em->rollback();
    }
}
