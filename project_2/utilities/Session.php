<?
// Manage user login & protection.
//
// @usage
//   require_once(utils/Session.php):
//
// @exposes
//   $_SESSION[]
//     - $_SESSION['user_id']

session_start();

if(!isset($_SESSION['user_id'])){
  header('Location: login.php');
  die();
}

// Check for session staleness
if($_SESSION['last_used'] && (time() - 600 > $_SESSION['last_used'])){

  // Prevents a redirect loop on logout
  unset($_SESSION['last_used']);

  header('Location: logout.php');
  die();
}

$_SESSION['last_used'] = time();

?>