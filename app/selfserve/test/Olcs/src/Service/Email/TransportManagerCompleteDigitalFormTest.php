<?php

/**
 *
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
namespace OlcsTest\Service\Email;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;

/**

 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class TransportManagerCompleteDigitalFormTest extends MockeryTestCase
{
    protected $sm;
    protected $sut;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();

        $this->sut = new \Olcs\Service\Email\TransportManagerCompleteDigitalForm();
        $this->sut->setServiceLocator($this->sm);
    }

    public function testSend()
    {
        $mockTmaEntityService = m::mock();
        $this->sm->setService('Entity\TransportManagerApplication', $mockTmaEntityService);

        $mockUrlHelper = m::mock();
        $this->sm->setService('Helper\Url', $mockUrlHelper);

        $mockTranslationHelper = m::mock();
        $this->sm->setService('Helper\Translation', $mockTranslationHelper);

        $mockViewRenderer = m::mock();
        $this->sm->setService('ViewRenderer', $mockViewRenderer);

        $mockEmail = m::mock();
        $this->sm->setService('Email', $mockEmail);

        $tmaData = [
            'transportManager' => [
                'homeCd' => [
                    'emailAddress' => 'mary@example.com',
                    'person' => [
                        'forename' => 'Mary'
                    ]

                ]
            ],
            'application' => [
                'id' => 733,
                'isVariation' => 1,
                'licence' => [
                    'licNo' => 'LIC001',
                    'organisation' => [
                        'name' => 'Acme Ltd'
                    ],
                    'translateToWelsh' => 'N'
                ]
            ],

        ];

        $mockTmaEntityService->shouldReceive('getContactApplicationDetails')
            ->with(65)
            ->once()
            ->andReturn($tmaData);

        $mockUrlHelper->shouldReceive('fromRoute')
            ->with(
                'lva-variation/transport_manager_details/action',
                ['action' => 'edit-details', 'application' => 733, 'child_id' => 65],
                ['force_canonical' => true],
                true
            )->once()
            ->andReturn('A-URL');

        $mockTranslationHelper->shouldReceive('translateReplace')
            ->with(
                'markup-email-transport-manager-complete-digital-form',
                [
                    $tmaData['transportManager']['homeCd']['person']['forename'],
                    $tmaData['application']['licence']['organisation']['name'],
                    $tmaData['application']['licence']['licNo'],
                    $tmaData['application']['id'],
                    'A-URL'
                ],
                'N'
            )->once()
            ->andReturn('CONTENT');

        $mockTranslationHelper->shouldReceive('translate')
            ->with('email.transport-manager-complete-digital-form.subject', 'N')
            ->once()
            ->andReturn('SUBJECT');

        $mockViewRenderer->shouldReceive('render')
            ->with(m::type('\Zend\View\Model\ViewModel'))
            ->once()
            ->andReturn('RENDERED');

        $mockEmail->shouldReceive('sendEmail')
            ->with(
                'donotreply@otc.gsi.gov.uk',
                'OLCS do not reply',
                $tmaData['transportManager']['homeCd']['emailAddress'],
                'SUBJECT',
                'RENDERED',
                true
            )->once();

        $this->sut->send(65);
    }
}
