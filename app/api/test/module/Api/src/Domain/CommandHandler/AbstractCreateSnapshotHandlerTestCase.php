<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\LicenceProviderInterface;
use Dvsa\Olcs\Snapshot\Service\Snapshots\SnapshotGeneratorInterface;
use Dvsa\Olcs\Transfer\Command\Document\Upload;
use Mockery as m;

/**
 * Abstract create snapshot command handler test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
abstract class AbstractCreateSnapshotHandlerTestCase extends AbstractCommandHandlerTestCase
{
    public $documentLinkValue;
    protected $cmdClass = 'changeMe';
    protected $sutClass = 'changeMe';
    protected $repoServiceName = 'changeMe';
    protected $repoClass = 'changeMe';
    protected $entityClass = 'changeMe';
    protected $documentCategory = 'changeMe';
    protected $documentSubCategory = 'changeMe';
    protected $documentDescription = 'changeMe';
    protected $documentLinkId = 'changeMe';
    protected $generatorClass = SnapshotGeneratorInterface::class;

    public function setUp(): void
    {
        $this->mockRepo($this->repoServiceName, $this->repoClass);
        $this->sut = new $this->sutClass();

        $this->mockedSmServices = [
            $this->generatorClass => m::mock($this->generatorClass)
        ];

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $id = 999;
        $html = '<html></html>';
        $licence = m::mock(LicenceEntity::class);
        $command = $this->cmdClass::create(['id' => $id]);

        $entity = m::mock($this->entityClass);

        $this->repoMap[$this->repoServiceName]
            ->shouldReceive('fetchUsingId')
            ->with($command)
            ->once()
            ->andReturn($entity);

        $entity->shouldReceive('getId')->twice()->withNoArgs()->andReturn($id);
        $entity->shouldReceive('getRelatedLicence')
            ->times($entity instanceof LicenceProviderInterface ? 1 : 0)
            ->withNoArgs()
            ->andReturn($licence);

        //this adds any extra assertions specific to the extending class
        $entity = $this->extraEntityAssertions($entity);

        $this->mockedSmServices[$this->generatorClass]
            ->shouldReceive('setData')
            ->once()
            ->with(['entity' => $entity]);

        $this->mockedSmServices[$this->generatorClass]
            ->shouldReceive('generate')
            ->once()
            ->withNoArgs()
            ->andReturn($html);

        $params = [
            'content' => base64_encode($html),
            'category' => $this->documentCategory,
            'subCategory' => $this->documentSubCategory,
            'isExternal' => false,
            'isScan' => false,
            'filename' => str_replace(' ', '', $this->documentDescription) . '.html',
            'description' => $this->documentDescription,
            'licence' => $entity instanceof LicenceProviderInterface ? $licence : null,
            $this->documentLinkId => $this->documentLinkValue,
        ];

        $this->expectedSideEffect(Upload::class, $params, new Result());

        $expectedResult = [
            'id' => [
                $this->repoServiceName => $id,
            ],
            'messages' => [
                $this->repoServiceName . ' snapshot generated',
            ]
        ];

        $this->assertEquals($expectedResult, $this->sut->handleCommand($command)->toArray());
    }

    /**
     * Override this method in case of needing specific entity assertions i.e. for a permit application reference
     */
    protected function extraEntityAssertions(m\MockInterface $entity): mixed
    {
        return $entity;
    }
}
