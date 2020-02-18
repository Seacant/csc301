<?php
  require_once('controllers/Users.php');
  require_once('controllers/Contacts.php');

  $UsersController = new Users();
  $ContactsController = new Contacts();

  // TODO: Login
  $user = $UsersController->get_user_by_id(1);

  $contact = $ContactsController->get_contact_by_id($_GET['contact_id']);

  if($contact == null){
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
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

    <style>
      .long-text {
        text-align: justify;
      }
    </style>

  </head>

  <body>
    <main class='container'>
      <div> 
        <a class='text-secondary' href='index.php'>Go Back</a>
      </div>
      <div class="jumbotron">
        <div class='row'>
          <div class='col-sm-10'>
            <h1 class="display-4"><?php echo $contact->name ?></h2>
          </div>
        </div>

        <hr class="my-4" />

        <div class='row px-5'>
          <div class='container'>
            <h3 class='text-center'>Records</h3>
            <br />
            <ul class='container'>
              <? foreach($contact->records as $index => $record): ?>
              <li class='list-group-item'>
                <span><?= $record->type ?>: <?= $record->value ?></span>
              </li>
              <? endforeach; ?>
            </ul>
          </div>
        </div>

        <hr class="my-4" />
      </div>
    </main>
  </body>

</html>