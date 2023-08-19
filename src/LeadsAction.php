<?php

namespace AMO;

use AmoCRM\Collections\ContactsCollection;
use AmoCRM\Collections\CustomFieldsValuesCollection;
use AmoCRM\Collections\Leads\LeadsCollection;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Exceptions\AmoCRMMissedTokenException;
use AmoCRM\Models\ContactModel;
use AmoCRM\Models\CustomFieldsValues\MultitextCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\MultitextCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueModels\MultitextCustomFieldValueModel;
use AmoCRM\Models\LeadModel;

class LeadsAction
{
    /**
     * @throws AmoCRMMissedTokenException
     */
    public function __construct($name, $price, $phone, $email)
    {
        $contactId = $this->createContact($name, $phone, $email);
        $this->createLeads($name, $price, $contactId);
        die(file_get_contents("templates/leads.phtml"));
    }

    public function createContact($name, $phone, $email): ?int
    {
        $apiClient = (new Provider())->returnApiClient();
        $contact = new ContactModel();
        $customFields = new CustomFieldsValuesCollection();

        $contact->setName($name);
        $phoneField = (new MultitextCustomFieldValuesModel())->setFieldCode('PHONE');
        $phoneField->setValues(
            (new MultitextCustomFieldValueCollection())
                ->add(
                    (new MultitextCustomFieldValueModel())
                        ->setEnum('WORK')
                        ->setValue($phone)
                )
        );
        $emailField = (new MultitextCustomFieldValuesModel())->setFieldCode('EMAIL');
        $emailField->setValues(
            (new MultitextCustomFieldValueCollection())
                ->add(
                    (new MultitextCustomFieldValueModel())
                        ->setEnum('WORK')
                        ->setValue($email)
                )
        );
        $customFields->add($phoneField);
        $customFields->add($emailField);
        $contact->setCustomFieldsValues($customFields);
        try {
            $contactModel = $apiClient->contacts()->addOne($contact);
        } catch (AmoCRMApiException $e) {
            printError($e);
            die;
        }
        return $contactModel->getId();
    }

    /**
     * @throws AmoCRMMissedTokenException
     */
    public function createLeads($name, $price, $contactId): void
    {
        $apiClient = (new Provider())->returnApiClient();
        $leadsService = $apiClient->leads();
        $lead = new LeadModel();
        $lead->setName($name)
            ->setPrice($price)
            ->setContacts(
                (new ContactsCollection())
                    ->add(
                        (new ContactModel())
                            ->setId($contactId)
                    )
            );

        $leadsCollection = new LeadsCollection();
        $leadsCollection->add($lead);

        try {
            $leadsCollection = $leadsService->add($leadsCollection);
        } catch (AmoCRMApiException $e) {
            printError($e);
            die;
        }
    }
}
