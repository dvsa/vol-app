<?php

/**
 * User Selfserve
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\User;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * User Selfserve
 */
class UserSelfserve extends AbstractQueryHandler
{
    protected $repoServiceName = 'User';

    /**
     * Handle query
     *
     * @param QueryInterface $query query
     *
     * @return \Dvsa\Olcs\Api\Domain\QueryHandler\Result
     */
    public function handleQuery(QueryInterface $query)
    {
        $user = $this->getRepo()->fetchUsingId($query);

        return $this->result(
            $user,
            [
                'transportManager',
                'localAuthority',
                'partnerContactDetails',
                'roles',
                'contactDetails' => [
                    'person',
                    'address' => ['countryCode'],
                    'phoneContacts' => ['phoneContactType']
                ],
                'organisationUsers' => [
                    'organisation',
                ],
            ],
            [
                'permission' => $user->getPermission()
            ]
        );
    }
}
