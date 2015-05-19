<?php

/**
 * Enqueue Companies House organisation lookups
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Cli\Service\Processing;

use Common\Service\Entity\LicenceStatusRuleEntityService;
use Common\Service\Entity\LicenceEntityService;
use Common\Util\RestCallTrait;
use Zend\Log\Logger;
use Common\BusinessService\Response;

/**
 * Enqueue Companies House organisation lookups
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class CompaniesHouseEnqueueOrganisations extends AbstractBatchProcessingService
{
    use RestCallTrait;

    /**
     * @param string $type queue message type
     * @return int
     */
    public function process($type)
    {
        $result = $this->makeRestCall('CompaniesHouseQueue', 'POST', ['type' => $type]);

        $this->outputLine('Enqueued '.$result.' messages');

        return self::EXIT_CODE_SUCCESS;
    }
}
