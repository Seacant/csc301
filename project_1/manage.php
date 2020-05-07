<?
require_once('utilities/Session.php');

require_once('controllers/Contacts.php');
require_once('controllers/Users.php');

$contactsController = new Contacts;
$usersController = new Users;

$user = $usersController->get_user_by_id($_SESSION['user_id']);

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
  error_log($e->getMessage());
  http_response_code(400);
  echo json_encode([
    'error' => $e->getMessage()
  ]);
}

function create_contact($data) { global $contactsController;
  echo json_encode($contactsController->create($data));

  http_response_code(201);
}

function update_contact($data){ global $contactsController;
  echo json_encode($contactsController->update($data));
}
function delete_contact($data){ global $contactsController; 
  echo json_encode($contactsController->delete($data));
}

function get_contact_by_id($data) { global $contactsController;
  echo json_encode($contactsController->get_contact_by_id($_GET['id']));
}

?>

