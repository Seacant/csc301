<?
require_once('utilities/Session.php');

require_once('controllers/Users.php');
require_once('controllers/Animals.php');

$usersController = new Users;
$animalsController = new Animals;

$user = $usersController->get_user_by_id($_SESSION['user_id']);

// Redirect unpermissioned users
if(!$user->is_manager()){
  header('Location: index.php');
  die();
}

if(isset($_GET['action'])){
  $action = $_GET['action'];
  // Handle impersonations
  if($action == 'impersonate' && isset($_GET['impersonate_user']) && $_GET['impersonate_user'] != ''){
    $_SESSION['user_id'] = $_GET['impersonate_user'];
    header('Location: index.php');
    die();
  }
  if(
    $action == 'add_animal' &&
    isset($_GET['animal_type_id']) && isset($_GET['name']) && isset($_GET['breed']) &&
    $_GET['animal_type_id'] != '' && $_GET['name'] != '' && $_GET['breed'] != ''
  ){
    $animalsController->create_animal($_GET);
  }
  if($user->is_admin() && $action == 'delete' && isset($_GET['delete_user']) && $_GET['delete_user'] != ''){
    $usersController->delete_user_by_id($_GET['delete_user']);
  }
}

$users = $usersController->list_users();

// Only include users whose permission is lower than yours
$users = array_filter($users, function($inner_user) use ($user) {
  return $inner_user->role > $user->role;
})

?>


<!DOCTYPE html>
<html>
  <head>
    <meta charset='utf-8' />
    <title>Admin Area</title>

    <!-- Bootstrap & Dark mode -->
    <link rel="stylesheet" href="/styles/bootstrap.min.css">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
  </head>

  <body>
  <main class='container'>
      <div> 
        <a class='text-secondary' href='index.php'>Go Back</a>
      </div>
      <div class="jumbotron">
        <form class='row'>
          <div class='col-sm-10'>
            <label for='impersonate_user' class='h4'>Impersonate User</label>

            <div class='row' style='padding-left: 60px'>
              <select name='impersonate_user' class='form-control bg-dark text-white col-11'>
                <option value=''>-- Select a User --</option>
                <? foreach($users as $Cuser): ?>
                <option value='<?=$Cuser->id?>'><?=$Cuser->name?></option>
                <? endforeach ?>
              </select>
              <button class='btn btn-submit col-1' name='action' value='impersonate'>Submit</button>
            </div>
          </div>
        </form>
        <hr/>
        <? if ($user->is_admin()): ?>
        <form class='row'>
          <div class='col-sm-10'>
            <label for='delete_user' class='h4'>Delete User</label>

            <div class='row' style='padding-left: 60px'>
              <select name='delete_user' class='form-control bg-dark text-white col-11'>
                <option value=''>-- Select a User --</option>
                <? foreach($users as $user): ?>
                <option value='<?=$user->id?>'><?=$user->name?></option>
                <? endforeach ?>
              </select>
              <button class='btn btn-submit col-1' name='action' value='delete'>Submit</button>
            </div>
          </div>
        </form>
        <? endif ?>
        <hr/>
        <form class='row'>
          <div class='col-sm-10'>
            <label for='delete_user' class='h4'>Add Animal</label>

            <div class='row' style='padding-left: 60px'>
              <select name='animal_type_id' class='form-control bg-dark text-white col-3'>
                <option value=''>-- Select a Type --</option>
                <? foreach($animalsController->animal_types as $type): ?>
                <option value='<?=$type->id?>'><?=$type->name?></option>
                <? endforeach ?>
              </select>
              <input class='form-control bg-dark text-white col-4' placeholder='Breed' name='breed'/>
              <input class='form-control bg-dark text-white col-4' placeholder='Name' name='name'/>
              <button class='btn btn-submit col-1' name='action' value='add_animal'>Add</button>
            </div>
          </div>
        </form>
      </div>
    </main>
  </body>

</html>