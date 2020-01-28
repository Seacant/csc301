<?php
  require_once('controllers/Users.php');
  require_once('controllers/Contacts.php');

  $UsersController = new Users();
  $ContactsController = new Contacts();

  // TODO: Login
  $user = $UsersController->get_user_by_id(1);

  $contacts = $ContactsController->find_contacts_by_user($user);
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
    <div class='container'>
      <h1 class='text-center'>Your Contacts</h1>
      <ul class='list-group'>
        <? foreach($contacts as $index => $contact): ?>
        <li class='list-group-item'>
          <a href='details.php?contact_id=<?= $contact->id ?>'> <?= $contact->name ?></a>
        </li>
        <? endforeach; ?>
      </ul>
    </div>
  </body>

</html>