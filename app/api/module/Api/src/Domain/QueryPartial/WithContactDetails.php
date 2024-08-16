<?php

/**
 * With Contact Details
 */

namespace Dvsa\Olcs\Api\Domain\QueryPartial;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\ContactDetails\Address;
use Dvsa\Olcs\Api\Entity\ContactDetails\PhoneContact;

/**
 * With Contact Details
 */
final readonly class WithContactDetails implements QueryPartialInterface
{
    public function __construct(private EntityManagerInterface $em, private With $with, private WithRefdata $withRefdata)
    {
    }

    /**
     * Grabs a contact details relationship with address and refdata
     * $property is the name of the ContactDetails property as references from it's parent entity e.g. m.contactDetails
     * $alias is the alias to give the contact details object
     *
     * @param QueryBuilder $qb
     * @param array $arguments
     */
    public function modifyQuery(QueryBuilder $qb, array $arguments = [])
    {
        switch (count($arguments)) {
            case 2:
                [$property, $alias] = $arguments;
                break;
            case 1:
                $property = $arguments[0];
                $alias = $qb->getRootAliases()[0] . '_cd';
                break;
            default:
                $rootAlias = $qb->getRootAliases()[0];

                $property = $rootAlias . '.contactDetails';
                $alias = $rootAlias . '_cd';
                break;
        }

        if (!strstr((string) $property, '.')) {
            $property = $qb->getRootAliases()[0] . '.' . $property;
        }

        $this->with->modifyQuery($qb, [$property, $alias]);
        $this->with->modifyQuery($qb, [$alias . '.address', $alias . '_a']);
        $this->with->modifyQuery($qb, [$alias . '_a.countryCode', $alias . '_a_cc']);
        $this->with->modifyQuery($qb, [$alias . '.phoneContacts', $alias . '_pc']);

        $this->withRefdata->modifyQuery($qb, [ContactDetails::class, $alias]);
        $this->withRefdata->modifyQuery($qb, [Address::class, $alias . '_a']);
        $this->withRefdata->modifyQuery($qb, [PhoneContact::class, $alias . '_pc']);
    }
}
