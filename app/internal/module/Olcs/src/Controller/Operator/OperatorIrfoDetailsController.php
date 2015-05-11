<?php

/**
 * Operator Irfo Details Controller
 */
namespace Olcs\Controller\Operator;

/**
 * Operator Irfo Details Controller
 */
class OperatorIrfoDetailsController extends OperatorController
{
    /**
     * Identifier name
     *
     * @var string
     */
    protected $identifierName = 'organisation';

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'Organisation';

    /**
     * Holds the form name
     *
     * @var string
     */
    protected $formName = 'IrfoDetails';

    /**
     * Holds the inline scripts
     *
     * @var array
     */
    protected $inlineScripts = ['trading-names'];

    /**
     * Data map
     *
     * @var array
    */
    protected $dataMap = array(
        'main' => array(
            'mapFrom' => array(
                'fields',
            )
        )
    );

    /**
     * Holds the Data Bundle
     *
     * @var array
     */
    protected $dataBundle = array(
        'children' => array(
            'tradingNames',
            'irfoContactDetails' => [
                'children' => [
                    'address' => [
                        'children' => [
                            'countryCode',
                        ]
                    ],
                    'phoneContacts' => [
                        'children' => [
                            'phoneContactType',
                        ]
                    ]
                ]
            ]
        )
    );

    /**
     * @var string
     */
    protected $section = 'irfo_details';

    /**
     * @var string
     */
    protected $subNavRoute = 'operator_irfo';

    /**
     * Map the data on load
     *
     * @param array $data
     * @return array
     */
    public function processLoad($data)
    {
        if (!empty($data['id'])) {
            // set id for HTML element
            $data['idHtml'] = $data['id'];
        }

        if (!empty($data['irfoContactDetails']['address'])) {
            // set address fields
            $data['address'] = $data['irfoContactDetails']['address'];
        }

        if (!empty($data['irfoContactDetails']['emailAddress'])) {
            // set contact fields
            $data['contact']['email'] = $data['irfoContactDetails']['emailAddress'];
        }

        if (!empty($data['irfoContactDetails']['phoneContacts'])) {
            $phoneFields = $this->getServiceLocator()->get('BusinessServiceManager')
                ->get('Lva\PhoneContact')
                ->mapPhoneFieldsFromDb($data['irfoContactDetails']['phoneContacts']);

            $data['contact'] = array_merge($data['contact'], $phoneFields);
        }

        return parent::processLoad($data);
    }

    /**
     * Complete section and save
     *
     * @param array $data
     * @return \Zend\Http\Response
     */
    public function processSave($data)
    {
        // get existing record
        $existingData = $this->loadCurrent();

        // merge with the submitted data
        $data['fields'] = array_merge($existingData, $data['fields']);

        // save the changes
        $response = $this->getServiceLocator()->get('BusinessServiceManager')
            ->get('Operator\IrfoDetails')
            ->process(
                [
                    'id' => $this->getIdentifier(),
                    'data' => $data['fields'],
                    'address' => $data['address'],
                    'contact' => $data['contact'],
                ]
            );

        if ($response->isOk()) {
            $this->addSuccessMessage('Saved successfully');
        } else {
            $this->addErrorMessage('Sorry; there was a problem. Please try again.');
        }

        return $this->redirectToIndex();
    }

    /**
     * Redirect to the edit form
     *
     * @return \Zend\Http\Response
     */
    public function indexAction()
    {
        return $this->redirectToIndex();
    }

    /**
     * Simple redirect to the edit form
     *
     * @return \Zend\Http\Response
     */
    public function redirectToIndex()
    {
        return $this->redirectToRoute(
            'operator/irfo/details',
            ['action' => 'edit'],
            ['code' => '303'],
            true
        );
    }
}
