<?
require_once('models/User.php');
require_once('models/Contact.php');
require_once('models/ContactRecord.php');

class Contacts {
  // TODO: Database
  private array $contacts_by_user;
  private array $contacts;
  
  // Dirty, dirty way to hold auto_increment counts until I can switch to MySQL
  private array $schema;


  public function find_contacts_by_user(User $user): array {
    if(!isset($this->contacts_by_user[$user->id])){
      return [];
    }
    return $this->contacts_by_user[$user->id];
  }

  public function get_contact_by_id($contact_id): ?Contact {
    $maybe_contact = array_filter($this->contacts, function($var) use($contact_id) { 
      return $var->id == $contact_id; 
    });


    if(empty($maybe_contact)){ return null; }
    return array_pop($maybe_contact);
  }

  public function create(array $IContact){
    // Enforce stuff
    if(!isset($IContact['user_id'])){
      throw new Exception('Created contacts must be supplied a user_id');
    }

    // Fill with ID from schema
    $IContact['id'] = $this->schema['contact_id_auto_increment']++;
    
    # Fill with post data
    $contact = $this->_fill_contact($IContact);

    return $this->contacts_by_user[$IContact['user_id']][] = $contact;
  }

  public function update(array $IContact){
    $contact = $this->get_contact_by_id($IContact['id']);

    $contact->name = $IContact['name'];
    $contact->records = array_map([$this, '_fill_contact_record'], $IContact['records'] ?? []);

    $this->contacts[$contact->id] = $contact;

    return $contact;
  }

  public function delete(array $IContact) {
    $contact = $this->get_contact_by_id($IContact['id']);

    unset($this->contacts_by_user[$IContact['user_id']][$IContact['id']]);

    return $contact;
  }

  private function _fill_contact_record(array $IcontactRecord): ContactRecord {
    $contactRecord = new ContactRecord();
    $contactRecord->type = $IcontactRecord['type'];
    $contactRecord->value = $IcontactRecord['value'];
    return $contactRecord;
  }

  private function _fill_contact(array $IContact): Contact {
    $contact = new Contact();
    $contact->id = $IContact['id'];
    $contact->name = $IContact['name'];
    $contact->records = array_map([$this, '_fill_contact_record'], $IContact['records']);
    return $contact;
  }

  private function _fill_contacts_by_user(array $IContactsByUser): array {
    $contacts_by_user = [];
    foreach($IContactsByUser as $user_id => $contacts) {
      foreach (array_map([$this, '_fill_contact'], $contacts) as $contact) {
        $contacts[$contact->id] = $contact;
        $contacts_by_user[$user_id][$contact->id] = &$contacts[$contact->id];
      }
    }

    return [$contacts_by_user, $contacts];
  }

  public function persist(){
    file_put_contents('data/contacts.json', json_encode($this->contacts_by_user));
    file_put_contents('data/schema.json', json_encode($this->schema));
  }

  public function __construct(){
    [$this->contacts_by_user, $this->contacts] = $this->_fill_contacts_by_user(json_decode(file_get_contents('data/contacts.json'), true));
    $this->schema = json_decode(file_get_contents('data/schema.json'), true);
  }

}


?>