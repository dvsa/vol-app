<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Bus;

use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore;
use Dvsa\Olcs\Api\Domain\Command\Email\SendBSRNotificationToLTAs;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\BadRequestException;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Entity\Bus\BusReg;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * @author Dmitry Golubev <dmitrij.golubev@valtech.com>
 */
class PrintLetter extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Bus';

    private static $templates = [
        BusReg::STATUS_REGISTERED => Document::BUS_REG_NEW,
        BusReg::STATUS_CANCELLED => Document::BUS_REG_CANCELLATION,
        BusReg::STATUS_VAR => Document::BUS_REG_VARIATION,
    ];

    private static $templatesRefusedShortNotice = [
        BusReg::STATUS_REGISTERED => Document::BUS_REG_NEW_REFUSE_SHORT_NOTICE,
        BusReg::STATUS_CANCELLED => Document::BUS_REG_CANCELLATION_REFUSE_SHORT_NOTICE,
        BusReg::STATUS_VAR => Document::BUS_REG_VARIATION_REFUSE_SHORT_NOTICE,
    ];

    private static $description = [
        BusReg::STATUS_REGISTERED => 'Bus registration letter',
        BusReg::STATUS_VAR => 'Bus variation letter',
        BusReg::STATUS_CANCELLED => 'Bus cancelled letter',
    ];

    /** @var  BusReg */
    private $busReg;

    /**
     * Handle comment
     *
     * @param \Dvsa\Olcs\Transfer\Command\Bus\PrintLetter $command Command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $this->busReg = $this->getRepo()->fetchUsingId($command);

        if (!$this->busReg instanceof BusReg) {
            throw new NotFoundException('Bus registration not found');
        }

        $licId = $this->busReg->getLicence()->getId();
        $busRegId = $this->busReg->getId();

        $dtoData = [
            'template' => $this->getTemplate(),
            'query' => [
                'licence' => $licId,
                'busRegId' => $busRegId,
            ],
            'description' => $this->getDescription(),
            'licence' => $licId,
            'busReg' => $busRegId,
            'category' => Category::CATEGORY_BUS_REGISTRATION,
            'subCategory' => Category::BUS_SUB_CATEGORY_OTHER_DOCUMENTS,
            'isExternal' => false,
            'dispatch' => true,
            'isEnforcePrint' => 'N',
        ];

        $generatedDocument = $this->handleSideEffect(GenerateAndStore::create($dtoData));

        if ($this->busReg->isFromEbsr() === false) {
            $emailDtoData = [
                'id' => $busRegId,
                'docs' => [$generatedDocument->getIds()['document']]
            ];
            $this->handleSideEffect(SendBSRNotificationToLTAs::create($emailDtoData));
        }

        return $generatedDocument;
    }

    /**
     * Get the Template Document Id for bus registration
     *
     * @return int template document Id
     */
    private function getTemplate()
    {
        $key = $this->getTemplateKey();

        //  find template for refused short notice
        if (
            $this->busReg->isShortNoticeRefused() === true
            && isset(self::$templatesRefusedShortNotice[$key])
        ) {
            return self::$templatesRefusedShortNotice[$key];
        }

        //  find template by status
        if (isset(self::$templates[$key])) {
            return self::$templates[$key];
        }

        throw new BadRequestException('Template not found for bus registration');
    }

    /**
     * Get File description
     *
     * @return string
     */
    private function getDescription()
    {
        $key = $this->getTemplateKey();

        $text = 'Bus letter';
        if (isset(self::$description[$key])) {
            $text = self::$description[$key];
        }

        if ($this->busReg->isShortNoticeRefused() === true) {
            $text .= ' with refused short notice';
        }

        return $text;
    }

    /**
     * Return key for looking template and description
     *
     * @return string
     */
    private function getTemplateKey()
    {
        $statusId = $this->busReg->getStatus()->getId();

        //  for variation
        if (
            $statusId === BusReg::STATUS_REGISTERED
            && $this->busReg->isVariation()
        ) {
            return BusReg::STATUS_VAR;
        }

        return $statusId;
    }
}
