<?
require_once('utilities/Session.php');

require_once('controllers/Users.php');

$usersController = new Users;

$users = $usersController->list_users();

// Redirect non-admin
if($_SESSION['user_id'] != 1){
  header('Location: index.php');
  die();
}

// Handle impersonations
if($_GET['impersonate_user']){
  $_SESSION['user_id'] = $_GET['impersonate_user'];
  header('Location: index.php');
  die();
}
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
              <select name='impersonate_user' class='form-control bg-dark text-white col-10'>
                <option>-- Select a User --</option>
                <? foreach($users as $user): ?>
                <option value='<?=$user->id?>'><?=$user->name?></option>
                <? endforeach ?>
              </select>
              <button class='btn btn-submit col-2'>Submit</button>
            </div>
          </div>
        </form>
      </div>
    </main>
  </body>

</html>