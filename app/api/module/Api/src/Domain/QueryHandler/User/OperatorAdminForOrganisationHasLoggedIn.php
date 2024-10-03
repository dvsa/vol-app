<?php declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\QueryHandler\User;

use DateTimeImmutable;
use Dvsa\Olcs\Api\Domain\Exception\BadRequestException;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Domain\Query\User\UserListSelfserve as ListDto;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Transfer\Query\User\OperatorAdminForOrganisationHasLoggedIn as Qry;

class OperatorAdminForOrganisationHasLoggedIn extends AbstractQueryHandler
{
    public const DEFAULT_LAST_LOGGED_IN_FROM = '1970-01-01';

    protected $repoServiceName = Repository\User::class;

    /**
     * Handle query
     *
     * @param QueryInterface $query query
     *
     * @throws BadRequestException|RuntimeException
     */
    public function handleQuery(QueryInterface $query): array
    {
        if (!$query instanceof Qry) {
            throw new BadRequestException('Expected instance of: ' . Qry::class);
        }

        if (empty($query->getOrganisation())) {
            throw new BadRequestException('Organisation ID is required');
        }

        $repo = $this->getRepo(Repository\User::class);

        $params = [
            'organisation' => $query->getOrganisation(),
            'roles' => [Entity\User\Role::ROLE_OPERATOR_ADMIN],
            'page' => 1,
            'limit' => 1,
            'sort' => 'id',
            'order' => 'DESC',
        ];

        $lastLoginDate = static::DEFAULT_LAST_LOGGED_IN_FROM;
        if (!empty($query->getLastLoggedInFrom())) {
            $lastLoginDate = $query->getLastLoggedInFrom();
        }

        $params['lastLoggedInFrom'] = $lastLoginDate;

        $userListDto = ListDto::create($params);

        $result = [
            'organisation' => (int) $query->getOrganisation(),
            'lastLoggedInFrom' => $lastLoginDate,
            'operatorAdminHasLoggedIn' => false,
        ];

        if ($repo->fetchCount($userListDto) > 0) {
            $result['operatorAdminHasLoggedIn'] = true;
        }

        return $result;
    }
}
