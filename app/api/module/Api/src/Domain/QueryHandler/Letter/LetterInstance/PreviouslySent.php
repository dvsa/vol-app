<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Letter\LetterInstance;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstance as LetterInstanceEntity;
use Dvsa\Olcs\Transfer\Query\Letter\LetterInstance\PreviouslySent as Qry;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * The letters of one type most recently sent for whatever a new letter is being started on, so
 * the New letter form can offer to start from the issues one of them covered
 */
final class PreviouslySent extends AbstractQueryHandler
{
    private const int HOW_MANY = 10;

    protected $repoServiceName = 'LetterInstance';

    /**
     * @param QueryInterface $query
     * @return array{result: list<array<string, mixed>>, count: int}
     */
    #[\Override]
    public function handleQuery(QueryInterface $query): array
    {
        /** @var Qry $query */
        // The query names what the new letter is being started on. Letters don't link to a
        // permit application, so there's nothing to look those up by.
        $letters = [];
        foreach (LetterInstanceEntity::RAISED_ON as $raisedOn) {
            $id = $query->{'get' . ucfirst($raisedOn)}();
            if ($id !== null) {
                $letters = $this->getRepo()->fetchLastSent((int) $query->getLetterType(), $raisedOn, (int) $id, self::HOW_MANY);
                break;
            }
        }

        $result = array_map($this->summarise(...), array_values($letters));

        return ['result' => $result, 'count' => count($result)];
    }

    /**
     * What the New letter form needs to offer the letter and tick its issues
     *
     * @return array{id: int, sentOn: ?string, choices: list<string>, issueIds: list<int>}
     */
    private function summarise(LetterInstanceEntity $letter): array
    {
        $choices = [];
        foreach ($letter->getLetterInstanceChoices() as $instanceChoice) {
            $choices[] = $instanceChoice->getLetterChoice();
        }
        usort($choices, static fn($a, $b): int => (int) $a->getDisplayOrder() <=> (int) $b->getDisplayOrder());

        // The parent issue, which is what the form lists, once each: the form counts the ones
        // it can't tick against this list
        $issueIds = [];
        foreach ($letter->getIssuesInLetterOrder() as $instanceIssue) {
            $issueId = $instanceIssue->getLetterIssueVersion()?->getLetterIssue()?->getId();
            if ($issueId !== null) {
                $issueIds[(int) $issueId] = (int) $issueId;
            }
        }

        return [
            'id' => (int) $letter->getId(),
            'sentOn' => $letter->getSentOn(true)?->format(\DateTimeInterface::ISO8601),
            'choices' => array_map(static fn($choice): string => (string) $choice->getLabel(), $choices),
            'issueIds' => array_values($issueIds),
        ];
    }
}
