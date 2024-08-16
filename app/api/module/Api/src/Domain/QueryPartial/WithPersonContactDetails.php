<?php

/**
 * With Person Contact details. Returns Contact details + address, person and phone contact information
 */

namespace Dvsa\Olcs\Api\Domain\QueryPartial;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * With PersonContactDetails
 */
final readonly class WithPersonContactDetails implements QueryPartialInterface
{
    public function __construct(
        /**
         * @var With
         */
        private QueryPartialInterface $with
    ) {
    }

    /**
     * Joins on all contact details / address / person / phone contact relationships
     *
     * @param QueryBuilder $qb
     * @param array $arguments
     */
    public function modifyQuery(QueryBuilder $qb, array $arguments = [])
    {
        $column = $arguments[0] ?? 'contactDetails';

        if (!str_contains((string) $column, '.')) {
            $column = $qb->getRootAliases()[0] . '.' . $column;
        }

        $this->with->modifyQuery($qb, [$column, 'c']);
        $this->with->modifyQuery($qb, ['c.person', 'p']);
        $this->with->modifyQuery($qb, ['c.address', 'a']);
        $this->with->modifyQuery($qb, ['c.contactType', 'ct']);
        $this->with->modifyQuery($qb, ['c.phoneContacts', 'pc']);
    }
}
