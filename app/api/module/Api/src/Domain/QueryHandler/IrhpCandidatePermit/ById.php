<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\IrhpCandidatePermit;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryByIdHandler;

/**
 * Retrieve a candidate permit by id
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
final class ById extends AbstractQueryByIdHandler
{
    protected $repoServiceName = 'IrhpCandidatePermit';
    protected $bundle = ['irhpPermitRange' => ['countrys'], 'irhpPermitApplication'];
}
