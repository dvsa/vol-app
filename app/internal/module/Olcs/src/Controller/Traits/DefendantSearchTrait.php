<?php

namespace Olcs\Controller\Traits;

use Common\Form\Elements\Types\EntitySearch;
use Common\Form\Elements\Types\OperatorSearch;
use Zend\Mvc\MvcEvent;

/**
 * Class DefendantSearchTrait
 * @package Olcs\Controller
 */
trait DefendantSearchTrait
{

    /**
     * Array to hold the entity data, as Edit needs to know what type of form
     * to build
     *
     * @var array
     */
    private $entity_data;


    /**
     * Gets a from from either a built or custom form config.
     * @param string $type
     * @return Form
     */
    protected function getForm($type)
    {
        $form = $this->getServiceLocator()->get('OlcsCustomForm')->createForm($type);

        $form = $this->processPostcodeLookup($form);

        $form = $this->processEntitySearchJourney($form);

        return $form;
    }

    /**
     * Process the entity lookup functionality
     *
     * @param Form $form
     * @return Form
     */
    private function processEntitySearchJourney($form)
    {

        $fieldsets = $form->getFieldsets();

        foreach ($fieldsets as $fieldset) {
            if ($fieldset instanceof EntitySearch) {
                $searchFieldset = $this->processEntitySearch($fieldset);
                if ($searchFieldset instanceof \Common\Form\Elements\Types\PersonSearch ||
                    $searchFieldset instanceof \Common\Form\Elements\Types\OperatorSearch) {
                    $elements = $searchFieldset->getElements();
                    foreach ($elements as $element) {
                        $fieldset->add($element);
                    }
                }
            }
        }

        return $form;
    }


    /**
     * Method to manipulate the form as neccessary
     *
     * @param object $fieldset
     * @param array $post
     * @return object
     */
    private function processEntitySearch($fieldset)
    {
        $this->setPersist(false);
        $request = $this->getRequest();

        $name = $fieldset->getName();

        $post = array();
        if ($request->isPost()) {
            $post = (array)$request->getPost();
        }

        // If we haven't posted a form, or we haven't clicked find person
        if (isset($post[$name]['lookupTypeSubmit'])
            && !empty($post[$name]['lookupTypeSubmit'])) {
            // get the relevant search form
            $searchFieldset = $this->processDefendantType($post);

        } elseif (isset($post[$name]['search'])
            && !empty($post[$name]['search'])) {
            // get the relevant results
            $searchFieldset = $this->processDefendantLookup($fieldset, $post);

        } elseif (isset($post[$name]['select'])
            && !empty($post[$name]['select'])) {
            // get the relevant entity and populate the relevant fields
            $searchFieldset = $this->processDefendantSelected($fieldset, $post);

        } elseif (isset($post[$name]['addNew'])
            && !empty($post[$name]['addNew'])) {
            // get the relevant entity and populate the relevant fields
            $searchFieldset = $this->processDefendantAddNew($fieldset, $post);

        } else {
            // add the search fieldset to ensure the relevant person/operator
            // form elements are present based on defType
            $searchFieldset = $this->processGetPreparedForm($post);
        }

        return $searchFieldset;
    }

    /**
     * Method called in the absence of any form buttons posting (e.g. on initial
     * GET requests and edit via GET
     *
     * @param array $post
     * @return object Fieldset
     */
    private function processGetPreparedForm($post)
    {
        $type = $this->getEntityType($post);
        $searchFieldset = $this->getSearchFieldsetbyEntityType($type, 'searchPerson', ['label' => 'Search for person']);

        if ($searchFieldset instanceof \Common\Form\Elements\Types\OperatorSearch) {
            $searchFieldset->remove('entity-list');
            $searchFieldset->remove('select');
        } elseif ($searchFieldset instanceof \Common\Form\Elements\Types\PersonSearch) {
            $searchFieldset->setLabel('Search for person');
            $searchFieldset->remove('person-list');
            $searchFieldset->remove('select');
        }
        $this->setPersist(true);
        return $searchFieldset;

    }

    /**
     * Gets the relevent search fieldset by type
     *
     * @param string $type Defendant type
     * @param array $options Options for fieldset
     * @param string $name Name to give the new fieldset
     *
     * @return object Fieldset
     */
    private function getSearchFieldsetbyEntityType($type, $name, $options)
    {

        switch($type) {
            case "defendant_type.operator":
                $fieldset = new \Common\Form\Elements\Types\OperatorSearch($name, $options);
                $fieldset->setAttributes(
                    array(
                        'type' => 'operator-search',
                    )
                );
                break;
            default:
                $fieldset = new \Common\Form\Elements\Types\PersonSearch($name, $options);
                $fieldset->setAttributes(
                    array(
                        'type' => 'person-search',
                    )
                );
                break;
        }

        return $fieldset;
    }

    /**
     * Method to process the defendant type
     *
     * @param array $post
     * @return \Common\Form\Elements\Types\PersonSearch
     */
    private function processDefendantType($post)
    {
        $this->setPersist(false);
        $type = $this->getEntityType($post);
        $search = $this->getSearchFieldsetbyEntityType($type, 'searchPerson', ['label' => 'Search for person']);

        if ($search instanceof \Common\Form\Elements\Types\OperatorSearch) {
            $search->remove('entity-list');
            $search->remove('select');
            $search->remove('operatorName');
        } elseif ($search instanceof \Common\Form\Elements\Types\PersonSearch) {
            $search->remove('person-list');
            $search->remove('select');
            $search->remove('personFirstname');
            $search->remove('personLastname');
            $search->remove('birthDate');
        }
        return $search;
    }

    /**
     * Method to get the type of entity
     *
     * @param type $post
     * @return type
     */
    private function getEntityType($post)
    {
        if (isset($post['defendant-details']['defType'])) {
            return $post['defendant-details']['defType'];
        } else {
            // check entity data
            $entityData = $this->getEntityData();
            return $entityData['defType'];
        }

    }
    /**
     * Method to process the entity tyoe search button
     *
     * @param object $fieldset
     * @param array $post
     * @return object fieldset
     */
    private function processDefendantLookup($fieldset, $post)
    {
        $this->setPersist(false);

        $type = $this->getEntityType($post);
        $search = $this->getSearchFieldsetbyEntityType($type, 'searchPerson', ['label' => 'Search for person']);

        if ($search instanceof \Common\Form\Elements\Types\OperatorSearch) {
            $name = trim($post[$fieldset->getName()]['operatorSearch']);
            $search = $this->processOperatorLookup($search, $name);
            $search->remove('operatorName');
        } elseif ($search instanceof \Common\Form\Elements\Types\PersonSearch) {
            $name = trim($post[$fieldset->getName()]['personSearch']);
            $search = $this->processPersonLookup($search, $name);
            $search->remove('personFirstname');
            $search->remove('personLastname');
            $search->remove('birthDate');
        }

        return $search;
    }

    /**
     * Method to process the person search form
     *
     * @param object Fieldset $search
     * @param string $name
     * @return object Fieldset
     */
    private function processPersonLookup($search, $name)
    {

        if (empty($name)) {
            $search->setMessages(
                array('Please enter a person name')
            );
        } else {
            $personList = $this->getPersonListForName($name);

            if (empty($personList)) {

                $search->setMessages(
                    array('No person found for name')
                );

            } else {
                $search->get('person-list')->setValueOptions(
                    $this->formatPersonsForSelect($personList)
                );

            }

        }
        return $search;
    }

    /**
     * Method to process the operator search form
     *
     * @param object Fieldset $search
     * @param string $name
     * @return object Fieldset
     */
    private function processOperatorLookup($search, $name)
    {
        if (empty($name)) {
            $search->setMessages(
                array('Please enter an operator name')
            );
        } else {
            $data['name'] = $name;

            $entityList = $this->getEntityListForName($data, 'OrganisationSearch');

            if (empty($entityList)) {

                $search->setMessages(
                    array('No operator found for name')
                );

            } else {
                $search->get('entity-list')->setValueOptions(
                    $this->formatEntitiesForSelect($entityList)
                );

            }

        }
        return $search;
    }

    /**
     * Method to process the person selected
     *
     * @param object $fieldset
     * @param array $post
     * @return object fieldset
     */
    private function processDefendantSelected($fieldset, $post)
    {
        $this->setPersist(false);

        $type = $this->getEntityType($post);
        $search = $this->getSearchFieldsetbyEntityType($type, 'searchPerson', ['label' => 'Search for person']);

        if ($search instanceof \Common\Form\Elements\Types\OperatorSearch) {
            $search->remove('entity-list');
            $search->remove('select');

            $entity = $this->getOperatorById($post[$fieldset->getName()]['entity-list']);

        } elseif ($search instanceof \Common\Form\Elements\Types\PersonSearch) {
            $search->remove('person-list');
            $search->remove('select');

            $entity = $this->getPersonById($post[$fieldset->getName()]['person-list']);

        }

        $fieldValues =  array_merge($post[$fieldset->getName()], $entity);
        $this->setFieldValue($fieldset->getName(), $fieldValues);

        return $search;
    }

    /**
     * Method to process the add new button
     *
     * @param object $fieldset
     * @param array $post
     * @return object fieldset
     */
    private function processDefendantAddNew($fieldset, $post)
    {
        $this->setPersist(false);

        $type = $this->getEntityType($post);
        $search = $this->getSearchFieldsetbyEntityType($type, 'searchPerson', ['label' => 'Search for person']);

        // just remember the defType, but clear the rest of the fieldset
        $defData = ['defType' => $post['defendant-details']['defType']];
        $this->setFieldValue($fieldset->getName(), $defData);

        $search->remove('person-list');
        $search->remove('entity-list');
        $search->remove('select');
        $search->remove('search');
        $search->remove('operatorSearch');
        $search->remove('personSearch');

        return $search;
    }

    /**
     * Method to retrieve the results of a search by name
     *
     * @param string $name
     * @return array
     */
    private function getPersonListForName($name)
    {
        $data['name'] = $name;
        $results = $this->makeRestCall('DefendantSearch', 'GET', $data);

        return $results['Results'];
    }

    /**
     * Method to retrieve the results of a search by name
     *
     * @param array $data search params
     * @param string RestController name
     * @return array
     */
    private function getEntityListForName($data, $restController)
    {
        $results = $this->makeRestCall($restController, 'GET', $data);
        return $results['Results'];
    }

    /**
     * Method to format the person list result into format suitable for select
     * dropdown
     *
     * @param array $personList
     * @return array
     */
    private function formatPersonsForSelect($personList)
    {
        $result = [];
        if (is_array($personList)) {
            foreach ($personList as $person) {
                $birthDate = new \DateTime($person['date_of_birth']);
                $result[$person['id']] = trim(
                    $person['familyName'] .
                    ',  ' . $person['forename'] .
                    '     (b. ' . $birthDate->format('d-M-Y')
                ) . ')';
            }
        }

        return $result;
    }

    /**
     * Method to format the entity list result into format suitable for select
     * dropdown
     *
     * @param array $entityList
     * @return array
     */
    private function formatEntitiesForSelect($entityList)
    {
        $result = [];
        if (is_array($entityList)) {
            foreach ($entityList as $entity) {
                $result[$entity['id']] = trim(
                    $entity['name']
                );
            }
        }

        return $result;
    }

    /**
     * Method to format a person details from db result into form field array
     * structure
     *
     * @param array $personDetails
     * @return array
     * @todo get date of birth to prepopulate form
     */
    private function formatPerson($personDetails)
    {
        $result['personFirstname'] = $personDetails['forename'];
        $result['personLastname'] = $personDetails['familyName'];

        $result['birthDate'] = $personDetails['birthDate'];

        return $result;
    }

    /**
     * Method to perform a final look up on the person selected.
     *
     * @param integer $id
     * @return array
     */
    private function getPersonById($id)
    {
        $result = $this->makeRestCall('Person', 'GET', ['id' => $id]);

        if ($result) {
            return $this->formatPerson($result);
        }
        return [];
    }

    /**
     * Method to perform a final look up on the entity selected.
     *
     * @param integer $id
     * @return array
     */
    private function getOperatorById($id)
    {
        $result = $this->makeRestCall('Organisation', 'GET', ['id' => $id]);

        if ($result) {
            return $this->formatOperator($result);
        }
        return [];
    }

    /**
     * Method to format an operator details from db result into form field array
     * structure
     *
     * @param array $person_details
     * @return array`
     */
    private function formatOperator($entityDetails)
    {
        $result['operatorName'] = $entityDetails['name'];

        return $result;
    }

    /**
     * Sets the entity data
     *
     * @param array $data
     * @return \Olcs\Controller\DefendantSearchController
     */
    public function setEntityData($data)
    {
        $this->entity_data = $data;
        return $this;
    }

    /**
     * Gets the entity data
     *
     * @return array
     */
    public function getEntityData()
    {
        return $this->entity_data;
    }
}
