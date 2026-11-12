<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Application;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryByIdHandler;
use Dvsa\Olcs\Api\Service\Sms\SmsNumberCapture;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Psr\Container\ContainerInterface;

/**
 * Retrieve an application alongside the completion status
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class Completion extends AbstractQueryByIdHandler
{
    protected $repoServiceName = 'Application';
    protected $bundle = ['applicationCompletion'];

    private SmsNumberCapture $smsNumberCapture;

    #[\Override]
    public function handleQuery(QueryInterface $query)
    {
        $application = $this->getRepo()->fetchUsingId($query);

        // Addresses done before sms_number_capture went on is shown as not done.
        return $this->smsNumberCapture->markStaleAddressesIncomplete(
            $this->result($application, $this->bundle, $this->values),
            $application,
            $this->isExternalUser(...)
        );
    }

    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $this->smsNumberCapture = $container->get(SmsNumberCapture::class);

        return parent::__invoke($container, $requestedName, $options);
    }
}
