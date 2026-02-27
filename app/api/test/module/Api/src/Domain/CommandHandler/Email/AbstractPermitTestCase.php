<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\CommandHandlerInterface;
use Dvsa\Olcs\Api\Domain\Repository\FeeType as FeeTypeRepo;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Api\Service\Permits\Fees\DaysToPayIssueFeeProvider;
use Dvsa\Olcs\Email\Service\TemplateRenderer;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use Laminas\I18n\Translator\Translator;

/**
 * Abstract permit email tester
 */
abstract class AbstractPermitTestCase extends AbstractCommandHandlerTestCase
{
    /**
     * @var string
     */
    public $userEmail;
    /**
     * @var string
     */
    public $orgEmail1;
    /**
     * @var string
     */
    public $orgEmail2;
    /**
     * @var string[]
     */
    public $orgEmails;
    /**
     * @var (\Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails & \Mockery\MockInterface)
     */
    public $contactDetails;
    /** @var string */
    protected $commandClass = 'changeMe';

    /** @var string */
    protected $commandHandlerClass = 'changeMe';

    /** @var string */
    protected $template = 'changeMe';

    /** @var string */
    protected $subject = 'changeMe';

    /** @var string */
    protected $permitApplicationRepo = 'changeMe';

    /** @var string */
    protected $applicationEntityClass = 'changeMe';

    /** @var CommandHandlerInterface */
    protected $sut;

    /** @var mixed */
    protected $applicationEntity;

    /** @var CommandInterface */
    protected $commandEntity;

    /** @var string */
    protected $applicationRef;

    /** @var Organisation */
    protected $organisation;

    /** @var User */
    protected $userEntity;

    /** @var int */
    protected $permitAppId;

    public function setUp(): void
    {
        $this->permitAppId = 77;

        $this->applicationRef = 'OC1234567/1234';

        $this->commandEntity = $this->commandClass::create(['id' => $this->permitAppId]);

        $this->sut = new $this->commandHandlerClass();

        $this->mockRepo($this->permitApplicationRepo, '\\Dvsa\\Olcs\\Api\\Domain\\Repository\\' . $this->permitApplicationRepo);

        $this->mockRepo('FeeType', FeeTypeRepo::class);

        $this->mockedSmServices = [
            TemplateRenderer::class => m::mock(TemplateRenderer::class),
            'translator' => m::mock(Translator::class),
            'PermitsFeesDaysToPayIssueFeeProvider' => m::mock(DaysToPayIssueFeeProvider::class),
        ];

        $this->userEmail = 'email1@test.com';
        $this->orgEmail1 = 'orgEmail1@test.com';
        $this->orgEmail2 = 'orgEmail2@test.com';
        $this->orgEmails = [$this->orgEmail1, $this->orgEmail2];

        $this->contactDetails = m::mock(ContactDetails::class);

        $this->userEntity = m::mock(User::class);

        $this->organisation = m::mock(Organisation::class);

        $this->applicationEntity = m::mock($this->applicationEntityClass);
        $this->applicationEntity->shouldReceive('getApplicationRef')->withNoArgs()->andReturn($this->applicationRef);
        $this->applicationEntity->shouldReceive('getId')->withNoArgs()->andReturn($this->permitAppId);
        $this->applicationEntity->shouldReceive('getLicence->getOrganisation')
            ->atMost(2)
            ->atLeast(1)
            ->withNoArgs()
            ->andReturn($this->organisation);

        $this->repoMap[$this->permitApplicationRepo]
            ->shouldReceive('fetchUsingId')
            ->with($this->commandEntity)
            ->once()
            ->andReturn($this->applicationEntity);

        parent::setUp();
    }
}
