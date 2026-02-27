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

    #[\Override]
    public function beginTransaction()
    {
        $this->em->beginTransaction();
    }

    #[\Override]
    public function commit()
    {
        $this->em->commit();
    }

    #[\Override]
    public function rollback()
    {
        $this->em->rollback();
    }
}
