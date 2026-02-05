<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\RepositoryServiceManager;
use Dvsa\Olcs\Api\Entity\Bus\LocalAuthority as LocalAuthorityEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails as ContactDetailsEntity;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager as TransportManagerEntity;
use Dvsa\Olcs\Api\Entity\User\Role as RoleEntity;
use Dvsa\Olcs\Api\Entity\User\Team as TeamEntity;
use Dvsa\Olcs\Api\Entity\User\User as Entity;
use Dvsa\Olcs\Api\Rbac\IdentityProviderInterface;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

class User extends AbstractRepository
{
    public const USERNAME_GEN_TRY_COUNT = 100;

    protected $entity = Entity::class;
    protected $alias = 'u';

    /**
     * @var Role
     */
    protected $roleRepo;

    /**
     * Called from the factory to allow additional services to be injected
     *
     * @param RepositoryServiceManager $serviceManager Repository manager
     *
     * @return void
     */
    #[\Override]
    public function initService(RepositoryServiceManager $serviceManager)
    {
        $this->roleRepo = $serviceManager->get('Role');
    }

    /**
     * Build default query
     *
     * @param QueryBuilder $qb Query
     * @param int          $id Id
     *
     * @return void
     */
    #[\Override]
    protected function buildDefaultQuery(QueryBuilder $qb, $id)
    {
        parent::buildDefaultQuery($qb, $id);

        // join in person and team details
        $this->getQueryBuilder()
            ->with('team', 't')
            ->with('contactDetails', 'cd')
            ->with('cd.person', 'p');
    }

    /**
     * Build default list query
     *
     * @param QueryBuilder   $qb              Query Builder
     * @param QueryInterface $query           Query
     * @param array          $compositeFields fields
     *
     * @return void
     */
    #[\Override]
    protected function buildDefaultListQuery(QueryBuilder $qb, QueryInterface $query, $compositeFields = [])
    {
        parent::buildDefaultListQuery($qb, $query, $compositeFields);

        // join in person and team details
        $this->getQueryBuilder()
            ->with('team', 't')
            ->with('contactDetails', 'cd')
            ->with('cd.person', 'p')
            ->with('p.disqualifications', 'd');
    }

    /**
     * Apply filters
     *
     * @param QueryBuilder   $qb    Query Builder
     * @param QueryInterface $query Query
     *
     * @return void
     */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        if (method_exists($query, 'getLocalAuthority') && !empty($query->getLocalAuthority())) {
            $qb->andWhere($qb->expr()->eq($this->alias . '.localAuthority', ':localAuthority'))
                ->setParameter('localAuthority', $query->getLocalAuthority());
        }

        if (method_exists($query, 'getPartnerContactDetails') && !empty($query->getPartnerContactDetails())) {
            $qb->andWhere($qb->expr()->eq($this->alias . '.partnerContactDetails', ':partnerContactDetails'))
                ->setParameter('partnerContactDetails', $query->getPartnerContactDetails());
        }

        // filter by organisation if it has been specified
        if (method_exists($query, 'getOrganisation') && !empty($query->getOrganisation())) {
            $qb->join('u.organisationUsers', 'ou', Expr\Join::WITH, 'ou.organisation = :organisation');
            $qb->setParameter('organisation', (int)$query->getOrganisation());
        }

        // filter by team if it has been specified
        if (method_exists($query, 'getTeam') && !empty($query->getTeam())) {
            $qb->andWhere($qb->expr()->eq($this->alias . '.team', ':team'))
                ->setParameter('team', (int)$query->getTeam());
        }

        // filter users by traffic area (this is for internal users and based on their team traffic area)
        if (method_exists($query, 'getTrafficAreas')) {
            $qb->andWhere($qb->expr()->in('t.trafficArea', ':trafficAreas'))
                ->setParameter('trafficAreas', $query->getTrafficAreas());
        }

        if (method_exists($query, 'getIsInternal') && $query->getIsInternal() == true) {
            $qb->andWhere($qb->expr()->isNotNull($this->alias . '.team'));
        }

        if (method_exists($query, 'getExcludeLimitedReadOnly') && $query->getExcludeLimitedReadOnly() == true) {
            /* @var \Doctrine\Orm\QueryBuilder $roleQb */
            $roleQb = $qb->getEntityManager()->getRepository(Entity::class)->createQueryBuilder('u2');
            $roleQb->select('u2.id');
            $roleQb->leftJoin('u2.roles', 'r');
            $roleQb->andWhere($roleQb->expr()->eq('r.role', ':role'));
            $qb->setParameter('role', RoleEntity::ROLE_INTERNAL_LIMITED_READ_ONLY);

            $qb->andWhere(
                $qb->expr()
                    ->notIn(
                        $this->alias . '.id',
                        $roleQb->getDQL()
                    )
            );
        }

        // filter by role array if it has been specified
        if (method_exists($query, 'getRoles') && !empty($query->getRoles())) {
            $qb->leftJoin('u.roles', 'r');
            $qb->andWhere('r.role IN (:roles)');
            $qb->setParameter('roles', $query->getRoles());
        }

        // filter by lastLoggedInFrom if it has been specified
        if (method_exists($query, 'getLastLoggedInFrom') && !empty($query->getLastLoggedInFrom())) {
            $qb->andWhere($qb->expr()->gte($this->alias . '.lastLoginAt', ':lastLoggedInFrom'))
                ->setParameter('lastLoggedInFrom', $query->getLastLoggedInFrom());
        }

        // exclude system user from all lists
        $qb->andWhere($qb->expr()->neq($this->alias . '.id', ':systemUser'))
            ->setParameter('systemUser', IdentityProviderInterface::SYSTEM_USER);
    }

    /**
     * Fetch transport manager by user id
     *
     * @param int $userId User identifier
     *
     * @return mixed
     */
    public function fetchForTma($userId)
    {
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()->modifyQuery($qb)
            ->withRefdata()
            ->with($this->alias . '.contactDetails', 'cd')
            ->with('cd.person', 'cdp')
            ->with($this->alias . '.transportManager', 'tm')
            ->byId($userId);

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * Get a list of users by licence number and email address
     *
     * @param string $licNo        Licence number
     * @param string $emailAddress email address
     *
     * @return array
     */
    public function fetchForRemindUsername($licNo, $emailAddress)
    {
        $qb = $this->createQueryBuilder();

        $qb
            // match by email address
            ->innerJoin($this->alias . '.contactDetails', 'cd')
            ->andWhere($qb->expr()->eq('cd.emailAddress', ':emailAddress'))
            ->setParameter('emailAddress', $emailAddress)
            // match by licence number
            ->innerJoin($this->alias . '.organisationUsers', 'ou')
            ->innerJoin('ou.organisation', 'o')
            ->innerJoin('o.licences', 'l')
            ->andWhere($qb->expr()->eq('l.licNo', ':licNo'))
            ->setParameter('licNo', $licNo);

        $query = $qb->getQuery();
        $query->execute();

        return $query->getResult();
    }

    public function fetchFirstByEmailOrFalse(string $emailAddress)
    {
        $qb = $this->createQueryBuilder();

        $qb->innerJoin($this->alias . '.contactDetails', 'cd')
            ->andWhere($qb->expr()->eq('cd.emailAddress', ':emailAddress'))
            ->setParameter('emailAddress', $emailAddress)
            ->setMaxResults(1);

        $query = $qb->getQuery();
        $query->execute();

        return current($query->getResult());
    }

    /**
     * Populate Ref Data References
     *
     * @param array $data Array of data as defined by Dvsa\Olcs\Transfer\Command\User\CreateUser
     *
     * @return array
     */
    public function populateRefDataReference(array $data)
    {
        if (isset($data['team'])) {
            $data['team'] = $this->getReference(TeamEntity::class, $data['team']);
        }

        if (isset($data['transportManager'])) {
            $data['transportManager'] = $this->getReference(TransportManagerEntity::class, $data['transportManager']);
        }

        if (isset($data['partnerContactDetails'])) {
            $data['partnerContactDetails'] = $this->getReference(
                ContactDetailsEntity::class,
                $data['partnerContactDetails']
            );
        }

        if (isset($data['localAuthority'])) {
            $data['localAuthority'] = $this->getReference(LocalAuthorityEntity::class, $data['localAuthority']);
        }

        if (isset($data['roles'])) {
            $data['roles'] = array_map(
                fn($role) => $this->roleRepo->fetchOneByRole($role),
                $data['roles']
            );
        }

        return $data;
    }

    /**
     * Get count of users in team
     *
     * @param int $teamId Team indentifier
     *
     * @return int|null
     */
    public function fetchUsersCountByTeam($teamId)
    {
        $qb = $this->createQueryBuilder();
        $qb->select('count(' . $this->alias . '.id)')
            ->andWhere($qb->expr()->eq($this->alias . '.team', ':team'))
            ->setParameter('team', $teamId);

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Get by pid
     *
     * @param int $pid Pid
     *
     * @return Entity|null
     */
    public function fetchByPid($pid)
    {
        $qb = $this->createQueryBuilder();
        $this->getQueryBuilder()
            ->modifyQuery($qb)
            ->withRefdata()
            ->withPersonContactDetails()
            ->with('team')
            ->with('organisationUsers')
            ->with('roles')
            ->with('transportManager')
            ->with('localAuthority');

        $qb->where($this->alias . '.pid = :pid')->setParameter('pid', $pid);
        $qb->andWhere($this->alias . '.accountDisabled = 0');

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Check user name is available and if not, then generate new user name
     *
     * @param string        $base   User Name to check and use as base
     * @param callable|null $fncSfx Function to change user name
     * @param int           $tryCnt Count of try to generate new user name
     *
     * @return null|string
     */
    public function findUserNameAvailable($base, callable $fncSfx = null, $tryCnt = self::USERNAME_GEN_TRY_COUNT)
    {
        if ($fncSfx === null) {
            $fncSfx = fn($base, $idx) => $base . ($idx > 0 ? (string)$idx : '');
        }

        $this->disableSoftDeleteable();

        $idx = 0;
        $user = $base;

        while (($isExist = (count($this->fetchByLoginId($user)) !== 0)) && ++$idx < $tryCnt) {
            $user = $fncSfx($base, $idx);
        }

        $this->enableSoftDeleteable();

        return !$isExist ? $user : null;
    }

    /**
     * Fetch by UserName|Login
     *
     * @param string $login UserName|Login
     *
     * @return array
     */
    public function fetchByLoginId($login)
    {
        return $this->fetchByX('loginId', [$login]);
    }

    public function fetchEnabledIdentityByLoginId(string $loginId)
    {
        $qb = $this->createQueryBuilder();
        $this->getQueryBuilder()
            ->modifyQuery($qb)
            ->withRefdata()
            ->withPersonContactDetails()
            ->with('team')
            ->with('organisationUsers')
            ->with('roles')
            ->with('transportManager')
            ->with('localAuthority');

        $qb->where($this->alias . '.loginId = :loginId')->setParameter('loginId', $loginId);
        $qb->andWhere($this->alias . '.accountDisabled = 0');

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Get count of users in role
     *
     * @param string $role Role
     *
     * @return int
     */
    public function fetchUsersCountByRole($role)
    {
        $qb = $this->createQueryBuilder();
        $qb->select('COUNT(DISTINCT ' . $this->alias . '.id)')
            ->innerJoin($this->alias . '.roles', 'r')
            ->andWhere($qb->expr()->eq('r.role', ':role'))
            ->setParameter('role', $role);

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Get count of users with lastLoginAt column set to null
     *
     * @return int
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function fetchUsersCountWithoutLastLoginTime(): int
    {
        $qb = $this->createQueryBuilder();
        $qb->select('COUNT(DISTINCT ' . $this->alias . '.id)')
            ->andWhere($qb->expr()->isNull($this->alias . '.deletedDate'))
            ->andWhere($qb->expr()->isNull($this->alias . '.lastLoginAt'));

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Get a paginated list of users with lastLoginAt column set to null
     *
     * @return \Iterator
     */
    public function fetchUsersWithoutLastLoginTime(): \Iterator
    {
        $qb = $this->createQueryBuilder();

        $qb->andWhere($qb->expr()->isNull($this->alias . '.deletedDate'));
        $qb->andWhere($qb->expr()->isNull($this->alias . '.lastLoginAt'));

        return $qb->getQuery()->iterate();
    }

    public function updateLastLogin(Entity $user, DateTime $lastLoginAt, Entity $lastModifiedBy): void
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->update(Entity::class, 'u')
           ->set('u.lastLoginAt', ':lastLoginAt')
           ->set('u.lastModifiedOn', ':lastModifiedOn')
           ->set('u.lastModifiedBy', ':lastModifiedBy')
           ->set('u.version', 'u.version + 1')
           ->where('u.id = :id')
           ->setParameter('id', $user->getId())
           ->setParameter('lastLoginAt', $lastLoginAt)
           ->setParameter('lastModifiedBy', $lastModifiedBy->getId())
           ->setParameter('lastModifiedOn', new DateTime());

        $qb->getQuery()->execute();

        $this->refresh($user);
    }
}
