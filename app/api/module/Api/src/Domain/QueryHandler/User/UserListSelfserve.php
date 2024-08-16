<?php

/**
 * User List Selfserve
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\User;

use Dvsa\Olcs\Api\Domain\Exception\BadRequestException;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\Query\User\UserListSelfserve as ListDto;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\Query;

/**
 * User List Selfserve
 */
class UserListSelfserve extends AbstractQueryHandler
{
    protected $repoServiceName = 'User';

    /**
     * Handle query
     *
     * @param QueryInterface $query query
     *
     * @return \Dvsa\Olcs\Api\Domain\QueryHandler\Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\BadRequestException
     */
    public function handleQuery(QueryInterface $query)
    {
        $repo = $this->getRepo();

        // get data from transfer query
        $data = $query->getArrayCopy();

        // add extra conditions based on who you are
        switch ($this->getCurrentUser()->getUserType()) {
            case UserEntity::USER_TYPE_PARTNER:
                $data['partnerContactDetails'] = $this->getCurrentUser()->getPartnerContactDetails()->getId();
                break;
            case UserEntity::USER_TYPE_LOCAL_AUTHORITY:
                $data['localAuthority'] = $this->getCurrentUser()->getLocalAuthority()->getId();
                break;
            case UserEntity::USER_TYPE_OPERATOR:
            case UserEntity::USER_TYPE_TRANSPORT_MANAGER:
                $data['organisation']
                    = $this->getCurrentUser()->getOrganisationUsers()->first()->getOrganisation()->getId();
                break;
            default:
                // only available to specific user types
                throw new BadRequestException('User type must be provided');
        }

        if (empty($data['partnerContactDetails']) && empty($data['localAuthority']) && empty($data['organisation'])) {
            // make sure that user type filter has been set
            throw new BadRequestException('User type must be provided');
        }

        // create new query with extra data
        $listDto = ListDto::create($data);

        return [
            'result' => $this->resultList(
                $repo->fetchList($listDto, Query::HYDRATE_OBJECT),
                [
                    'contactDetails' => ['person'],
                    'roles'
                ]
            ),
            'count' => $repo->fetchCount($listDto)
        ];
    }
}
