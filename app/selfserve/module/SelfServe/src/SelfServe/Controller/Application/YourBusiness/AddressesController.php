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
                                    'address',
                                    'contactType' => array(
                                        'properties' => 'id'
                                    )
                                ]
                            ],
                        ]
                    ],
                    'contactDetails' => [
                        'children' => [
                            'phoneContacts',
                            'address',
                            'contactType' => array(
                                'properties' => 'id'
                            )
                        ]
                    ],
                ]
            ],
        ],
    ];

    protected $dataMap = null;

    protected $phoneTypes = Array(
        'business' => 'phone_t_tel',
        'home' => 'phone_t_home',
        'mobile' => 'phone_t_mobile'
    );

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
        $bundle = array(
            'properties' => array(),
            'children' => array(
                'licence' => array(
                    'children' => array(
                        'licenceType' => array(
                            'properties' => array(
                                'id'
                            )
                        ),
                        'organisation' => array(
                            'children' => array(
                                'type' => array(
                                    'properties' => array(
                                        'id'
                                    )
                                )
                            )
                        )
                    )
                )
            )
        );

        $allowedLicTypes = [self::LICENCE_TYPE_STANDARD_NATIONAL, self::LICENCE_TYPE_STANDARD_INTERNATIONAL];
        $allowedOrgTypes = [self::ORG_TYPE_REGISTERED_COMPANY, self::ORG_TYPE_LLP];

        $data = $this->makeRestCall('Application', 'GET', ['id' => $this->getIdentifier()], $bundle);

        if (!in_array($data['licence']['licenceType']['id'], $allowedLicTypes)) {
            $form->remove('establishment');
            $form->remove('establishment_address');
        }

        if (!in_array($data['licence']['organisation']['type']['id'], $allowedOrgTypes)) {
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
            'contactType'    => 'ct_corr',
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

        foreach ($this->phoneTypes as $phoneType => $phoneRefName) {

            $phone = [
                'id'        => $data['contact']['phone_'.$phoneType.'_id'],
                'version'   => $data['contact']['phone_'.$phoneType.'_version'],
            ];

            if (!empty($data['contact']['phone_'.$phoneType])) {

                $phone['phoneNumber']           = $data['contact']['phone_'.$phoneType];
                $phone['phoneContactType']      = $phoneRefName;
                $phone['contactDetails']        = $correspondenceId;

                parent::save($phone, $service);

            } elseif ((int)$phone['id'] > 0) {
                $this->makeRestCall($service, 'DELETE', $phone);
            }
        }

        if (!empty($data['establishment'])) {

            $establishment = [
                'id'                    => $data['establishment']['id'],
                'version'               => $data['establishment']['version'],
                'contactType'    => 'ct_est',
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
                'contactType'    => 'ct_reg',
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

            if (!isset($contactDetails['contactType']['id'])) {
                continue;
            }

            $type = $contactDetails['contactType']['id'];

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
