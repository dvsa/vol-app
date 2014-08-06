<?php

/**
 * An abstract form controller that all ordinary OLCS controllers inherit from
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Shaun <shaun.lizzio@valtech.co.uk>
 */
namespace Olcs\Controller;

use Common\Form\Elements\Types\EntitySearch;
use Zend\Mvc\MvcEvent;

/**
 * An abstract form controller that all ordinary OLCS controllers inherit from
 *
 * @author Shaun <shaun.lizzio@valtech.co.uk>
 */
class DefendantSearchController extends CaseController
{

    /**
     * Gets a from from either a built or custom form config.
     * @param type $type
     * @return type
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
                if ($searchFieldset instanceof \Common\Form\Elements\Types\PersonSearch) {
                    $elements = $searchFieldset->getElements();
                    foreach($elements as $element) {
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
        $searchFieldset = false;
        // If we haven't posted a form, or we haven't clicked find person
        if (isset($post[$name]['lookupTypeSubmit'])
            && !empty($post[$name]['lookupTypeSubmit'])) {
            // get the relevant search form
            $searchFieldset = $this->processDefendantType($fieldset, $post);

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
            $searchFieldset = new \Common\Form\Elements\Types\PersonSearch('searchPerson', array('label' => 'Select'));
            $searchFieldset->setAttributes(
                array(
                    'type' => 'person-search',
                )
            );
            $searchFieldset->setLabel('Search for person');
            $searchFieldset->remove('person-list');
            $searchFieldset->remove('select');

            $this->setPersist(true);
        }

        return $searchFieldset;
    }

    private function getSearchFieldsetFromPost()
    {

    }

    /**
     * Method to process the defendant type
     *
     * @param object $fieldset
     * @param array $post
     * @return \Common\Form\Elements\Types\PersonSearch
     */
    private function processDefendantType($fieldset, $post)
    {
        $this->setPersist(false);

        $search = new \Common\Form\Elements\Types\PersonSearch('searchPerson', array('label' => 'Select'));
        $search->setAttributes(
            array(
                'type' => 'person-search',
            )
        );

        $search->setLabel('Search for person');

        $search->remove('person-list');
        $search->remove('select');
        $search->remove('personFirstname');
        $search->remove('personLastname');
        $search->remove('dateOfBirth');

        return $search;
    }

    /**
     * Method to process the person search button
     *
     * @param object $fieldset
     * @param array $post
     * @return \Common\Form\Elements\Types\PersonSearch
     */
    private function processDefendantLookup($fieldset, $post)
    {
        $this->setPersist(false);

        $search = new \Common\Form\Elements\Types\PersonSearch('searchPerson', array('label' => 'Select'));
        $search->setAttributes(
            array(
                'type' => 'person-search',
            )
        );

        $personName = trim($post[$fieldset->getName()]['personSearch']);

        if (empty($personName)) {
            $search->setMessages(
                array('Please enter a person name')
            );
        } else {

            $personList = $this->getPersonListForName($personName);

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

        $search->setLabel('Search for person');
        $search->remove('personFirstname');
        $search->remove('personLastname');
        $search->remove('dateOfBirth');
        return $search;
    }

    /**
     * Method to process the person selected
     *
     * @param object $fieldset
     * @param array $post
     * @return \Common\Form\Elements\Types\PersonSearch
     */
    private function processDefendantSelected($fieldset, $post)
    {

        $this->setPersist(false);

        $search = new \Common\Form\Elements\Types\PersonSearch('searchPerson', array('label' => 'Select'));
        $search->setAttributes(
            array(
                'type' => 'person-search',
            )
        );
        $search->setLabel('Search for person');
        $search->remove('person-list');
        $search->remove('select');

        $person = $this->getPersonById($post[$fieldset->getName()]['person-list']);

        $fieldValues =  array_merge($post[$fieldset->getName()], $person);
        $this->setFieldValue($fieldset->getName(), $fieldValues);

        return $search;
    }

    /**
     * Method to process the add new button
     *
     * @param object $fieldset
     * @param array $post
     * @return \Common\Form\Elements\Types\PersonSearch
     */
    private function processDefendantAddNew($fieldset, $post)
    {
        $this->setPersist(false);

        $search = new \Common\Form\Elements\Types\PersonSearch('searchPerson', array('label' => 'Select'));
        $search->setAttributes(
            array(
                'type' => 'person-search',
            )
        );

        $search->setLabel('Search for person');
        $search->remove('person-list');
        $search->remove('select');
        $search->remove('search');
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
     * Method to format the person list result into format suitable for select
     * dropdown
     *
     * @param array $person_list
     * @return array
     */
    private function formatPersonsForSelect($person_list)
    {
        $result = [];
        if (is_array($person_list)) {
            foreach ($person_list as $person) {
                $dob = new \DateTime($person['date_of_birth']);
                $result[$person['id']] = trim(
                    $person['surname'] .
                    ',  ' . $person['first_name'] .
                    '     (b. ' . $dob->format('d-M-Y')
                ) . ')';
            }
        }

        return $result;
    }

    /**
     * Method to format a person details from db result into form field array
     * structure
     *
     * @param type $person_details
     * @return type
     * @todo get date of birth to prepopulate form
     */
    private function formatPerson($person_details)
    {
        $result['personFirstname'] = $person_details['firstName'];
        $result['personLastname'] = $person_details['surname'];

        $result['dateOfBirth'] = $person_details['dateOfBirth'];

        return $result;
    }

    /**
     * Method to perform a final look up on the person selected.
     *
     * @param type $id
     * @return type
     * @todo Call relevent backend service to get person details
     */
    private function getPersonById($id)
    {
        $result = $this->makeRestCall('Person', 'GET', ['id' => $id]);

        if ($result) {
            return $this->formatPerson($result);
        }
        return [];
    }
}
