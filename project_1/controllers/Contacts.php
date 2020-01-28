<?
require_once('models/User.php');
require_once('models/Contact.php');
require_once('models/ContactRecord.php');

class Contacts {
  // TODO: Database
  private $contacts_by_user = [
    1 => [
      [
        "id" => 1,
        "name" => 'John Smith',
        "records" => [
          [
            "type" => 'Phone',
            "value" => '555-867-5309'
          ],
          [
            "type" => 'Address',
            "value" => '123 Fake Street'
          ],
        ]
      ],
      [
        "id" => 2,
        "name" => 'Jane Smith',
        "records" => [
          [
            "type" => 'Phone',
            "value" => '555-867-5310'
          ],
          [
            "type" => 'Address',
            "value" => '124 Fake Street'
          ],
        ]
      ],
      [
        "id" => 3,
        "name" => 'John Doe',
        "records" => [
          [
            "type" => 'Phone',
            "value" => '555-867-5305'
          ],
        ]
      ],
      [
        "id" => 4,
        "name" => 'Tim Cook',
        "records" => [
          [
            "type" => 'Email',
            "value" => 'tim@apple.com'
          ],
        ]
      ],
      [
        "id" => 5,
        "name" => 'Tim Horton',
        "records" => [
          [
            "type" => 'Address',
            "value" => '12 Main Street'
          ],
        ]
      ],
      [
        "id" => 6,
        "name" => 'Ron Weasley',
        "records" => [
          [
            "type" => 'Phone',
            "value" => '123-456-7890'
          ],
        ]
      ],
      [
        "id" => 7,
        "name" => 'Travis Fletcher',
        "records" => [
          [
            "type" => 'Email',
            "value" => 'travis@tfletch.tech'
          ],
        ]
      ],
      [
        "id" => 8,
        "name" => 'Jeff Bezos',
        "records" => [
          [
            "type" => 'Email',
            "value" => 'JDog@amazon.gov'
          ],
        ]
      ],
      [
        "id" => 9,
        "name" => 'Sean Lento',
        "records" => [
          [
            "type" => 'Phone',
            "value" => '752-876-2309'
          ],
          [
            "type" => 'Email',
            "value" => 'shane_lentils@gmail.com'
          ],
        ]
      ],
      [
        "id" => 10,
        "name" => 'Blake Maislin',
        "records" => [
          [
            "type" => 'Phone',
            "value" => '444-444-4444'
          ],
        ]
      ],
    ],
  ];


  public function find_contacts_by_user($user){
    if(!isset($this->contacts_by_user[$user->id])){
      return [];
    }
    return array_map( [$this, '_fill_contact'], $this->contacts_by_user[$user->id]);
  }

  public function get_contact_by_id($user, $contact_id){
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

  private function _fill_contact($Icontact){
    $contact = new Contact();
    $contact->id = $Icontact['id'];
    $contact->name = $Icontact['name'];
    $contact->records = array_map([$this, '_fill_contact_record'], $Icontact['records']);
    return $contact;
  }

  private function _fill_contact_record($IcontactRecord){
    $contactRecord = new ContactRecord();
    $contactRecord->type = $IcontactRecord['type'];
    $contactRecord->value = $IcontactRecord['value'];
    return $contactRecord;
  }
}


?>