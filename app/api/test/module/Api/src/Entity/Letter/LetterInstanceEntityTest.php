<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Letter;

use Dvsa\Olcs\Api\Entity\Letter\LetterInstance as Entity;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceIssue;
use Dvsa\Olcs\Api\Entity\Letter\LetterIssueTodo;
use Dvsa\Olcs\Api\Entity\Letter\LetterIssueVersion;
use Dvsa\Olcs\Api\Entity\Letter\LetterTodo;
use Dvsa\Olcs\Api\Entity\Letter\LetterTodoVersion;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;

/**
 * LetterInstance Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
final class LetterInstanceEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    private function instanceIssueLinking(LetterTodoVersion $todoVersion): LetterInstanceIssue
    {
        $issueTodo = new LetterIssueTodo();
        $issueTodo->setLetterTodoVersion($todoVersion);

        $issueVersion = new LetterIssueVersion();
        $issueVersion->addLetterIssueTodo($issueTodo);

        $instanceIssue = new LetterInstanceIssue();
        $instanceIssue->setLetterIssueVersion($issueVersion);

        return $instanceIssue;
    }

    private function todoVersion(int $versionId, ?LetterTodo $todo = null): LetterTodoVersion
    {
        $version = new LetterTodoVersion();
        $version->setId($versionId);
        $version->setLetterTodo($todo);

        return $version;
    }

    /**
     * VOL-7408: issues can link different versions of the same to-do, which still counts as one to-do.
     */
    public function testTodoRequiringIssueCountsAreKeyedByTodoAcrossVersions(): void
    {
        $todo = new LetterTodo();
        $todo->setId(5);

        $entity = new Entity();
        $entity->addLetterInstanceIssue($this->instanceIssueLinking($this->todoVersion(32, $todo)));
        $entity->addLetterInstanceIssue($this->instanceIssueLinking($this->todoVersion(27, $todo)));

        $this->assertSame(['todo-5' => 2], $entity->getTodoRequiringIssueCounts());
    }

    public function testTodoRequiringIssueCountsFallBackToTheVersionWithoutAParent(): void
    {
        $entity = new Entity();
        $entity->addLetterInstanceIssue($this->instanceIssueLinking($this->todoVersion(27)));

        $this->assertSame(['version-27' => 1], $entity->getTodoRequiringIssueCounts());
    }
}
