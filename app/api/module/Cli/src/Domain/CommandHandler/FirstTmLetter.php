<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cli\Domain\CommandHandler;

use DateTime;
use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore;
use Dvsa\Olcs\Api\Domain\Repository\Licence;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Api\Entity\System\SystemParameter;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Transfer\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerLicence as TmlEntity;
use Dvsa\Olcs\Api\Domain\Repository\TransportManagerLicence;
use Dvsa\Olcs\Api\Domain\EmailAwareInterface;
use Dvsa\Olcs\Api\Domain\EmailAwareTrait;
use Dvsa\Olcs\Email\Data\Message;

final class FirstTmLetter extends AbstractCommandHandler implements EmailAwareInterface
{
    use EmailAwareTrait;

    protected const GB_GV_TEMPLATE = [
        'identifier' => 'GV_loss_of_TM_1st_letter',
        'id' => 1285
    ];
    protected const GB_PSV_TEMPLATE = [
        'identifier' => 'PSV_loss_of_TM_1st_letter',
        'id' => 1286
    ];
    protected const NI_GV_TEMPLATE = [
        'identifier' => 'GV_loss_of_TM_1st_letter_NI',
        'id' => 1284
    ];

    /**
     * @var string
     */
    protected $repoServiceName = 'Licence';

    /**
     * @var array
     */
    protected $extraRepos = ['User', 'Document', 'DocTemplate', 'TransportManagerLicence', 'SystemParameter'];

    /**
     * Handle command
     *
     * @param \Dvsa\Olcs\Cli\Domain\Command\FirstTmLetter $command Command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     * @throws \Exception
     */
    public function handleCommand(CommandInterface $command): \Dvsa\Olcs\Api\Domain\Command\Result
    {
        /** @var Licence $licenceRepo */
        $licenceRepo = $this->getRepo();
        $eligibleLicences = $licenceRepo->fetchForLastTmAutoLetter($licenceRepo::LETTER_FIRST);

        /** @var LicenceEntity $licence */
        foreach ($eligibleLicences as $licence) {
            /** @var TransportManagerLicence $tmlRepo */
            $tmlRepo = $this->getRepo('TransportManagerLicence');
            $removedTms = $tmlRepo->fetchRemovedTmForLicence($licence->getId());

            /** @var TmlEntity $removedTm */
            foreach ($removedTms as $removedTm) {
                $document = $this->generateDocuments($licence, $removedTm);
                $removedTm->setLastTmFirstEmailDate(new DateTime());
                $tmlRepo->save($removedTm);
            }      

            if (!empty($removedTms)) {
                $this->sendEmailToOperator($licence);
            }
        }

        return $this->result;
    }

    /**
     * Sends an email notification to the operator.
     */
    private function sendEmailToOperator(LicenceEntity $licence): void
    {
        if (
            is_null($contactDetails = $licence->getCorrespondenceCd()) ||
            is_null($email = $contactDetails->getEmailAddress())
        ) {
            return;
        }

        $registeredToSelfserve = false;
        $translateToWelsh = $licence->getTranslateToWelsh();

        /** @var \Dvsa\Olcs\Api\Domain\Repository\User $userRepo */
        $userRepo = $this->getRepo('User');

        /** @var User $user */
        $user = $userRepo->fetchFirstByEmailOrFalse($email);
        if ($user !== false) {
            $registeredToSelfserve = true;
            $translateToWelsh = $user->getTranslateToWelsh();
        }

        $message = new Message($email, 'email.last-tm-operator-notification.subject');
        $message->setTranslateToWelsh($translateToWelsh);
        $message->setHighPriority();

        $this->sendEmailTemplate(
            $message,
            'licensing-information-standard',
            [
                'licNo' => $licence->getLicNo(),
                'operatorName' => $licence->getOrganisation()->getName(),
                    // @NOTE the http://selfserve part gets replaced
                    'url' => 'http://selfserve/correspondence'
            ]
        );
    }

    /**
     * Generates documents for the given licence and TML entity.
     */
    private function generateDocuments(LicenceEntity $licence, TmlEntity $tml): ?int
    {
        $template = $this->selectTemplate($licence);

        $caseworkerDetailsBundle = [
            'contactDetails' => [
                'address',
                'phoneContacts' => [
                    'phoneContactType'
                ],
                'person'
            ],
            'team' => [
                'trafficArea' => [
                    'contactDetails' => [
                        'address'
                    ]
                ]
            ]
        ];

        $caseworkerNameBundle = [
            'contactDetails' => [
                'person'
            ]
        ];

        $licenceBundle = [
            'trafficArea',
        ];

        $createTaskResult = $this->handleSideEffect($this->createTaskSideEffect($licence));
        $this->result->merge($createTaskResult);

        $userRepo = $this->getRepo('User');
        /** @var User $user */
        $user = $userRepo->fetchById($createTaskResult->getId('assignedToUser'));
        $contactDetails = $user->serialize($caseworkerDetailsBundle);
        $licenceDetails = $licence->serialize($licenceBundle);
        $caseworkerName = $user->serialize($caseworkerNameBundle);
        $caseworkerDetails = [
            $contactDetails,
            $licenceDetails
        ];

        $generateCommandData = [
            'template' => $template['identifier'],
            'query' => [
                'licence' => $licence->getId(),
                'transportManager' => $tml->getTransportManager()->getId(),
                'transportManagerLicence' => $tml->getId()
            ],
            'description' => 'Last TM letter Licence ' . $licence->getLicNo() . ' 1st letter',
            'licence' => $licence->getId(),
            'category' => Category::CATEGORY_TRANSPORT_MANAGER,
            'subCategory' => Category::DOC_SUB_CATEGORY_TRANSPORT_MANAGER_CORRESPONDENCE,
            'isExternal' => false,
            'dispatch' => true,
            'metadata' => json_encode([
                'details' => [
                    'category' => Category::CATEGORY_TRANSPORT_MANAGER,
                    'documentSubCategory' => Category::DOC_SUB_CATEGORY_TRANSPORT_MANAGER_CORRESPONDENCE,
                    'documentTemplate' => $template['id'],
                    'allowEmail' => $licence->getOrganisation()->getAllowEmail()
                ]
            ]),
            'knownValues' => [
                'caseworker_details' => $caseworkerDetails,
                'caseworker_name' => $caseworkerName
            ]
        ];

        $result = $this->handleSideEffect(GenerateAndStore::create($generateCommandData));
        $this->result->merge($result);

        return $result->getId('document');
    }

    /**
     * Select template based on licence information
     */
    private function selectTemplate(LicenceEntity $licence): array
    {
        $template = self::GB_GV_TEMPLATE;

        if ($licence->isNi()) {
            $template = self::NI_GV_TEMPLATE;
        } elseif ($licence->isPsv()) {
            $template = self::GB_PSV_TEMPLATE;
        }

        return $template;
    }

    /**
     * Creates a task side-effect for the given licence.
     */
    private function createTaskSideEffect($licence): CreateTask
    {
        $params = [
            'category' => Category::CATEGORY_APPLICATION,
            'subCategory' => SubCategory::TM_SUB_CATEGORY_TM1_REMOVAL,
            'description' => TmlEntity::DESC_TM_REMOVED_LAST_RESPONSE,
            'actionDate' => (new DateTime())->add(new \DateInterval('P21D'))->format('Y-m-d'),
            'licence' => $licence->getId(),
            'urgent' => 'Y'
        ];

        $sysParamRepo = $this->getRepo('SystemParameter');
        $assignToUserId = $licence->isNi()
            ? $sysParamRepo->fetchValue(SystemParameter::LAST_TM_1ST_LETTER_NI_TASK_OWNER)
            : $sysParamRepo->fetchValue(SystemParameter::LAST_TM_1ST_LETTER_GB_TASK_OWNER);
        if ($assignToUserId && $assignToUserId != 0) {
            $params['assignedToUser'] = $assignToUserId;
        }
        return CreateTask::create($params);
    }
}
