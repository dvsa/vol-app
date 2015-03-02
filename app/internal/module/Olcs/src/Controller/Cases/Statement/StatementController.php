<?php

/**
 * Case Statement Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\Controller\Cases\Statement;

// Olcs
use Olcs\Controller as OlcsController;
use Olcs\Controller\Traits as ControllerTraits;
use Olcs\Controller\Interfaces\CaseControllerInterface;

/**
 * Case Statement Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class StatementController extends OlcsController\CrudAbstract implements CaseControllerInterface
{
    use ControllerTraits\CaseControllerTrait;
    use ControllerTraits\GenerateActionTrait;

    /**
     * Identifier name
     *
     * @var string
     */
    protected $identifierName = 'statement';

    /**
     * Table name string
     *
     * @var string
     */
    protected $tableName = 'statement';

    /**
     * Holds the form name
     *
     * @var string
     */
    protected $formName = 'statement';

    /**
     * The current page's extra layout, over and above the
     * standard base template, a sibling of the base though.
     *
     * @var string
     */
    protected $pageLayout = 'case-section';

    /**
     * For most case crud controllers, we use the layout/case-details-subsection
     * layout file. Except submissions.
     *
     * @var string
     */
    protected $pageLayoutInner = 'layout/case-details-subsection';

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'Statement';

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represneted by a single navigation id.
     */
    protected $navigationId = 'case_details_statements';

    /**
     * Holds an array of variables for the
     * default index list page.
     */
    protected $listVars = [
        'case',
    ];

    /**
     * @var array
     */
    protected $inlineScripts = ['table-actions'];

    /**
     * Data map
     *
     * @var array
    */
    protected $dataMap = array(
        'main' => array(
            'mapFrom' => array(
                'fields'
            )
        )
    );

    /**
     * Holds the isAction
     *
     * @var boolean
    */
    protected $isAction = false;

    /**
     * Holds the Data Bundle
     *
     * @var array
     */
    protected $dataBundle = array(
        'children' => array(
            'contactType',
            'statementType',
            'requestorsContactDetails' => array(
                'children' => array(
                    'address' => array(
                        'children' => array(
                            'countryCode'
                        )
                    ),
                    'person'
                )
            ),
            'case'
        )
    );

    /**
     * @var int $licenceId cache of licence id for a given case
     */
    protected $licenceId;

    /**
     * Transforms the data prior to saving.
     *
     * @param array $data
     * @return array
     */
    public function processSave($data)
    {
        unset($data['requestorsAddress']['searchPostcode']);

        // set up person
        $person = [];
        $person['id'] = $data['fields']['personId'];
        $person['version'] = $data['fields']['personVersion'];
        $person['forename'] = $data['fields']['requestorsForename'];
        $person['familyName'] = $data['fields']['requestorsFamilyName'];

        // set up contactDetails
        $contactDetails = [];
        $contactDetails['id'] = $data['fields']['contactDetailsId'];
        $contactDetails['version'] = $data['fields']['contactDetailsVersion'];
        $contactDetails['contactType'] = $data['fields']['contactDetailsType'];
        $contactDetails['person'] = $person;
        $contactDetails['address'] = $data['requestorsAddress'];

        $data['fields']['requestorsContactDetails'] = $contactDetails;

        return parent::processSave($data);
    }

    /**
     * Map the data on load
     *
     * @param array $data
     * @return array
     */
    public function processLoad($data)
    {
        if (isset($data['requestorsContactDetails'])) {
            $address = $data['requestorsContactDetails']['address'];

            // set up contactDetails
            $data['contactDetailsId'] = $data['requestorsContactDetails']['id'];
            $data['contactDetailsVersion'] = $data['requestorsContactDetails']['version'];

            $data['personId'] = $data['requestorsContactDetails']['person']['id'];
            $data['personVersion'] = $data['requestorsContactDetails']['person']['version'];
            $data['requestorsForename'] = $data['requestorsContactDetails']['person']['forename'];
            $data['requestorsFamilyName'] = $data['requestorsContactDetails']['person']['familyName'];

            $data = parent::processLoad($data);

            $data['requestorsAddress'] = $address;

            $data['requestorsAddress']['countryCode'] = $address['countryCode']['id'];
        } else {
            $data = parent::processLoad($data);
        }

        return $data;
    }

    /**
     * Route for document generate action redirects
     * @see Olcs\Controller\Traits\GenerateActionTrait
     * @return string
     */
    protected function getDocumentGenerateRoute()
    {
        return 'case_licence_docs_attachments/entity/generate';
    }

    /**
     * Route params for document generate action redirects
     * @see Olcs\Controller\Traits\GenerateActionTrait
     * @return array
     */
    protected function getDocumentGenerateRouteParams()
    {
        return [
            'case' => $this->getFromRoute('case'),
            'licence' => $this->getLicenceIdForCase(),
            'entityType' => 'statement',
            'entityId' => $this->getFromRoute('statement')
        ];
    }

    /**
     * Gets licence id from route or backend, caching it in member variable
     */
    protected function getLicenceIdForCase()
    {
        if (is_null($this->licenceId)) {
            $this->licenceId = $this->getQueryOrRouteParam('licence');
            if (empty($this->licenceId)) {
                $case = $this->getCase();
                $this->licenceId = $case['licence']['id'];
            }
        }
        return $this->licenceId;
    }
}
