<?php

declare(strict_types=1);

/**
 * Create Case Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Cases;

use Doctrine\ORM\Query;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Cases\CreateCase;
use Dvsa\Olcs\Api\Domain\Repository\Cases as CasesRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Cases\CreateCase as Cmd;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager as TransportManagerEntity;

/**
 * Create Case Test
 */
class CreateCaseTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateCase();
        $this->mockRepo('Cases', CasesRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->references = [
            ApplicationEntity::class => [
                5 => m::mock(ApplicationEntity::class)
            ],
            LicenceEntity::class => [
                7 => m::mock(LicenceEntity::class)
            ],
            TransportManagerEntity::class => [
                9 => m::mock(TransportManagerEntity::class)
            ]
        ];

        $this->refData = [
            CasesEntity::LICENCE_CASE_TYPE,
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $command = Cmd::Create(
            [
                'categorys' => ['case_cat_compl_proh'],
                'outcomes' => ['case_o_opr'],
                'application' => null,
                'licence' => 7,
                'transportManager' => 9,
                'caseType' => CasesEntity::LICENCE_CASE_TYPE
            ]
        );

        $this->repoMap['Cases']->shouldReceive('save')
            ->with(m::type(CasesEntity::class))
            ->once();

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(\Dvsa\Olcs\Api\Domain\Command\Result::class, $result);
    }
}
