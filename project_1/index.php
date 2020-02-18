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
    
    <!-- Material Icons -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">

    <style>
      .long-text {
        text-align: justify;
      }
    </style>
    
    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js"> </script>

    <script>
    $(document).ready(() => {
      
    })
    </script>

  </head>

  <body>
    <div class='container'>
      <h1 class='text-center'>Your Contacts</h1>
      <ul class='list-group'>
        <? foreach($contacts as $index => $contact): ?>
        <li class='list-group-item d-flex justify-content-between align-items-center'>
          <a href='details.php?contact_id=<?= $contact->id ?>'> <?= $contact->name ?></a>
          <span class="">
            <span><a href=''><i data-id='<?= $contact->id ?>' class='edit_contact   material-icons text-primary'>edit  </i></a></span>
            <span><a href=''><i data-id='<?= $contact->id ?>' class='delete_contact material-icons text-danger '>delete</i></a></span>
          </span>
        </li>
        <? endforeach; ?>
        <li class='list-group-item'>
          <a class='add_contact text-muted' href=''>Add More</a>
        </li>
      </ul>
    </div>
  </body>

</html>