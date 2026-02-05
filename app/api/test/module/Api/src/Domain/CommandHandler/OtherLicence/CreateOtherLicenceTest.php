<?php

declare(strict_types=1);

/**
 * Create Other Licence Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\OtherLicence;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\OtherLicence\CreateOtherLicence;
use Dvsa\Olcs\Api\Domain\Repository\OtherLicence as OtherLicenceRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\OtherLicence\CreateOtherLicence as Cmd;
use Dvsa\Olcs\Api\Entity\OtherLicence\OtherLicence as OtherLicenceEntity;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion as UpdateApplicationCompletionCmd;

/**
 * Create Other Licence Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CreateOtherLicenceTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateOtherLicence();
        $this->mockRepo('OtherLicence', OtherLicenceRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            OtherLicenceEntity::TYPE_CURRENT
        ];

        $this->references = [
            Application::class => [
                1 => m::mock(Application::class)
            ],
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {

        $data = [
            'licNo' => 'licNo',
            'holderName' => 'holderName',
            'willSurrender' => 'Y',
            'application' => 1,
            'previousLicenceType' => OtherLicenceEntity::TYPE_CURRENT
        ];

        /** @var OtherLicenceEntity $savedOtherLicence */
        $savedOtherLicence = null;

        $command = Cmd::create($data);

        $this->repoMap['OtherLicence']->shouldReceive('save')
            ->once()
            ->with(m::type(OtherLicenceEntity::class))
            ->andReturnUsing(
                function (OtherLicenceEntity $otherLicence) use (&$savedOtherLicence) {
                    $otherLicence->setId(1);
                    $savedOtherLicence = $otherLicence;
                }
            );

        $this->expectedSideEffect(
            UpdateApplicationCompletionCmd::class,
            ['id' => 1, 'section' => 'licenceHistory'],
            new Result()
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => ['otherLicence' => 1],
            'messages' => ['Other licence created successfully']
        ];

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($expected, $result->toArray());

        $this->assertEquals('licNo', $savedOtherLicence->getLicNo());
        $this->assertEquals('holderName', $savedOtherLicence->getHolderName());
        $this->assertEquals('Y', $savedOtherLicence->getWillSurrender());
        $this->assertSame($this->references[Application::class][1], $savedOtherLicence->getApplication());
        $this->assertSame(
            $this->refData[OtherLicenceEntity::TYPE_CURRENT],
            $savedOtherLicence->getPreviousLicenceType()
        );
    }
}
