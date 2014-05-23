<?php

/**
 * Addresses Controller
 *
 * @author Jakub Igla <jakub.igla@valtech.co.uk>
 */
namespace SelfServe\Controller\Application\YourBusiness;

/**
 * Addresses Controller
 *
 * @author Jakub Igla <jakub.igla@valtech.co.uk>
 */
class AddressesController extends YourBusinessController
{
    const MAIN_CONTACT_DETAILS_TYPE = 'correspondence';

    /**
     * Set the service for the "Free" save behaviour
     *
     * @var string
     */
    protected $service = 'ContactDetails';

    protected $dataBundle = [
        'children' => [
            'licence' => [
                'children' => [
                    'organisation' => [
                        'children' => [
                            'contactDetails' => [
                                'children' => [
                                    'address'
                                ]
                            ],
                        ]
                    ],
                    'contactDetails' => [
                        'children' => [
                            'phoneContacts',
                            'address',
                        ]
                    ],
                ]
            ],
        ],
    ];


    /**
     * Render the section form
     *
     * @return Response
     */
    public function indexAction()
    {
        $view = $this->getViewModel();
        return $this->renderSection($view);
    }

    protected function alterForm($form)
    {
        $bundle = [
            'properties' => [],
            'children' => array(
                'licence' => array(
                    'properties' => ['licenceType'],
                    'children' => array(
                        'organisation' => array(
                            'properties' => ['organisationType']
                        )
                    )
                )
            )
        ];

        $allowedLicTypes = ['standard-national', 'standard-international'];
        $allowedOrgTypes = ['org_type.lc', 'org_type.llp'];

        $data = $this->makeRestCall('Application', 'GET', ['id' => $this->getIdentifier()], $bundle);

        if (array_search($data['licence']['licenceType'], $allowedLicTypes) === false) {
            $form->remove('establishment');
            $form->remove('establishment_address');
        }

        if (array_search($data['licence']['organisation']['organisationType'], $allowedOrgTypes) === false) {
            $form->remove('registered_office');
            $form->remove('registered_office_address');
        }

        return $form;
    }

    /**
     * Save data
     *
     * @param array $data
     * @param string $service
     * @return array
     */
    protected function save($data, $service = null)
    {
        $licence = $this->getLicenceData();

        $correspondence = [
            'id'                    => $data['correspondence']['id'],
            'version'               => $data['correspondence']['version'],
            'contactDetailsType'    => 'correspondence',
            'licence'               => $licence['id'],
            'emailAddress'          => $data['contact']['email'],
            'addresses'             => [
                'address' => $data['correspondence_address'],
            ]
        ];

        //persist correspondence details
        $correspondenceDetails = parent::save($correspondence);

        $correspondenceId = (int)$data['correspondence']['id'] > 0
            ? $data['correspondence']['id']
            : $correspondenceDetails['id'];

        //process phones
        $service = 'PhoneContact';
        $phoneTypes = ['business', 'home', 'mobile'];

        foreach ($phoneTypes as $phoneType) {

            $phone = [
                'id'        => $data['contact']['phone_'.$phoneType.'_id'],
                'version'   => $data['contact']['phone_'.$phoneType.'_version'],
            ];

            if (!empty($data['contact']['phone_'.$phoneType])) {

                $phone['number']            = $data['contact']['phone_'.$phoneType];
                $phone['type']              = $phoneType;
                $phone['contactDetails']    = $correspondenceId;

                parent::save($phone, $service);

            } else {
                $this->makeRestCall($service, 'DELETE', $phone);
            }
        }

        if (!empty($data['establishment'])) {

            $establishment = [
                'id'                    => $data['establishment']['id'],
                'version'               => $data['establishment']['version'],
                'contactDetailsType'    => 'establishment',
                'licence'               => $licence['id'],
                'addresses'             => [
                    'address' => $data['establishment_address'],
                ]
            ];

            parent::save($establishment);
        }

        if (!empty($data['registered_office'])) {

            $organisation = $this->getOrganisationData(['id']);

            $registeredOffice = [
                'id'                    => $data['registered_office']['id'],
                'version'               => $data['registered_office']['version'],
                'contactDetailsType'    => 'registered_office',
                'organisation'          => $organisation['id'],
                'addresses'             => [
                    'address' => $data['registered_office_address'],
                ]
            ];

            parent::save($registeredOffice);
        }

        return $correspondenceDetails;
    }

    /**
     * Load data for the form
     *
     * @param int $id
     * @return array
     */
    protected function load($id)
    {
        $this->service = 'Application';
        $app = parent::load($id);

        //init
        $data = [
            'contact' => [
                'phone-validator' => true
            ]
        ];

        $contactDetailsMerge = array_merge(
            $app['licence']['contactDetails'],
            $app['licence']['organisation']['contactDetails']
        );

        foreach ($contactDetailsMerge as $contactDetails) {

            $type = $contactDetails['contactDetailsType'];

            $data[$type] = [
                'id' => $contactDetails['id'],
                'version' => $contactDetails['version'],
            ];

            $data[$type . '_address'] = $contactDetails['address'];

            if ($type == self::MAIN_CONTACT_DETAILS_TYPE) {

                $data['contact']['email'] = $contactDetails['emailAddress'];

                foreach ($contactDetails['phoneContacts'] as $phoneContact) {

                    $phoneType = $phoneContact['type'];

                    $data['contact']['phone_'.$phoneType]               = $phoneContact['number'];
                    $data['contact']['phone_'.$phoneType.'_id']         = $phoneContact['id'];
                    $data['contact']['phone_'.$phoneType.'_version']    = $phoneContact['version'];
                }

            }

        }

        return $data;
    }
}
