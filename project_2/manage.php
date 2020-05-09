<?
require_once('utilities/Session.php');

require_once('controllers/Animals.php');
require_once('controllers/Users.php');

$AnimalsController = new Animals;
$UsersController = new Users;

$user = $UsersController->get_user_by_id($_SESSION['user_id']);

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

function delete_adoption($data){ global $AnimalsController; 
  echo json_encode($AnimalsController->delete_adoption($data));
}

function create_adoption($data){ global $AnimalsController; 
  echo json_encode($AnimalsController->create_adoption($data));
}



?>

