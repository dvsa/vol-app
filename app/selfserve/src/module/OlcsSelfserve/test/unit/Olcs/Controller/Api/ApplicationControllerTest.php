<?php
namespace unit\Olcs\Controller;

use \OlcsCommon\Controller\AbstractRestfulController;
use \OlcsCommon\Controller\AbstractHttpControllerTestCase;
use \Mockery as m;

class ApplicationControllerTest extends AbstractHttpControllerTestCase
{
    protected $traceError = true;

    public function setUp($noConfig = false)
    {
        $this->setApplicationConfig(\Olcs\Bootstrap::getServiceManager()->get('ApplicationConfig'));

        parent::setUp(true);

        $transactionMock =  m::mock('Olcs\Controller\Plugin\DoctrineTransaction');
        $transactionMock->shouldReceive('setController');
        $transactionMock->shouldReceive('__invoke')
            ->once()
            ->with(m::type('callable'))
            ->andReturnUsing(function ($func) {
                $return = call_user_func($func, $this);
                return $return ?: true;
            });

        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->get('ControllerPluginManager')->setService('transactional', $transactionMock);

        $this->basicRequestBody = array(
            'receivedAt' => '2013-12-03T00:00:00+0000',
            'licence' => array(
                'goodsOrPsv' => 'goods',
                'licenceType' => 'Restricted',
                'tradeType' => 'Gen haulier / distribution',
                'tradingNames' => array(
                    'Pellentesque Ipsum',
                    'Porta Pharetra',
                ),
                'trafficArea' => 'North West of England',
                'operator' => array(
                    'version' => 1,
                    'operatorId' => '6',
                    'operatorName' => 'John Smith Haulage Ltd.',
                    'entityType' => 'Registered Company',
                    'owners' => array(9, 10),
                    'registeredAddress' => array(
                        'version' => 1,
                        'country' => 'GB',
                        'line1' => 'Cool Street 1',
                        'line2' => '',
                        'line3' => '',
                        'line4' => '',
                        'postcode' => '21354',
                        'town' => 'Leeds',
                    ),
                    'registeredCompanyNumber' => 'abc123',
                ),
            ),
        );
    }

    private function assembleServices(array $requestBody)
    {
        $applicationParams = $requestBody;
        $licenceParams = $applicationParams['licence'];
        $operatorParams = $licenceParams['operator'];
        $registeredAddressParams = $operatorParams['registeredAddress'];

        unset($operatorParams['registeredAddress']);

        $applicationParams['licence'] = 1;
        $applicationParams['trafficArea'] = $licenceParams['trafficArea'];
        $licenceParams['operator'] = empty($operatorParams['operatorId']) ? 2 : $operatorParams['operatorId'];
        $registeredAddressParams = array(
            'contactDetailsType' => 'Registered',
            'operator' => $licenceParams['operator'],
            'address' => $registeredAddressParams,
        );

        // Mocking of services

        $licenceServiceMock = $this->mockNativeService('LicenceServiceFactory', 'Olcs\Service\LicenceService');
        $licenceServiceMock->shouldReceive('createLicence')
            ->once()
            ->with($licenceParams)
            ->andReturn(1);

        if (empty($operatorParams['operatorId'])) {
            $organisationServiceMock = $this->mockNativeService(
                'OrganisationServiceFactory',
                'Olcs\Service\OrganisationService'
            );
            $organisationServiceMock->shouldReceive('createOrganisation')
                ->once()
                ->with($operatorParams)
                ->andReturn(2);

            $addressServiceMock = $this->mockNativeService('AddressServiceFactory', 'Olcs\Service\AddressService');
            $addressServiceMock->shouldReceive('createContactDetails')
                ->once()
                ->with($registeredAddressParams)
                ->andReturn(3);
        } else {
            $organisationServiceMock = $this->mockNativeService(
                'OrganisationServiceFactory',
                'Olcs\Service\OrganisationService'
            );
            $organisationServiceMock->shouldReceive('updateOrganisationByData')
                ->once()
                ->with($operatorParams)
                ->andReturnNull();

            $addressServiceMock = $this->mockNativeService('AddressServiceFactory', 'Olcs\Service\AddressService');
            $addressServiceMock->shouldReceive('updateContactDetailsByData')
                ->once()
                ->with($registeredAddressParams)
                ->andReturnNull();
        }

        $applicationServiceMock = $this->mockNativeService(
            'ApplicationServiceFactory',
            'Olcs\Service\ApplicationService'
        );
        $applicationServiceMock->shouldReceive('createApplication')
            ->once()
            ->with($applicationParams)
            ->andReturn(4);
    }

    public function testCreationRequest()
    {
        $this->assembleServices($this->basicRequestBody);

        // Doing the request

        $this->dispatchBody('/api/application', 'POST', $this->basicRequestBody);

        // Asserting the request went well

        $this->assertControllerClass('ApplicationController');
        $this->assertActionName('create');

        $this->assertResponseStatusCode(201);
        $this->assertRedirectTo('/api/application/4');
        $this->assertResponseHeaderContains('Content-Type', 'application/json; charset=utf-8');

        $content = json_decode($this->getResponse()->getContent(), true);

        $this->assertEquals($content, array(
            'applicationId' => 4,
        ), "API response doesn't match the expected result");
    }


    public function testCreationNewOrganisationRequest()
    {
        $alternateRequestBody = $this->basicRequestBody;
        unset($alternateRequestBody['licence']['operator']['operatorId']);
        unset($alternateRequestBody['licence']['operator']['version']);
        unset($alternateRequestBody['licence']['operator']['registeredAddress']['version']);

        $this->assembleServices($alternateRequestBody);

        // Doing the request

        $this->dispatchBody('/api/application', 'POST', $alternateRequestBody);

        // Asserting the request went well

        $this->assertResponseStatusCode(201);
        $this->assertRedirectTo('/api/application/4');
        $this->assertResponseHeaderContains('Content-Type', 'application/json; charset=utf-8');

        $content = json_decode($this->getResponse()->getContent(), true);

        $this->assertEquals($content, array(
            'applicationId' => 4,
        ), "API response doesn't match the expected result");
    }
}
