<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Surrender;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Entity\System\FeatureToggle;
use Dvsa\Olcs\Api\Domain\ToggleAwareTrait;
use Dvsa\Olcs\Api\Domain\ToggleRequiredInterface;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\Query;

/**
 * Class GetSignature
 *
 * @package Dvsa\Olcs\Api\Domain\QueryHandler\Surrender
 */
final class GetSignature extends AbstractQueryHandler implements ToggleRequiredInterface
{
    use ToggleAwareTrait;

    protected $toggleConfig = [FeatureToggle::BACKEND_SURRENDER];
    protected $repoServiceName = 'Surrender';

    /**
     * handleQuery
     *
     * @param QueryInterface $query
     *
     * @return array
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleQuery(QueryInterface $query)
    {
        $licenceId = $query->getId();
        $surrender = $this->getRepo()->fetchOneByLicence($licenceId, Query::HYDRATE_OBJECT);
        return $this->result(
            $surrender,
            ['signatureType', 'digitalSignature', 'licence']
        );
    }
}
