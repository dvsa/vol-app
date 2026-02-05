<?php

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
use Dvsa\Olcs\Transfer\Command\Document\PrintLetter;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Transfer\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerLicence as TmlEntity;
use Dvsa\Olcs\Api\Domain\Repository\TransportManagerLicence;
use Dvsa\Olcs\Api\Domain\EmailAwareInterface;
use Dvsa\Olcs\Api\Domain\EmailAwareTrait;
use Dvsa\Olcs\Email\Data\Message;

final class LastTmLetter extends AbstractCommandHandler implements EmailAwareInterface
{
    use EmailAwareTrait;

    protected const array GB_GV_TEMPLATE = [
        'identifier' => 'GV_letter_to_op_regarding_no_TM_specified',
        'id' => 919
    ];
    protected const array GB_PSV_TEMPLATE = [
        'identifier' => 'PSV_letter_to_op_regarding_no_TM_specified',
        'id' => 920
    ];
    protected const array NI_GV_TEMPLATE = [
        'identifier' => 'GV_letter_to_op_regarding_no_TM_specified_NI',
        'id' => 918
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
     * @param \Dvsa\Olcs\Cli\Domain\Command\DataGovUkExport $command Command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     * @throws \Exception
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var Licence $licenceRepo */
        $licenceRepo = $this->getRepo();
        $eligibleLicences = $licenceRepo->fetchForLastTmAutoLetter();

        /** @var LicenceEntity $licence */
        foreach ($eligibleLicences as $licence) {
            $document = $this->generateDocuments($licence);
            $this->printAndEmailDocument($document);
            $this->updateLastTmLetterDate($licence);
            $this->sendEmailToOperator($licence);
        }

        return $this->result;
    }

    /**
     * @return void
     */
    private function sendEmailToOperator(LicenceEntity $licence)
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
        $message->setDocs([$this->result->getId('document')]);

        $this->sendEmailTemplate(
            $message,
            'last-tm-operator-notification',
            [
                'licNo' => $licence->getLicNo(),
                'registeredToSelfserve' => $registeredToSelfserve,
            ]
        );
    }

    /**
     * @return array|null
     */
    private function generateDocuments(LicenceEntity $licence)
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
            ],
            'description' => 'Last TM letter Licence ' . $licence->getLicNo(),
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
     * @param int $document
     * @return void
     */
    private function printAndEmailDocument($document)
    {
        $this->printDocument($document);
        $this->maybeEmailDocument($document);
    }

    /**
     * @param int $document (id)
     * @return void
     */
    private function printDocument($document)
    {
        $result = $this->handleSideEffect(
            PrintLetter::create([
                'id' => $document,
                'method' => PrintLetter::METHOD_PRINT_AND_POST
            ])
        );

        $this->result->merge($result);
    }

    /**
     * @param int $document (id)
     * @return void
     */
    private function maybeEmailDocument($document)
    {
        $documentRepo = $this->getRepo('Document');
        /** @var Document $docEntity */
        $docEntity = $documentRepo->fetchById($document);
        $metadata = json_decode($docEntity->getMetadata(), true);
        if ($this->shouldSendEmail($metadata)) {
            $result = $this->handleSideEffect(
                PrintLetter::create([
                    'id' => $document,
                    'method' => PrintLetter::METHOD_EMAIL,
                    'forceCorrespondence' => true
                ])
            );
            $this->result->merge($result);
        }
    }

    /**
     * @return array
     */
    private function selectTemplate(LicenceEntity $licence)
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
     * @param array $metadata
     * @return bool
     */
    private function shouldSendEmail($metadata)
    {
        return array_key_exists('details', $metadata) &&
            is_array($metadata['details']) &&
            array_key_exists('sendToAddress', $metadata['details']) &&
            $metadata['details']['sendToAddress'] === 'correspondenceAddress' &&
            array_key_exists('allowEmail', $metadata['details']) &&
            $metadata['details']['allowEmail'] === 'Y';
    }

    /**
     * @param $licence LicenceEntity
     * @return CreateTask
     */
    private function createTaskSideEffect($licence)
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
            ? $sysParamRepo->fetchValue(SystemParameter::LAST_TM_NI_TASK_OWNER)
            : $sysParamRepo->fetchValue(SystemParameter::LAST_TM_GB_TASK_OWNER);
        if ($assignToUserId && $assignToUserId != 0) {
            $params['assignedToUser'] = $assignToUserId;
        }
        return CreateTask::create($params);
    }

    private function updateLastTmLetterDate(LicenceEntity $licence)
    {
        /** @var TransportManagerLicence $tmlRepo */
        $tmlRepo = $this->getRepo('TransportManagerLicence');
        $removedTms = $tmlRepo->fetchRemovedTmForLicence($licence->getId());
        /** @var TmlEntity $removedTm */
        foreach ($removedTms as $removedTm) {
            $removedTm->setLastTmLetterDate(new DateTime());
            $tmlRepo->save($removedTm);
        }
    }
}
