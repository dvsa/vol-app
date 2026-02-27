<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\CommandHandler\Traits\PermitEmailTrait;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Psr\Container\ContainerInterface;
use Laminas\I18n\Translator\Translator;

/**
 * Send ECMT Annual APSG post scoring notification
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class SendEcmtApsgPostScoring extends AbstractEcmtShortTermEmailHandler
{
    use PermitEmailTrait;

    protected $repoServiceName = 'IrhpApplication';
    protected $template = 'ecmt-apsg-post-scoring-notification';

    protected $subject = 'email.ecmt.post.scoring.subject';

    /** @var Translator */
    private $translator;

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

        $euro5PermitsRequired = $irhpPermitApplication->getRequiredEuro5();
        $euro6PermitsRequired = $irhpPermitApplication->getRequiredEuro6();
        $euro5PermitsGranted = $irhpPermitApplication->countPermitsAwarded(RefData::EMISSIONS_CATEGORY_EURO5_REF);
        $euro6PermitsGranted = $irhpPermitApplication->countPermitsAwarded(RefData::EMISSIONS_CATEGORY_EURO6_REF);

        $previousLocale = $this->translator->getLocale();

        $locale = $this->getTranslateToWelsh($recordObject) == 'Y' ? 'cy_GB' : 'en_GB';
        $this->translator->setLocale($locale);

        $periodName = $this->translator->translate(
            $recordObject->getAssociatedStock()->getPeriodNameKey()
        );

        $this->translator->setLocale($previousLocale);

        return [
            'applicationRef' => $recordObject->getApplicationRef(),
            'euro5PermitsRequired' => $euro5PermitsRequired,
            'euro6PermitsRequired' => $euro6PermitsRequired,
            'euro5PermitsGranted' => $euro5PermitsGranted,
            'euro6PermitsGranted' => $euro6PermitsGranted,
            'periodName' => $periodName
        ];
    }
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->translator = $container->get('translator');
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
