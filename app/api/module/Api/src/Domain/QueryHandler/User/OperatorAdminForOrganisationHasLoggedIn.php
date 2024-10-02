<?php declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\QueryHandler\User;

use DateTimeImmutable;
use Doctrine\ORM\AbstractQuery;
use Dvsa\Olcs\Api\Domain\Exception\BadRequestException;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Domain\Query\User\UserListSelfserve as ListDto;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\QueryHandler\Result;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Transfer\Query\User\OperatorAdminForOrganisationHasLoggedIn as Qry;

/**
 * Returns true if an operator admin for the organisation has logged in
 */
class OperatorAdminForOrganisationHasLoggedIn extends AbstractQueryHandler
{
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
            'limit' => 100,
            'sort' => 'id',
            'order' => 'DESC',
        ];

        $lastLoginDate = DateTimeImmutable::createFromFormat("Y-m-d", '1970-01-01');
        if (!empty($query->getLastLoggedInFrom())) {
            $lastLoginDate = DateTimeImmutable::createFromFormat("Y-m-d", $query->getLastLoggedInFrom());
        }

        $params['lastLoggedInFrom'] = $lastLoginDate;

        $userListDto = ListDto::create($params);

        $result = [
            'organisation' => (int) $query->getOrganisation(),
            'lastLoggedInFrom' => $lastLoginDate->format('Y-m-d'),
            'operatorAdminHasLoggedIn' => false,
        ];

        if ($repo->fetchCount($userListDto) > 0) {
            $result['operatorAdminHasLoggedIn'] = true;
        }

        return $result;
    }
}
