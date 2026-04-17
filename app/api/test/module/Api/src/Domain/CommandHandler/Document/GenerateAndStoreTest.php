<?php

declare(strict_types=1);

/**
 * Generate And Store Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Api\Domain\Command\Document\CreateDocument;
use Dvsa\Olcs\Api\Domain\Command\Document\DispatchDocument;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Document\GenerateAndStore;
use Dvsa\Olcs\Api\Domain\Repository\Document;
use Dvsa\Olcs\Api\Service\Document\DocumentGenerator;
use Dvsa\Olcs\Api\Service\Document\NamingService;
use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Api\Entity;

/**
 * Generate And Store Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class GenerateAndStoreTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new GenerateAndStore();
        $this->mockRepo('Document', Document::class);

        $this->mockedSmServices['DocumentNamingService'] = m::mock(NamingService::class);
        $this->mockedSmServices[AuthorizationService::class] = m::mock(AuthorizationService::class);
        $this->mockedSmServices['DocumentGenerator'] = m::mock(DocumentGenerator::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->references = [
            Entity\Licence\Licence::class => [
                111 => m::mock(Entity\Licence\Licence::class)
            ]
        ];

        $this->categoryReferences = [
            11 => m::mock(Entity\System\Category::class)
        ];

        $this->subCategoryReferences = [
            22 => m::mock(Entity\System\SubCategory::class)
        ];

        parent::initReferences();
    }

    public function testHandleCommandWithDispatch(): void
    {
        $data = [
            'template' => 'foo',
            'query' => [
                'licence' => 111
            ],
            'knownValues' => ['foo' => 'bar'],
            'description' => 'Foo bar/ [cake]',
            'category' => 11,
            'subCategory' => 22,
            'licence' => 111,
            'dispatch' => true,
            'disableBookmarks' => false
        ];

        $command = \Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore::create($data);

        $mockUser = m::mock()->shouldReceive('getId')->andReturn(123)->once()->getMock();
        $mockIdentity = m::mock()
            ->shouldReceive('getUser')
            ->andReturn($mockUser)
            ->twice()
            ->getMock();
        $this->mockedSmServices[AuthorizationService::class]
            ->shouldReceive('getIdentity')
            ->andReturn($mockIdentity)
            ->twice();

        $document = m::mock();
        $file = m::mock();
        $file->shouldReceive('getIdentifier')
            ->andReturn(12345)
            ->shouldReceive('getSize')
            ->andReturn(100);

        $this->mockedSmServices['DocumentGenerator']->shouldReceive('generateFromTemplate')
            ->once()
            ->with('foo', ['licence' => 111, 'user' => 123], ['foo' => 'bar'], false)
            ->andReturn($document)
            ->shouldReceive('uploadGeneratedContent')
            ->once()
            ->with($document, '/foo/bar/cake.rtf')
            ->andReturn($file);

        $this->mockedSmServices['DocumentNamingService']->shouldReceive('generateName')
            ->once()
            ->with(
                'Foo bar/ [cake]',
                'rtf',
                $this->categoryReferences[11],
                $this->subCategoryReferences[22],
                $this->references[Entity\Licence\Licence::class][111]
            )
            ->andReturn('/foo/bar/cake.rtf');

        $result = new Result();
        $result->addMessage('DispatchDocument');

        unset($data['template']);
        unset($data['query']);
        unset($data['knownValues']);
        unset($data['dispatch']);
        unset($data['disableBookmarks']);

        $data['identifier'] = 12345;
        $data['size'] = 100;
        $data['filename'] = '/foo/bar/cake.rtf';
        $data['user'] = 123;

        $this->expectedSideEffect(DispatchDocument::class, $data, $result);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'identifier' => 12345
            ],
            'messages' => [
                'DispatchDocument',
                '/foo/bar/cake.rtf Document created'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommand(): void
    {
        $data = [
            'template' => 'foo',
            'query' => [
                'licence' => 111
            ],
            'knownValues' => ['foo' => 'bar'],
            'description' => 'Foo bar/ [cake]',
            'category' => 11,
            'subCategory' => 22,
            'licence' => 111,
            'disableBookmarks' => true
        ];

        $command = \Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore::create($data);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser->getId')
            ->once()
            ->andReturn(123);

        $document = m::mock();
        $file = m::mock();
        $file->shouldReceive('getIdentifier')
            ->andReturn(12345)
            ->shouldReceive('getSize')
            ->andReturn(100);

        $this->mockedSmServices['DocumentGenerator']->shouldReceive('generateFromTemplate')
            ->once()
            ->with('foo', ['licence' => 111, 'user' => 123], ['foo' => 'bar'], true)
            ->andReturn($document)
            ->shouldReceive('uploadGeneratedContent')
            ->once()
            ->with($document, '/foo/bar/cake.rtf')
            ->andReturn($file);

        $this->mockedSmServices['DocumentNamingService']->shouldReceive('generateName')
            ->once()
            ->with(
                'Foo bar/ [cake]',
                'rtf',
                $this->categoryReferences[11],
                $this->subCategoryReferences[22],
                $this->references[Entity\Licence\Licence::class][111]
            )
            ->andReturn('/foo/bar/cake.rtf');

        $result = new Result();
        $result->addMessage('CreateDocument');

        unset($data['template']);
        unset($data['query']);
        unset($data['knownValues']);
        unset($data['disableBookmarks']);

        $data['identifier'] = 12345;
        $data['size'] = 100;
        $data['filename'] = '/foo/bar/cake.rtf';

        $this->expectedSideEffect(CreateDocument::class, $data, $result);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'identifier' => 12345
            ],
            'messages' => [
                'CreateDocument',
                '/foo/bar/cake.rtf Document created'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
