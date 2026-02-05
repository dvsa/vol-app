<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cli\Domain\CommandHandler\MessageQueue\Consumer\CompaniesHouse;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStoreWithMultipleAddresses;
use Dvsa\Olcs\Api\Domain\Command\Email\SendPtrNotificationForRegisteredUser;
use Dvsa\Olcs\Api\Domain\Command\Email\SendLiquidatedCompanyForUnregisteredUser;
use Dvsa\Olcs\Api\Domain\Command\Queue\Create as CreateQueue;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\CompaniesHouse\CompaniesHouseCompany;
use Dvsa\Olcs\Api\Entity\CompaniesHouse\CompaniesHouseInsolvencyPractitioner;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Cli\Domain\CommandHandler\MessageQueue\Consumer\AbstractConsumer;
use Dvsa\Olcs\CompaniesHouse\Service\Client as CompaniesHouseClient;
use Dvsa\Olcs\CompaniesHouse\Service\Exception\ServiceException;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Document\PrintLetter;
use Dvsa\Olcs\Api\Domain\QueueAwareTrait;
use Psr\Container\ContainerInterface;

class ProcessInsolvency extends AbstractConsumer
{
    use QueueAwareTrait;

    public const GB_TEAMLEADER_TASK = 'GB insolvency team leader';
    public const NI_TEAMLEADER_TASK = 'NI insolvency team leader';


    protected $extraRepos = [
        'CompaniesHouseCompany',
        'Organisation',
        'Team'
    ];

    public const GB_GV_STANDARD_TEMPLATE = [
        'identifier' => 'Reg_31_Standard_licence',
        'description' => 'Liquidation/receivership/administration letter for standard GV licence'
    ];

    public const GB_GV_RESTRICTED_TEMPLATE = [
        'identifier' => 'Reg_31_Restricted_licence',
        'description' => 'Liquidation/receivership/administration letter for restricted GV licence'
    ];

    public const GB_PSV_STANDARD_TEMPLATE = [
        'identifier' => 'Section57Standard',
        'description' => 'Liquidation/receivership/administration letter for standard PSV licence'
    ];

    public const GB_PSV_RESTRICTED_TEMPLATE = [
        'identifier' => 'Section57Restricted',
        'description' => 'Liquidation/receivership/administration letter for restricted PSV licence'
    ];

    public const NI_GV_STANDARD_TEMPLATE = [
        'identifier' => 'GV_Reg_29_NIStandardlicence',
        'description' => 'Liquidation/receivership/administration letter for standard NI GV licence'
    ];

    public const NI_GV_RESTRICTED_TEMPLATE = [
        'identifier' => 'GV_Reg_29_NIRestrictedlicence',
        'description' => 'Liquidation/receivership/administration letter for restricted NI GV licence'
    ];


    /**
     * @var CompaniesHouseCompany
     */
    protected $company;

    protected CompaniesHouseClient $companiesHouseApi;

    /**
     * @param CommandInterface $command
     *
     * @return Result
     * @throws RuntimeException
     * @throws ServiceException
     * @throws \Exception
     */
    public function handleCommand(CommandInterface $command)
    {
        $messages = $this->fetchMessages(1);

        if (is_null($messages)) {
            $this->noMessages();
            return $this->result;
        }

        $companyNumber = $messages[0]['Body'];
        $this->company = $this->getRepo('CompaniesHouseCompany')->getLatestByCompanyNumber($companyNumber);

        $insolvencyDetails = $this->companiesHouseApi->getInsolvencyDetails($this->company->getCompanyNumber());
        $practitioners = $this->getPractitioners($insolvencyDetails);
        $this->company->setInsolvencyPractitioners($practitioners);

        $this->getRepo('CompaniesHouseCompany')->save($this->company);

        $this->result->addMessage($practitioners->count() . ' insolvency practitioners added for company ' .
            $this->company->getCompanyNumber());

        /** @var Organisation[] $organisations */
        $organisations = $this->getRepo('Organisation')
            ->getByCompanyOrLlpNo($this->company->getCompanyNumber());

        foreach ($organisations as $organisation) {
            $this->handleGenerations($organisation);
        }

        $this->company->setInsolvencyProcessed(true);
        $this->getRepo('CompaniesHouseCompany')->save($this->company);

        $this->deleteMessage($messages[0]);

        return $this->result;
    }

    /**
     * Fetch the insolvencyPractitioners for the company
     *
     *
     * @return ArrayCollection
     */
    private function getPractitioners(array $insolvencyDetails): ArrayCollection
    {
        $practitioners = [];
        foreach ($insolvencyDetails as $details) {
            $practitioners  = array_merge($practitioners, $details['practitioners']);
        }

        $filteredPractitioners = $this->filterPractitioners($practitioners);

        return new ArrayCollection(array_map($this->mapToEntity(...), $filteredPractitioners));
    }


    /**
     * Map practitioner from API result to CompaniesHouseInsolvencyPractitioner entity
     *
     *
     * @return CompaniesHouseInsolvencyPractitioner
     */
    private function mapToEntity(array $practitioner): CompaniesHouseInsolvencyPractitioner
    {
        $data = [
            'name' => $practitioner['name'],
            'appointedOn' => $practitioner['appointed_on'] ?? null,
            'addressLine1' => $practitioner['address']['address_line_1'] ?? null,
            'addressLine2' => $practitioner['address']['address_line_2'] ?? null,
            'postalCode' => $practitioner['address']['postal_code'] ?? null,
            'locality' => $practitioner['address']['locality'] ?? null,
            'region' => $practitioner['address']['region'] ?? null,
            'companiesHouseCompany' => $this->company
        ];

        return new CompaniesHouseInsolvencyPractitioner($data);
    }


    /**
     * @throws \Exception
     */
    protected function handleGenerations(Organisation $organisation): void
    {
        $licences = $organisation->getActiveLicences();
        foreach ($licences as $licence) {
            $this->generateLetters($licence);
            $this->printLetters();
            $this->generateEmails($licence);
            $this->generateFollowUpTask($licence);
        }
    }

    private function generateLetters(Licence $licence): void
    {
        $template = $this->selectTemplate($licence);

        $data = [
            'generateCommandData' => [
                'template' => $template['identifier'],
                'licence' => $licence->getId(),
                'query' => [
                    'licence' => $licence->getId()
                ],
                'category' => Category::CATEGORY_LICENSING,
                'subCategory' => SubCategory::DOC_SUB_CATEGORY_REG_29_31_SECTION_57,
                'isExternal' => false,
                'description' => $template['description'],
            ],
            'addressBookmark' => 'licence_holder_address',
            'bookmarkBundle' => [
                'correspondenceCd' => ['address']
            ],
            'sendToAddresses' => [
                'insolvencyPractitionerAddresses' => true
            ]
        ];

        $this->result->merge($this->handleSideEffect(GenerateAndStoreWithMultipleAddresses::create($data)));
    }

    /**
     *
     * @return void
     * @throws \Exception
     */
    private function generateFollowUpTask(Licence $licence): void
    {
        $taskData = [
            'category' => Category::CATEGORY_LICENSING,
            'subCategory' => SubCategory::DOC_SUB_CATEGORY_REG_29_31_SECTION_57,
            'description' => 'Check response to Regulation 29/31/Section 57',
            'actionDate' => (new DateTime())->add(new \DateInterval('P21D'))->format('Y-m-d'),
            'licence' => $licence->getId(),
            'urgent' => 'Y',
            'assignedToTeam' => $licence->isNi() ? $this->getTeamId(self::NI_TEAMLEADER_TASK) : $this->getTeamId(self::GB_TEAMLEADER_TASK)
        ];

        $params = [
            'type' => Queue::TYPE_CREATE_TASK,
            'status' => Queue::STATUS_QUEUED,
            'options' => json_encode($taskData)
        ];

        $this->result->merge($this->handleSideEffect(CreateQueue::create($params)));
    }

    private function generateEmails(Licence $licence): void
    {
        $translateToWelsh = $licence->getTranslateToWelsh();

        $correspondenceEmail = $licence->getCorrespondenceCd()->getEmailAddress();

        $selfServeUserEmailCommands = array_map(
            function ($organisationUser) use ($translateToWelsh) {
                $selfServeUserEmailCommandsData = [
                    'emailAddress' => $organisationUser->getUser()->getContactDetails()->getEmailAddress(),
                    'translateToWelsh' => $translateToWelsh
                ];
                return $this->emailQueue(
                    SendPtrNotificationForRegisteredUser::class,
                    $selfServeUserEmailCommandsData,
                    $this->company->getId()
                );
            },
            $licence->getOrganisation()->getAdminOrganisationUsers()->toArray()
        );

        if (is_null($correspondenceEmail) && empty($selfServeUserEmailCommands)) {
            $this->result->addMessage('Unable to send emails: No email addresses found');
            return;
        }

        if (!is_null($correspondenceEmail)) {
            $this->sendCorrespondenceEmail(
                $correspondenceEmail,
                $translateToWelsh,
                !empty($selfServeUserEmailCommands)
            );
        }

        foreach ($selfServeUserEmailCommands as $selfServeUserEmailCommand) {
            $this->result->merge($this->handleSideEffect($selfServeUserEmailCommand));
        }
    }

    private function printLetters(): void
    {
        foreach ($this->result->getId('documents') as $letter) {
            $this->result->merge($this->handleSideEffect(
                PrintLetter::create([
                    'id' => $letter,
                    'method' => PrintLetter::METHOD_PRINT_AND_POST
                ])
            ));
        }
    }

    /**
     *
     * @return int
     * @throws RuntimeException
     */
    private function getTeamId(string $name): int
    {
        return $this->getRepo('Team')->fetchOneByName($name)->getId();
    }

    /**
     * @return array
     */
    private function selectTemplate(Licence $licence): array
    {
        if ($licence->isNi()) {
            if ($licence->isRestricted()) {
                return static::NI_GV_RESTRICTED_TEMPLATE;
            }
            return static::NI_GV_STANDARD_TEMPLATE;
        }

        if ($licence->isPsv()) {
            if ($licence->isRestricted() || $licence->isSpecialRestricted()) {
                return static::GB_PSV_RESTRICTED_TEMPLATE;
            }
            return static::GB_PSV_STANDARD_TEMPLATE;
        }

        if ($licence->isRestricted()) {
            return static::GB_GV_RESTRICTED_TEMPLATE;
        }

        return static::GB_GV_STANDARD_TEMPLATE;
    }

    private function sendCorrespondenceEmail(string $email, string $translateToWelsh, bool $isRegistered): void
    {
        $cmdData = [
            'emailAddress' => $email,
            'translateToWelsh' => $translateToWelsh,
            'docs' => [$this->result->getId('correspondenceAddress')]
        ];
        if (!$isRegistered) {
            $cmd = $this->emailQueue(SendLiquidatedCompanyForUnregisteredUser::class, $cmdData, $this->company->getId());
        } else {
            $cmd = $this->emailQueue(SendPtrNotificationForRegisteredUser::class, $cmdData, $this->company->getId());
        }

        $this->result->merge($this->handleSideEffect($cmd));
    }

    /**
     * Filters out any practitioners that have ceased to act and then removes any duplicates
     *
     * @return array
     */
    private function filterPractitioners(array $practitionerData): array
    {
        $practitionerData = array_filter($practitionerData, fn($practitioner) => empty($practitioner['ceased_to_act_on']));

        return array_values(array_unique($practitionerData, SORT_REGULAR));
    }
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $this->companiesHouseApi = $container->get(CompaniesHouseClient::class);
        return parent::__invoke($container, $requestedName, $options);
    }
}
