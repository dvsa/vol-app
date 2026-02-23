<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterType;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Letter\LetterType as LetterTypeEntity;
use Dvsa\Olcs\Transfer\Command\Letter\LetterType\Create as Cmd;

/**
 * Create LetterType
 */
final class Create extends AbstractCommandHandler
{
    protected $repoServiceName = 'LetterType';

    protected $extraRepos = ['MasterTemplate', 'Category', 'SubCategory', 'LetterTestData', 'LetterAppendix'];

    public function handleCommand(CommandInterface $command): Result
    {
        /** @var Cmd $command */
        
        $letterType = new LetterTypeEntity();
        $letterType->setName($command->getName());
        $letterType->setDescription($command->getDescription());
        $letterType->setIsActive($command->getIsActive());
        
        // Set master template if provided
        if ($command->getMasterTemplate()) {
            $masterTemplate = $this->getRepo('MasterTemplate')->fetchById($command->getMasterTemplate());
            $letterType->setMasterTemplate($masterTemplate);
        }

        // Set category if provided
        if ($command->getCategory()) {
            $category = $this->getRepo('Category')->fetchById($command->getCategory());
            $letterType->setCategory($category);
        }

        // Set sub category if provided
        if ($command->getSubCategory()) {
            $subCategory = $this->getRepo('SubCategory')->fetchById($command->getSubCategory());
            $letterType->setSubCategory($subCategory);
        }

        // Set letter test data if provided
        if ($command->getLetterTestData()) {
            $letterTestData = $this->getRepo('LetterTestData')->fetchById($command->getLetterTestData());
            $letterType->setLetterTestData($letterTestData);
        }

        // Add appendices if provided
        if ($command->getAppendices() !== null) {
            $displayOrder = 0;
            foreach ($command->getAppendices() as $appendixId) {
                $letterAppendix = $this->getRepo('LetterAppendix')->fetchById($appendixId);
                $appendixVersion = $letterAppendix->getCurrentVersion();
                if ($appendixVersion) {
                    $lta = new \Dvsa\Olcs\Api\Entity\Letter\LetterTypeAppendix();
                    $lta->setLetterAppendixVersion($appendixVersion);
                    $lta->setDisplayOrder($displayOrder++);
                    $letterType->addLetterTypeAppendix($lta);
                }
            }
        }

        $this->getRepo()->save($letterType);

        $this->result->addId('letterType', $letterType->getId());
        $this->result->addMessage("Letter type '{$letterType->getName()}' created");
        
        return $this->result;
    }
}