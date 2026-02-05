<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\CommandHandler\Traits\PermitEmailTrait;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Service\Permits\Fees\DaysToPayIssueFeeProvider;
use Psr\Container\ContainerInterface;

/**
 * Send confirmation of ECMT short term app being automatically withdrawn
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class SendEcmtShortTermAutomaticallyWithdrawn extends AbstractEmailHandler
{
    use PermitEmailTrait;

    protected $repoServiceName = 'IrhpApplication';
    protected $template = 'ecmt-short-term-automatically-withdrawn';
    protected $subject = 'email.ecmt.short.term.automatically.withdrawn.subject';

    /** @var DaysToPayIssueFeeProvider */
    private $daysToPayIssueFeeProvider;

    /**
     * Get template variables
     *
     * @param IrhpApplication $recordObject
     *
     * @return array
     */
    #[\Override]
    protected function getTemplateVariables($recordObject): array
    {
        return [
            'applicationRef' => $recordObject->getApplicationRef(),
            'paymentDeadlineNumDays' => $this->daysToPayIssueFeeProvider->getDays(),
        ];
    }
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $this->daysToPayIssueFeeProvider = $container->get('PermitsFeesDaysToPayIssueFeeProvider');
        return parent::__invoke($container, $requestedName, $options);
    }
}
