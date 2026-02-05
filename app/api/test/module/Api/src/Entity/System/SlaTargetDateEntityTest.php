<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\System;

use Dvsa\Olcs\Api\Entity as Entities;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\System\SlaTargetDate as Entity;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;

/**
 * SlaTargetDate Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class SlaTargetDateEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestConstruct')]
    public function testConstruct(mixed $entity, mixed $expect): void
    {
        $agreedDate = new \DateTime('2011-10-09');
        $underDelegation = 'unit_UnderDeleg';

        $sut = new Entity($entity, $agreedDate, $underDelegation);

        $actual = null;
        if ($expect === 'DOC') {
            $actual = $sut->getDocument();
        } elseif ($expect === 'PI') {
            $actual = $sut->getPi();
        } elseif ($expect === 'SUBMISSION') {
            $actual = $sut->getSubmission();
        }

        static::assertSame($entity, $actual);
        static::assertEquals($agreedDate, $sut->getAgreedDate());
        static::assertEquals($underDelegation, $sut->getUnderDelegation());
    }

    public static function dpTestConstruct(): array
    {
        return [
            [
                'entity' => m::mock(\Dvsa\Olcs\Api\Entity\Doc\Document::class),
                'expect' => 'DOC',
            ],
            [
                'entity' => m::mock(\Dvsa\Olcs\Api\Entity\Pi\Pi::class),
                'expect' => 'PI',
            ],
            [
                'entity' => m::mock(\Dvsa\Olcs\Api\Entity\Submission\Submission::class),
                'expect' => 'SUBMISSION',
            ],
        ];
    }

    public function testConstructExceptionNotFound(): void
    {
        $this->expectException(NotFoundException::class);

        new Entity(new Entities\System\RefData(), null);
    }
}
