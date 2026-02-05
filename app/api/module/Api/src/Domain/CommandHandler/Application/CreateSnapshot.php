<?php

/**
 * Create Snapshot
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\GeneratorFactory;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Transfer\Command\Document\Upload;
use Psr\Container\ContainerInterface;
use Dvsa\Olcs\Transfer\Command\Application\CreateSnapshot as Cmd;

/**
 * Create Snapshot
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class CreateSnapshot extends AbstractCommandHandler implements AuthAwareInterface
{
    use AuthAwareTrait;

    public const string CODE_GV_APP             = 'GV79';
    public const string CODE_GV_VAR_UPGRADE     = 'GV80A';
    public const string CODE_GV_VAR_NO_UPGRADE  = 'GV81';

    public const string CODE_PSV_APP = 'PSV421';
    public const string CODE_PSV_APP_SR = 'PSV356';
    public const string CODE_PSV_VAR_UPGRADE    = 'PSV431A';
    public const string CODE_PSV_VAR_NO_UPGRADE = 'PSV431';

    protected $repoServiceName = 'Application';

    /**
     * @var GeneratorFactory
     */
    private $reviewSnapshotService;

    public function handleCommand(CommandInterface $command)
    {
        /** @var ApplicationEntity $application */
        $application = $this->getRepo()->fetchUsingId($command);

        $markup = $this->reviewSnapshotService->generate($application, $this->isInternalUser());
        $this->result->addMessage('Snapshot generated');

        $this->result->merge($this->generateDocument($markup, $application, $command->getEvent()));

        return $this->result;
    }

    protected function generateDocument($content, ApplicationEntity $application, $event)
    {
        $licenceId = $application->getLicence()->getId();
        $code = $this->getDocumentCode($application);

        $data = [
            'content' => base64_encode(trim((string) $content)),
            'application' => $application->getId(),
            'licence' => $licenceId,
            'category' => Category::CATEGORY_APPLICATION,
            'subCategory' => Category::TASK_SUB_CATEGORY_APPLICATION_FORMS_ASSISTED_DIGITAL,
            'isExternal' => false,
            'isScan' => false
        ];

        $data = array_merge($data, $this->getDocumentData($application, $event, $code));

        return $this->handleSideEffect(Upload::create($data));
    }

    /**
     * Get Application code
     *
     * @param int $applicationId   Application ID
     * @param array $applicationData
     *
     * @return string Eg GV80A
     */
    protected function getDocumentCode(ApplicationEntity $application)
    {
        // All New application options
        if (!$application->isVariation()) {
            if ($application->isGoods()) {
                return self::CODE_GV_APP;
            }

            if ($application->isSpecialRestricted()) {
                return self::CODE_PSV_APP_SR;
            }

            return self::CODE_PSV_APP;
        }

        $isUpgrade = $application->isRealUpgrade();

        if ($application->isGoods()) {
            if ($isUpgrade) {
                return self::CODE_GV_VAR_UPGRADE;
            }

            return self::CODE_GV_VAR_NO_UPGRADE;
        } else {
            if ($isUpgrade) {
                return self::CODE_PSV_VAR_UPGRADE;
            }

            return self::CODE_PSV_VAR_NO_UPGRADE;
        }
    }

    /**
     * Get Document entity data
     *
     * @param ApplicationEntity $application The Application entity
     * @param int               $event       One the of the self::ON_* constants
     * @param string            $code        Application code eg GV79
     *
     * @return array Document entity data
     */
    protected function getDocumentData($application, $event, $code)
    {
        $descriptionPrefix = sprintf('%s Application %d Snapshot ', $code, $application->getId());

        return match ((string)$event) {
            (string)Cmd::ON_GRANT => [
                'filename' => $descriptionPrefix . 'Grant.html',
                'description' => $descriptionPrefix . '(at grant/valid)',
            ],
            (string)Cmd::ON_SUBMIT => [
                'subCategory' => Category::TASK_SUB_CATEGORY_APPLICATION_FORMS_DIGITAL,
                'filename' => $descriptionPrefix . 'Submit.html',
                'description' => $descriptionPrefix . '(at submission)',
                'isExternal' => true,
            ],
            (string)Cmd::ON_REFUSE => [
                'filename' => $descriptionPrefix . 'Refuse.html',
                'description' => $descriptionPrefix . '(at refuse)',
            ],
            (string)Cmd::ON_WITHDRAW => [
                'filename' => $descriptionPrefix . 'Withdraw.html',
                'description' => $descriptionPrefix . '(at withdraw)',
            ],
            (string)Cmd::ON_NTU => [
                'filename' => $descriptionPrefix . 'NTU.html',
                'description' => $descriptionPrefix . '(at NTU)',
            ],
            default => throw new ValidationException(['Unexpected event']),
        };
    }
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->reviewSnapshotService = $container->get('ReviewSnapshot');
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
