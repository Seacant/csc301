<?
require_once('controllers/Contacts.php');
require_once('controllers/Users.php');

$contactsController = new Contacts;
$usersController = new Users;

// TODO: Login
$user = $usersController->get_user_by_id(1);

try {
  if(!isset($_GET['operation'])){
    throw new Exception("You must specify a valid operation");
  }

  // Delegate the ajax call to the appropriate handler
  call_user_func(
    $_GET['operation'],
    json_decode(file_get_contents('php://input'), true)
  );
} catch (Exception $e) {
  echo json_encode([
    'error' => $e->getMessage()
  ]);
}

function not_found_error(){
  echo json_encode([
    'error' => 'You must specify a valid operation'
  ]);
}

function create_contact($data) { global $contactsController;

  echo json_encode($contactsController->create($data));
  $contactsController->persist();

  http_response_code(201);
}

function update_contact($data){ global $contactsController;
  echo json_encode($contactsController->update($data));
  $contactsController->persist();
}
function delete_contact($data){ global $contactsController; 
  echo json_encode($contactsController->delete($data));
  $contactsController->persist();
}

?>

