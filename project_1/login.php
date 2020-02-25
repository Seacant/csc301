<?
  session_start();

  require_once('controllers/Users.php');
  $UsersController = new Users();

  if($_POST['action'] == 'Log In'){
    try{
      $user = $UsersController->login(
        $_POST['username'],
        $_POST['password']
      );

      $_SESSION['user_id'] = $user->id;
    }
    catch(Exception $e){
      $error_msg = $e->getMessage();
    }
  }
  else if ($_POST['action'] == 'Register'){
    $user = $UsersController->register(
      $_POST['name'],
      $_POST['username'],
      $_POST['password']
    );

    $_SESSION['user_id'] = $user->id;
  }

  if($_SESSION['user_id']){
    header('Location: index.php');
    die();
  }
  
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset='utf-8' />
    
    <!-- Bootstrap & Dark mode -->
    <link rel="stylesheet" href="/styles/bootstrap.min.css">
    <link rel="stylesheet" href="/styles/login.css">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    
    <!-- Material Icons -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    
    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js"> </script>

    <script>
      // Super simple create/register toggle because I'm lazy
      $(document).ready(() => {
        $('.create-account').click(() => {
          $('form.login input[type=submit]').val('Register')

          $('form.login #username').before(`
            <input type='text' id='name' name='name' placeholder='name'>
          `)
        })
      })
    </script>
  </head>

  <body>
    <div class="wrapper">
      <div id="formContent">
        <h2>Contacts</h2>
        
        <? if (isset($error_msg)): ?>
        <div>
          <span class='text-danger'><?= $error_msg ?></span>
        </div>
        <? endif ?>
  
        <form style='padding-top: 30px;' class='login' method=POST>
          <input type="text" id="username" class="fadeIn second" name="username" placeholder="username">
          <input type="password" id="password" class="fadeIn third" name="password" placeholder="password">
          <input type="submit" id='action' name='action' class="fadeIn fourth" value="Log In">
        </form>
  
        <div id="formFooter">
          <div>
            <a class="underlineHover create-account" href="#">Create Account</a>
          </div>
          <div>
            <a class="underlineHover" href="#" onclick='alert("Lol that sucks bud")'>Forgot Password?</a>
          </div>
        </div>
  
      </div>
    </div> 
  </body>

</html>