<?
require_once('models/User.php');
require_once('models/Contact.php');
require_once('models/ContactRecord.php');
require_once('utilities/Database.php');

class Contacts {
  // TODO: Database
  private array $contacts_by_user;
  private array $contacts;
  
  // Dirty, dirty way to hold auto_increment counts until I can switch to MySQL
  private array $schema;

  private $conn;


  public function find_contacts_by_user(User $user): array {
    $sth = $this->conn->prepare('
      SELECT 
        c.contact_id,
        c.user_id,
        c.first_name,
        c.last_name,
        c.notes,
        cr.contact_record_id,
        cr.value,
        crt.contact_record_type_id,
        crt.name as contact_record_type_name
      FROM contacts c
      LEFT JOIN contact_records cr
        USING(contact_id)
      LEFT JOIN contact_record_types crt
        USING(contact_record_type_id)
      WHERE user_id = ?
      ORDER BY last_name
    ');

    $sth->bind_param("i", $user->id);
    $sth->execute();

    $result = $sth->get_result()->fetch_all(MYSQLI_ASSOC);

    // Collect results by contact and format as IContacts
    $contacts;
    foreach($result as $row) {
      $contacts[$row['contact_id']] ??= [
        "id"      => $row['contact_id'],
        "user_id" => $row['user_id'],
        "name"    => $row['first_name'] . ' ' . $row['last_name'],
        "records" => []
      ];


      if($row['contact_record_id']){
        array_push(
          $contacts[$row['contact_id']]['records'],
          [
            "id"  => $row["contact_record_id"],
            "type"  => $row["contact_record_type_name"],
            "value" => $row["value"]
          ]
        );
      }

    }

    return (array_map([$this, '_fill_contact'], $contacts));
  }

  public function get_contact_by_id($contact_id): ?Contact {
    $sth = $this->conn->prepare('
      SELECT 
        c.contact_id,
        c.user_id,
        c.first_name,
        c.last_name,
        c.notes,
        cr.contact_record_id,
        cr.value,
        crt.contact_record_type_id,
        crt.name as contact_record_type_name
      FROM contacts c
      LEFT JOIN contact_records cr
        USING(contact_id)
      LEFT JOIN contact_record_types crt
        USING(contact_record_type_id)
      WHERE contact_id = ?
      ORDER BY last_name
    ');

    $sth->bind_param("i", $contact_id);
    $sth->execute();

    $result = $sth->get_result()->fetch_all(MYSQLI_ASSOC);

    // Collect results by contact and format as IContacts
    $contact;
    foreach($result as $row) {
      $contact ??= [
        "id"      => $row['contact_id'],
        "user_id" => $row['user_id'],
        "name"    => $row['first_name'] . ' ' . $row['last_name'],
        "records" => []
      ];

      if($row['contact_record_id']){
        array_push(
          $contact['records'],
          [
            "id"    => $row["contact_record_id"],
            "type"  => $row["contact_record_type_name"],
            "value" => $row["value"]
          ]
        );
      }
    }

    return $this->_fill_contact($contact);
  }

  public function create(array $IContact){
    // Enforce stuff
    if(!isset($IContact['user_id'])){
      throw new Exception('Created contacts must be supplied a user_id');
    }

    # Fill with post data
    $contact = $this->_fill_contact($IContact);

    # Insert main contact record
    $sth = $this->conn->prepare('
      INSERT INTO contacts
      SET
        user_id = ?,
        first_name = ?,
        last_name = ?
    ');

    $names = explode(' ', $IContact['name'], 2);

    $sth->bind_param(
      'iss',
      $IContact['user_id'],
      $names[0],
      $names[1]
    );


    $sth->execute();

    $contact_id = $sth->insert_id;

    // lol
    $manual_map_because_im_lazy = [
      'Email'   => 1,
      'Phone'   => 2,
      'Address' => 3
    ];

    foreach($IContact['records'] as $record){
      $sth = $this->conn->prepare('
        INSERT INTO contact_records
        SET
          contact_id = ?,
          contact_record_type_id = ?,
          value = ?
      ');

      $sth->bind_param(
        'iss',
        $contact_id,
        $manual_map_because_im_lazy[$record['type']],
        $record['value']
      );

      $sth->execute();

      error_log($sth->error);
    }

    return $this->get_contact_by_id($contact_id);
  }

  public function update(array $IContact){
    $contact = $this->get_contact_by_id($IContact['id']);
    $contact_id = $contact->id;

    # Update main contact record
    $sth = $this->conn->prepare('
      UPDATE contacts
      SET
        first_name = ?,
        last_name = ?
      WHERE contact_id = ?
    ');

    $names = explode(' ', $IContact['name'], 2);

    $sth->bind_param(
      'ssi',
      $names[0],
      $names[1],
      $contact_id
    );

    $sth->execute();
    error_log($sth->error);

    // Delete the existing contact records
    $sth = $this->conn->prepare('
      DELETE FROM contact_records
      WHERE contact_id = ?
    ');
    $sth->bind_param('i', $contact_id);
    $sth->execute();
    error_log($sth->error);

    // lol
    $manual_map_because_im_lazy = [
      'Email'   => 1,
      'Phone'   => 2,
      'Address' => 3
    ];

    var_dump($IContact['records']);

    // Upload the new contact records
    foreach($IContact['records'] as $record){
      $sth = $this->conn->prepare('
        INSERT INTO contact_records
        SET
          -- This will be null on new records, thus autogenerated
          contact_record_id = ?,

          contact_id = ?,
          contact_record_type_id = ?,
          value = ?
      ');

      $sth->bind_param(
        'siis',
        $record['id'],
        $contact_id,
        $manual_map_because_im_lazy[$record['type']],
        $record['value']
      );

      $sth->execute();

      error_log($sth->error);
      var_dump($sth->error);
    }

    return $this->get_contact_by_id($contact_id);
  }

  public function delete(array $IContact) {
    $contact = $this->get_contact_by_id($IContact['id']);

    $sth = $this->conn->prepare('
      DELETE FROM contacts
      WHERE contact_id = ?
    ');
    $sth->bind_param('i', $contact->id);
    $sth->execute();

    return $contact;
  }

  private function _fill_contact_record(array $IcontactRecord): ContactRecord {
    $contactRecord = new ContactRecord();
    $contactRecord->id = $IcontactRecord['id'] ?? null;
    $contactRecord->type = $IcontactRecord['type'];
    $contactRecord->value = $IcontactRecord['value'];
    return $contactRecord;
  }

  private function _fill_contact(array $IContact): Contact {
    $contact = new Contact();
    $contact->id = intval($IContact['id']);
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

  public function __construct(){
    $databaseController = new Database();
    $this->conn = $databaseController->get_connection();
    [$this->contacts_by_user, $this->contacts] = $this->_fill_contacts_by_user(json_decode(file_get_contents('data/contacts.json'), true));
    $this->schema = json_decode(file_get_contents('data/schema.json'), true);
  }

}


?>