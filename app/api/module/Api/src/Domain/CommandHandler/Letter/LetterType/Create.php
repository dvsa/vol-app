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

    protected $extraRepos = ['MasterTemplate', 'Category', 'SubCategory', 'LetterTestData'];

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

        $this->getRepo()->save($letterType);

        $this->result->addId('letterType', $letterType->getId());
        $this->result->addMessage("Letter type '{$letterType->getName()}' created");
        
        return $this->result;
    }
}