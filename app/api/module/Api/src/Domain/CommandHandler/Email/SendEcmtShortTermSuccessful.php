<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\CommandHandler\Traits\PermitEmailTrait;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Permits\Fees\DaysToPayIssueFeeProvider;
use Psr\Container\ContainerInterface;
use Laminas\I18n\Translator\Translator;

/**
 * Send confirmation of ECMT short term app being successful
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class SendEcmtShortTermSuccessful extends AbstractEcmtShortTermEmailHandler
{
    use PermitEmailTrait;

    protected $repoServiceName = 'IrhpApplication';
    protected $template = 'ecmt-short-term-app-successful';
    protected $subject = 'email.ecmt.short.term.response.subject';

    /** @var Translator */
    private $translator;

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
        $this->getRepo()->refresh($recordObject);

        $irhpPermitApplication = $recordObject->getFirstIrhpPermitApplication();
        $irhpPermitStock = $irhpPermitApplication->getIrhpPermitWindow()->getIrhpPermitStock();

        $euro5PermitsGranted = $irhpPermitApplication->countPermitsAwarded(RefData::EMISSIONS_CATEGORY_EURO5_REF);
        $euro6PermitsGranted = $irhpPermitApplication->countPermitsAwarded(RefData::EMISSIONS_CATEGORY_EURO6_REF);

        $issueFee = $recordObject->getLatestIssueFee();
        $invoicedDateTime = $issueFee->getInvoicedDateTime();
        $irhpApplicationId = $recordObject->getId();

        $previousLocale = $this->translator->getLocale();

        $locale = $this->getTranslateToWelsh($recordObject) == 'Y' ? 'cy_GB' : 'en_GB';
        $this->translator->setLocale($locale);

        $periodName = $this->translator->translate(
            $irhpPermitStock->getPeriodNameKey()
        );

        $this->translator->setLocale($previousLocale);

        $daysToPayIssueFee = $this->daysToPayIssueFeeProvider->getDays();

        return [
            'applicationRef' => $recordObject->getApplicationRef(),
            'euro5PermitsGranted' => $euro5PermitsGranted,
            'euro6PermitsGranted' => $euro6PermitsGranted,
            'issueFeeAmount' => $this->formatCurrency($issueFee->getFeeTypeAmount()),
            'issueFeeTotal' => $this->formatCurrency($issueFee->getOutstandingAmount()),
            'paymentDeadlineNumDays' => $daysToPayIssueFee,
            'issueFeeDeadlineDate' => $this->calculateDueDate($invoicedDateTime, $daysToPayIssueFee),
            'paymentUrl' => 'http://selfserve/permits/application/' . $irhpApplicationId . '/awaiting-fee',
            'periodName' => $periodName
        ];
    }
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $this->translator = $container->get('translator');
        $this->daysToPayIssueFeeProvider = $container->get('PermitsFeesDaysToPayIssueFeeProvider');
        return parent::__invoke($container, $requestedName, $options);
    }
}
