<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Letter;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Letter\LetterIssue as Entity;
use Dvsa\Olcs\Api\Entity\Letter\LetterIssueTodo;
use Dvsa\Olcs\Api\Entity\Letter\LetterIssueVersion;
use Dvsa\Olcs\Api\Entity\Letter\LetterTodoVersion;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;

/**
 * LetterIssue Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
final class LetterIssueEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    /**
     * VOL-7280: linked LetterTodos must be propagated to the new version when an issue
     * is re-versioned, so editing issue content doesn't silently drop the links.
     */
    public function testCreateNewVersionPropagatesIssueTodoLinks(): void
    {
        $issue = new Entity();

        $todoVersionA = new LetterTodoVersion();
        $todoVersionA->setId(11);
        $todoVersionB = new LetterTodoVersion();
        $todoVersionB->setId(22);

        $currentVersion = new LetterIssueVersion();
        $currentVersion->setVersionNumber(3);
        $currentVersion->setLetterIssue($issue);

        $junctionA = new LetterIssueTodo();
        $junctionA->setLetterIssueVersion($currentVersion);
        $junctionA->setLetterTodoVersion($todoVersionA);
        $junctionA->setDisplayOrder(0);
        $currentVersion->addLetterIssueTodo($junctionA);

        $junctionB = new LetterIssueTodo();
        $junctionB->setLetterIssueVersion($currentVersion);
        $junctionB->setLetterTodoVersion($todoVersionB);
        $junctionB->setDisplayOrder(1);
        $currentVersion->addLetterIssueTodo($junctionB);

        $issue->addVersion($currentVersion);
        $issue->setCurrentVersion($currentVersion);

        $newVersion = $issue->createNewVersion();

        $this->assertSame(4, $newVersion->getVersionNumber());

        $newJunctions = $newVersion->getLetterIssueTodos();
        $this->assertCount(2, $newJunctions, 'Both to-do links should propagate to the new version');

        $propagated = $newJunctions->toArray();
        $this->assertSame($newVersion, $propagated[0]->getLetterIssueVersion());
        $this->assertSame($newVersion, $propagated[1]->getLetterIssueVersion());
        $this->assertSame($todoVersionA, $propagated[0]->getLetterTodoVersion());
        $this->assertSame($todoVersionB, $propagated[1]->getLetterTodoVersion());

        // Original junctions on the previous version must remain (we're creating new rows, not moving)
        $this->assertCount(2, $currentVersion->getLetterIssueTodos());
    }
}
