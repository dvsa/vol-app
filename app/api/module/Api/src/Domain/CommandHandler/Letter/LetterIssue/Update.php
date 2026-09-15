<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterIssue;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Letter\LetterIssue as LetterIssueEntity;
use Dvsa\Olcs\Api\Entity\Letter\LetterIssueTodo;
use Dvsa\Olcs\Transfer\Command\Letter\LetterIssue\Update as Cmd;

/**
 * Update LetterIssue
 */
final class Update extends AbstractCommandHandler
{
    protected $repoServiceName = 'LetterIssue';

    protected $extraRepos = ['Category', 'SubCategory', 'LetterIssueType', 'LetterTodo'];

    #[\Override]
    public function handleCommand(CommandInterface $command): Result
    {
        /** @var Cmd $command */

        /** @var \Dvsa\Olcs\Api\Entity\Letter\LetterIssue $letterIssue */
        $letterIssue = $this->getRepo()->fetchUsingId($command);

        // Update all properties - versioning will be handled by repository
        $letterIssue->setIssueKey($command->getIssueKey());
        $letterIssue->setCategory($this->getRepo('Category')->fetchById($command->getCategory()));

        // Set subCategory only if provided
        if ($command->getSubCategory()) {
            $letterIssue->setSubCategory($this->getRepo('SubCategory')->fetchById($command->getSubCategory()));
        } else {
            $letterIssue->setSubCategory(null);
        }

        $letterIssue->setHeading($command->getHeading());
        $letterIssue->setModalLabel($command->getModalLabel());
        $letterIssue->setDefaultBodyContent($command->getDefaultBodyContent());
        $letterIssue->setHelpText($command->getHelpText());
        $letterIssue->setMinLength($command->getMinLength());
        $letterIssue->setMaxLength($command->getMaxLength());
        $letterIssue->setRequiresInput($command->getRequiresInput());
        $letterIssue->setIsNi($command->getIsNi());

        // Set goodsOrPsv only if provided
        if ($command->getGoodsOrPsv()) {
            $letterIssue->setGoodsOrPsv($this->getRepo()->getRefdataReference($command->getGoodsOrPsv()));
        } else {
            $letterIssue->setGoodsOrPsv(null);
        }

        // Set letterIssueType only if provided
        if ($command->getLetterIssueTypeId()) {
            $letterIssue->setLetterIssueType($this->getRepo('LetterIssueType')->fetchById($command->getLetterIssueTypeId()));
        } else {
            $letterIssue->setLetterIssueType(null);
        }

        $this->getRepo()->save($letterIssue);

        // Reconcile LetterTodo links on the (possibly new) currentVersion (VOL-7280).
        // If the command's letterTodos is null, leave existing links alone; if it's an
        // empty array the admin wants them cleared.
        $todos = $command->getLetterTodos();
        if ($todos !== null && $letterIssue->getCurrentVersion() !== null) {
            $this->syncIssueTodos($letterIssue, $todos);
            $this->getRepo()->save($letterIssue);
        }

        $this->result->addId('letterIssue', $letterIssue->getId());
        $this->result->addMessage("Letter issue '{$letterIssue->getHeading()}' updated");

        return $this->result;
    }

    /**
     * Reconcile this issue's current-version letterIssueTodos junction to match $todoIds.
     * Mirrors the LetterType/Update flushAll-between-clear-and-add pattern that keeps
     * Doctrine from issuing INSERTs before DELETEs on composite-PK junctions.
     *
     * @param LetterIssueEntity $letterIssue
     * @param array $todoIds
     * @return void
     */
    private function syncIssueTodos(LetterIssueEntity $letterIssue, array $todoIds): void
    {
        $currentVersion = $letterIssue->getCurrentVersion();

        foreach ($currentVersion->getLetterIssueTodos()->toArray() as $existing) {
            $currentVersion->removeLetterIssueTodo($existing);
        }

        // Flush removals so DELETEs execute before INSERTs (composite PK)
        $this->getRepo()->flushAll();

        $displayOrder = 0;
        foreach ($todoIds as $todoId) {
            $letterTodo = $this->getRepo('LetterTodo')->fetchById($todoId);
            $todoVersion = $letterTodo->getCurrentVersion();
            if ($todoVersion === null) {
                continue;
            }
            $junction = new LetterIssueTodo();
            $junction->setLetterIssueVersion($currentVersion);
            $junction->setLetterTodoVersion($todoVersion);
            $junction->setDisplayOrder($displayOrder++);
            $currentVersion->addLetterIssueTodo($junction);
        }
    }
}
