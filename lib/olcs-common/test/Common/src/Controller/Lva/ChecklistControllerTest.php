<?php

namespace CommonTest\Controller\Lva;

use Common\Controller\Continuation\ChecklistController;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Query\ContinuationDetail\LicenceChecklist;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Hamcrest\Core\IsEqual;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use LmcRbacMvc\Service\AuthorizationService;

class ChecklistControllerTest extends MockeryTestCase
{
    public $mockNiTextTranslationUtil;
    public $mockAuthService;
    public $mockFormServiceManager;
    public $mockTranslationHelper;
    public $request;
    public $sut;
    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->mockNiTextTranslationUtil = m::mock(NiTextTranslation::class);
        $this->mockAuthService = m::mock(AuthorizationService::class);
        $this->mockFormServiceManager = m::mock(FormServiceManager::class);
        $this->mockTranslationHelper = m::mock(TranslationHelperService::class);

        $this->mockController(ChecklistController::class, [
            $this->mockNiTextTranslationUtil,
            $this->mockAuthService,
            $this->mockFormServiceManager,
            $this->mockTranslationHelper,
        ]);

        $this->mockTranslationHelper->shouldReceive('translate')
            ->andReturnUsing(
                static fn($input) => $input
            );
    }

    protected function mockController(string $className, array $constructorParams = []): void
    {
        $this->request = m::mock(\Laminas\Http\Request::class)->makePartial();

        // If constructor params are provided, pass them to the mock, otherwise mock without them
        if ($constructorParams !== []) {
            $this->sut = m::mock($className, $constructorParams)
                ->makePartial()
                ->shouldAllowMockingProtectedMethods();
        } else {
            $this->sut = m::mock($className)
                ->makePartial()
                ->shouldAllowMockingProtectedMethods();
        }

        $this->sut
            ->shouldReceive('getRequest')
            ->andReturn($this->request);
    }

    public function testUsersAction(): void
    {
        $continuationId = 99;

        $this->sut->shouldReceive('params')->with('continuationDetailId')->once()->andReturn($continuationId);

        $this->sut->shouldReceive('handleQuery')
            ->once()
            ->with(IsEqual::equalTo(LicenceChecklist::create(['id' => $continuationId])))
            ->andReturn($this->aMockResultWithUserData());

        $view = $this->sut->usersAction();
        $this->assertEquals('layouts/simple', $view->getTemplate());

        $userSectionView = $view->getChildren()[0];
        $this->assertEquals('pages/continuation-section', $userSectionView->getTemplate());

        $expected =  [
            'licNo' => '1234',
            'data' =>
                 [
                    0 =>
                         [
                            0 =>
                                 [
                                    'value' => 'continuations.users-section.table.name',
                                    'header' => true,
                                ],
                            1 =>
                                 [
                                    'value' => 'continuations.users-section.table.email',
                                    'header' => true,
                                ],
                            2 =>
                                 [
                                    'value' => 'continuations.users-section.table.permission',
                                    'header' => true,
                                ],
                        ],
                    1 =>
                         [
                            0 =>
                                 [
                                    'value' => 'Test1 Test1',
                                ],
                            1 =>
                                 [
                                    'value' => 'test1@test.com',
                                ],
                            2 =>
                                 [
                                    'value' => 'role.operator',
                                ],
                        ],
                    2 =>
                         [
                            0 =>
                                 [
                                    'value' => 'Test2 Test2',
                                ],
                            1 =>
                                 [
                                    'value' => 'test2@test.com',
                                ],
                            2 =>
                                 [
                                    'value' => 'role.operator',
                                ],
                        ],
                ],
            'totalMessage' => 'continuations.users-section-header',
            'totalCount' => 2,
        ];

        $this->assertEquals($expected, $userSectionView->getVariables());
    }

    /**
     * @return m\LegacyMockInterface|m\MockInterface
     */
    private function aMockResultWithUserData()
    {
        return m::mock()->shouldReceive('isOk')->andReturn(true)->getMock()->shouldReceive('getResult')->andReturn(
            [
                'licence' =>
                    [
                        'licNo' => '1234',
                        'organisation' =>
                            [
                                'organisationUsers' =>
                                    [
                                        [
                                            'user' =>
                                                [
                                                    'contactDetails' =>
                                                        [
                                                            'emailAddress' => 'test1@test.com',
                                                            'person' =>
                                                                [
                                                                    'familyName' => 'Test1',
                                                                    'forename' => 'Test1',
                                                                ],
                                                        ],
                                                    'id' => 543,
                                                    'roles' =>
                                                        [
                                                            [
                                                                'description' => 'Operator',
                                                                'id' => 27,
                                                                'role' => 'operator',
                                                            ],
                                                        ],
                                                ]
                                        ],
                                        [
                                            'user' =>
                                                [
                                                    'contactDetails' =>
                                                        [
                                                            'emailAddress' => 'test2@test.com',
                                                            'person' =>
                                                                [
                                                                    'familyName' => 'Test2',
                                                                    'forename' => 'Test2',
                                                                ],
                                                        ],
                                                    'id' => 544,
                                                    'roles' =>
                                                        [
                                                            [
                                                                'description' => 'Operator',
                                                                'id' => 27,
                                                                'role' => 'operator',
                                                            ],
                                                        ],

                                                ]
                                        ]
                                    ]
                            ]
                    ],

            ]
        )->getMock();
    }
}
