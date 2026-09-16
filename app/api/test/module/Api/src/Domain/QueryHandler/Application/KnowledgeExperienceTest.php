<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Application;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\Application\KnowledgeExperience;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Api\Entity\User\Role;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Transfer\Query\Application\KnowledgeExperience as Qry;
use LmcRbacMvc\Identity\IdentityInterface;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;

final class KnowledgeExperienceTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new KnowledgeExperience();

        $this->mockRepo('Application', ApplicationRepo::class);

        $this->mockedSmServices[AuthorizationService::class]
            = m::mock(AuthorizationService::class);

        parent::setUp();
    }

    public function testHandleQueryWithDocuments(): void
    {
        $applicationId = 111;

        $query = Qry::create([
            'id' => $applicationId,
        ]);

        $document = m::mock()
            ->shouldReceive('serialize')
            ->with([])
            ->once()
            ->andReturn([
                'description' => 'evidence.pdf',
            ])
            ->getMock();

        $application = m::mock(Application::class);

        $application
            ->shouldReceive('getApplicationDocuments')
            ->with('category', 'subCategory')
            ->once()
            ->andReturn([$document]);

        $application
            ->shouldReceive('serialize')
            ->with(['licence'])
            ->once()
            ->andReturn([
                'id' => $applicationId,
                'knowledgeExperienceEvidenceUploaded' => '1',
                'knowledgeExperienceOlat' => 'N',
            ]);

        $this->repoMap['Application']
            ->shouldReceive('fetchUsingId')
            ->with($query, Query::HYDRATE_OBJECT)
            ->once()
            ->andReturn($application);

        $this->repoMap['Application']
            ->shouldReceive('getCategoryReference')
            ->with(Category::CATEGORY_APPLICATION)
            ->once()
            ->andReturn('category');

        $this->repoMap['Application']
            ->shouldReceive('getSubCategoryReference')
            ->with(
                SubCategory::DOC_SUB_CATEGORY_KNOWLEDGE_EXPERIENCE_EVIDENCE_DIGITAL
            )
            ->once()
            ->andReturn('subCategory');

        $identity = m::mock(IdentityInterface::class)
            ->shouldReceive('getUser')
            ->andReturn(
                m::mock(User::class)
                    ->shouldReceive('getRoles')
                    ->andReturn(new ArrayCollection([]))
                    ->getMock()
            )
            ->getMock();

        $this->mockedSmServices[AuthorizationService::class]
            ->shouldReceive('getIdentity')
            ->once()
            ->andReturn($identity);

        $expected = [
            'id' => $applicationId,
            'knowledgeExperienceEvidenceUploaded' => '1',
            'knowledgeExperienceOlat' => 'N',
            'documents' => [
                [
                    'description' => 'evidence.pdf',
                ],
            ],
        ];

        $this->assertEquals(
            $expected,
            $this->sut->handleQuery($query)->serialize()
        );
    }

    public function testHandleQueryForReadOnlyInternalUser(): void
    {
        $applicationId = 111;

        $query = Qry::create([
            'id' => $applicationId,
        ]);

        $application = m::mock(Application::class);

        $application
            ->shouldReceive('getApplicationDocuments')
            ->never();

        $application
            ->shouldReceive('serialize')
            ->with(['licence'])
            ->once()
            ->andReturn([
                'id' => $applicationId,
                'knowledgeExperienceEvidenceUploaded' => '1',
                'knowledgeExperienceOlat' => 'N',
            ]);

        $this->repoMap['Application']
            ->shouldReceive('fetchUsingId')
            ->with($query, Query::HYDRATE_OBJECT)
            ->once()
            ->andReturn($application);

        $this->repoMap['Application']
            ->shouldReceive('getCategoryReference')
            ->never();

        $this->repoMap['Application']
            ->shouldReceive('getSubCategoryReference')
            ->never();

        $role = m::mock(Role::class);
        $role
            ->shouldReceive('getRole')
            ->once()
            ->andReturn(Role::ROLE_INTERNAL_LIMITED_READ_ONLY);

        $user = m::mock(User::class);
        $user
            ->shouldReceive('getRoles')
            ->once()
            ->andReturn(new ArrayCollection([$role]));

        $identity = m::mock(IdentityInterface::class);
        $identity
            ->shouldReceive('getUser')
            ->once()
            ->andReturn($user);

        $this->mockedSmServices[AuthorizationService::class]
            ->shouldReceive('getIdentity')
            ->once()
            ->andReturn($identity);

        $expected = [
            'id' => $applicationId,
            'knowledgeExperienceEvidenceUploaded' => '1',
            'knowledgeExperienceOlat' => 'N',
            'documents' => null,
        ];

        $this->assertEquals(
            $expected,
            $this->sut->handleQuery($query)->serialize()
        );
    }
}
