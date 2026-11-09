<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterInstance;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstance as LetterInstanceEntity;
use Dvsa\Olcs\Api\Entity\Letter\LetterIssue;
use Dvsa\Olcs\Api\Service\Letter\IssueChange\IssueChangePlanner;
use Dvsa\Olcs\Api\Service\Letter\LetterInstanceComposer;
use Dvsa\Olcs\Api\Service\Letter\LetterInstanceGrabSnapshotter;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Letter\LetterInstance\UpdateIssues as Cmd;
use Psr\Container\ContainerInterface;

/**
 * Change the issues on a letter that has already been generated (VOL-7734)
 *
 * Takes the full list of issues wanted, so posting the same list twice is harmless. Everything
 * already on the letter that stays keeps its edits; the to-dos follow the same rules as a freshly
 * generated letter. If the change would throw away a caseworker's edits it is refused, with the
 * edits named, until it is resent with confirmLoss.
 */
final class UpdateIssues extends AbstractCommandHandler
{
    use LetterContextTrait;

    protected $repoServiceName = 'LetterInstance';

    protected $extraRepos = ['LetterIssue'];

    private IssueChangePlanner $planner;
    private LetterInstanceComposer $letterInstanceComposer;
    private LetterInstanceGrabSnapshotter $grabSnapshotter;

    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $this->planner = $container->get(IssueChangePlanner::class);
        $this->letterInstanceComposer = $container->get(LetterInstanceComposer::class);
        $this->grabSnapshotter = $container->get(LetterInstanceGrabSnapshotter::class);
        return parent::__invoke($container, $requestedName, $options);
    }

    #[\Override]
    public function handleCommand(CommandInterface $command): Result
    {
        /** @var Cmd $command */

        /** @var LetterInstanceEntity $letterInstance */
        $letterInstance = $this->getRepo()->fetchUsingId($command);

        if (!$letterInstance->isOpenForChanges()) {
            throw new ValidationException(['Issues can only be changed on a draft or ready letter']);
        }

        $plan = $this->planner->plan($letterInstance, $this->selectedIssues($letterInstance, $command));

        if (!$plan->hasChanges()) {
            $this->result->addMessage('No changes to issues');
            return $this->result;
        }

        $lostEdits = $plan->lostEdits();
        if ($lostEdits !== [] && !$command->getConfirmLoss()) {
            $this->result->setFlag('confirmationRequired', true);
            foreach ($lostEdits as $name) {
                $this->result->addMessage($name);
            }
            return $this->result;
        }

        $created = $this->letterInstanceComposer->applyIssueChange($letterInstance, $plan);

        // grabs on the new rows resolve now; what was frozen at generation stays as it was
        $this->grabSnapshotter->snapshotRows($letterInstance, $created['issues'], $created['todos']);

        // read before saving: Doctrine clears the id of a row it deletes
        $movedFrom = array_map(static fn(array $pair) => $pair[0]->getId(), $created['movedTodos']);

        $this->getRepo()->save($letterInstance);

        // a flag rather than addId: Result collapses a single id to a scalar, callers want a list
        $this->result->setFlag('addedIssueIds', array_map(
            static fn($instanceIssue) => $instanceIssue->getId(),
            $created['issues']
        ));

        // to-dos that changed issue are new rows now (see LetterInstanceComposer::reconcileTodos)
        $movedTodoIds = [];
        foreach ($created['movedTodos'] as $index => [, $to]) {
            $movedTodoIds[$movedFrom[$index]] = $to->getId();
        }
        $this->result->setFlag('movedTodoIds', $movedTodoIds);
        $this->result->addMessage(sprintf(
            'Issues updated: %d added, %d removed',
            count($created['issues']),
            count($plan->removedIssues)
        ));

        return $this->result;
    }

    /**
     * The posted issues once each, in order. Issues already on the letter are always kept: they were
     * valid when it was generated. A new one has to suit the licence's Goods/PSV, as in Generate.
     *
     * @return list<LetterIssue>
     */
    private function selectedIssues(LetterInstanceEntity $letterInstance, Cmd $command): array
    {
        $onLetter = [];
        foreach ($letterInstance->getLetterInstanceIssues() as $instanceIssue) {
            $parentId = $instanceIssue->getLetterIssueVersion()?->getLetterIssue()?->getId();
            if ($parentId !== null) {
                $onLetter[(int) $parentId] = true;
            }
        }

        $goodsOrPsv = $this->goodsOrPsvAndNi($letterInstance)['goodsOrPsv'];

        $selected = [];
        foreach (array_unique(array_map('intval', $command->getSelectedIssues() ?? [])) as $issueId) {
            /** @var LetterIssue $letterIssue */
            $letterIssue = $this->getRepo('LetterIssue')->fetchById($issueId);

            if (isset($onLetter[$issueId])) {
                $selected[] = $letterIssue;
                continue;
            }

            $currentVersion = $letterIssue->getCurrentVersion();
            if ($currentVersion !== null && ($goodsOrPsv === null || $currentVersion->appliesToType($goodsOrPsv))) {
                $selected[] = $letterIssue;
            }
        }

        return $selected;
    }
}
