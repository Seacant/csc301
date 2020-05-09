<?php
  require_once('utilities/Session.php');

  require_once('controllers/Users.php');
  require_once('controllers/Animals.php');

  $UsersController = new Users();
  $AnimalsController = new Animals();

  $user = $UsersController->get_user_by_id($_SESSION['user_id']);

  $animal = $AnimalsController->get_animal_by_id($_GET['animal_id']);

  if($animal == null){
    header('Location: index.php');
    die();
  }

  $adoptions = $AnimalsController->find_adoptions_by_animal($animal);

  # Only managers can see other adoption inquiries
  if(!$user->is_manager()){
    $adoptions = array_filter(
      $adoptions,
      function ($adoption) use ($user) {
        return $adoption->user->id == $user->id;
      }
    ); 
  }

  # You can inquire if you have no current adoption inquiries on this animal
  $can_inquire = empty(array_filter(
    $adoptions,
    function ($adoption) use ($user) {
      return $adoption->user->id == $user->id;
    }
  ));
?>


<!DOCTYPE html>
<html>
  <head>
    <meta charset='utf-8' />
    <title><?= $contact->name ?></title>

    <!-- Bootstrap & Dark mode -->
    <link rel="stylesheet" href="/styles/bootstrap.min.css">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js"> </script>

    <style>
      .long-text {
        text-align: justify;
      }
    </style>

    <script>
    $(document).ready(() => {
      $('.inquire-button').click(() => {
        fetch('manage.php?operation=create_adoption', {
          method: 'post',
          body: JSON.stringify({
            'animal_id': <?= $animal->id ?>,
            'user_id': <?= $user->id ?> 
          }),
          headers: {
            'Accept': 'application/json'
          },
          credentials: 'same-origin'
        }).then(() => location.reload())
      })
    })
    </script>

  </head>

  <body>
    <main class='container'>
      <div> 
        <a class='text-secondary' href='index.php'>Go Back</a>
      </div>
      <div class="jumbotron align-items-center">
        <div style='text-align: center;'>
          <h4>Name: <?php echo $animal->name ?></h4>
          <h4>Breed: <?php echo $animal->breed ?></h4>
          <img width='500px' height='500px' src='<?='static/pictures/' . ($animal->picture) ?>'>
        </div>
        
        <hr class="my-4" />

        <div class='row px-5'>
          <div class='container'>
            <h3 class='text-center'>Adoption Inquiries</h3>
            <br />
            <ul class='container'>
              <? foreach ($adoptions as $adoption): ?>
              <li class='list-group-item'>
                <div>Name: <?= $adoption->user->name ?></div>
                <div>Status: <?= $adoption->status ?></div>
              </li>
              <? endforeach ?>

              <? if ($can_inquire): ?>
              <li class='list-group-item' style='text-align: center;'>
                <button class='btn btn-primary inquire-button'>Inquire!</button>
              </li>
              <? endif ?>

            </ul>
          </div>
        </div>

        <hr class="my-4" />
      </div>
    </main>
  </body>

</html>