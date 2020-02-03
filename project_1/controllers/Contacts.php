<?
require_once('models/User.php');
require_once('models/Contact.php');
require_once('models/ContactRecord.php');

class Contacts {
  // TODO: Database
  private $contacts_by_user;

  public function __construct(){
    $this->contacts_by_user = json_decode(file_get_contents('data/contacts.json'), true);
  }


  public function find_contacts_by_user(User $user): array {
    if(!isset($this->contacts_by_user[$user->id])){
      return [];
    }
    return array_map( [$this, '_fill_contact'], $this->contacts_by_user[$user->id]);
  }

  public function get_contact_by_id($user, $contact_id): ?Contact {
    if(!isset($this->contacts_by_user[$user->id])){
      return null;
    }
    $contacts = $this->find_contacts_by_user($user);

    $maybe_contact = array_filter($contacts, function($var) use($contact_id) { 
      return $var->id == $contact_id; 
    });


    if(empty($maybe_contact)){ return null; }
    return array_pop($maybe_contact);
  }

  private function _fill_contact(array $Icontact): Contact {
    $contact = new Contact();
    $contact->id = $Icontact['id'];
    $contact->name = $Icontact['name'];
    $contact->records = array_map([$this, '_fill_contact_record'], $Icontact['records']);
    return $contact;
  }

  private function _fill_contact_record(array $IcontactRecord): ContactRecord {
    $contactRecord = new ContactRecord();
    $contactRecord->type = $IcontactRecord['type'];
    $contactRecord->value = $IcontactRecord['value'];
    return $contactRecord;
  }
}


?>