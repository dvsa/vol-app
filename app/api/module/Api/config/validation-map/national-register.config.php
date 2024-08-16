<?php

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers as Handler;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

return [
    // Queries
    QueryHandler\Nr\ReputeUrl::class => Misc\IsInternalUser::class,

    /**
     * This is for ATOS to call when they verify whether a licence exists prior to sending erru requests
     */
    QueryHandler\Licence\Exists::class => Misc\IsAnonymousUser::class,

    /**
     * This is incoming xml from ATOS. The xml will have been validated on the transfer side using Laminas\Xml/Security.
     * The schema and other data is validated by national register itself \Dvsa\Olcs\Api\Service\Nr\InputFilter
     */
    CommandHandler\Cases\Si\ComplianceEpisodeDocument::class => Misc\IsAnonymousUser::class,

    /**
     * Create response xml and send to ATOS
     */
    CommandHandler\Cases\Si\CreateResponse::class => Misc\IsInternalUser::class
];
