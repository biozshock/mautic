<?php

namespace MauticPlugin\MauticCrmBundle\Integration\Salesforce\CampaignMember;

use MauticPlugin\MauticCrmBundle\Integration\Salesforce\Object\Contact;
use MauticPlugin\MauticCrmBundle\Integration\Salesforce\Object\Lead;

class Organizer
{
    /**
     * @var array
     */
    private $records;

    /**
     * @var array<int, Lead>
     */
    private $leads = [];

    /**
     * @var array<int, Contact>
     */
    private $contacts = [];

    /**
     * ObjectOrganizer constructor.
     */
    public function __construct(array $records)
    {
        $this->records = $records;

        $this->organize();
    }

    /**
     * @return array<int, Lead>
     */
    public function getLeads(): array
    {
        return $this->leads;
    }

    /**
     * @return array<int, int>
     */
    public function getLeadIds(): array
    {
        return array_keys($this->leads);
    }

    /**
     * @return array<int, Contact>
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * @return array<int, int>
     */
    public function getContactIds()
    {
        return array_keys($this->contacts);
    }

    private function organize()
    {
        foreach ($this->records as $campaignMember) {
            $object    = !empty($campaignMember['LeadId']) ? 'Lead' : 'Contact';
            $objectId  = !empty($campaignMember['LeadId']) ? $campaignMember['LeadId'] : $campaignMember['ContactId'];
            $isDeleted = ($campaignMember['IsDeleted']) ? true : false;

            switch ($object) {
                case Lead::OBJECT:
                    $this->leads[$objectId] = new Lead($objectId, $campaignMember['CampaignId'], $isDeleted);
                    break;

                case Contact::OBJECT:
                    $this->contacts[$objectId] = new Contact($objectId, $campaignMember['CampaignId'], $isDeleted);
                    break;
            }
        }
    }
}
