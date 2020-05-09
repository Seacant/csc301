<?php
  require_once('utilities/Session.php');

  require_once('controllers/Users.php');
  require_once('controllers/Animals.php');

  $UsersController = new Users();
  $AnimalsController = new Animals();

  $user = $UsersController->get_user_by_id($_SESSION['user_id']);

  $all_animals = $AnimalsController->list_available_animals();
  $adoptions = $AnimalsController->find_adoptions_by_user($user);

  # Remove the animals who are already above from 'all' animals
  $all_animals = array_filter(
    $all_animals,
    function ($animal) use ($adoptions) {
      return !in_array(
        $animal->id,
        array_map(
          function ($adoption) { return $adoption->animal->id;},
          $adoptions
        )
      );
    }
  );
?>


<!DOCTYPE html>
<html>
  <head>
    <meta charset='utf-8' />
    <title>ACME Animal Shelter</title>

    <!-- Bootstrap & Dark mode -->
    <link rel="stylesheet" href="/styles/bootstrap.min.css">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    
    <!-- Material Icons -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">

    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js"> </script>

    <!-- Code for form interactions & AJAX -->
    <script>
    $(document).ready(() => {
      // Filtering by animal type support
      $('SELECT[name=filter_animal_type]').change(() => {
        const animal_type_id = $('SELECT[name=filter_animal_type]').val()

        $('.animal-root').each(
          (idx, row) => 
            animal_type_id == '__all' || $(row).data('animal-type') == animal_type_id
              ? $(row).show()
              : $(row).hide()
        );
      })

      $('.adoption-status').click(event => {
        const $root = $(event.target).closest('.adoption-root')

        fetch('manage.php?operation=delete_adoption', {
          method: 'post',
          body: JSON.stringify({
            'adoption_id': $root.data('id')
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
    <div class='container'>
      <div class='row justify-content-end'>
        <span class='right' style='padding-right: 20px;'>
          <? if ($user->is_manager()): ?>
            <a href='admin.php' class='text-muted' style='padding-right: 10px;'>Admin</a>
          <? endif ?>
          <a href='logout.php' class='text-muted'>Log Out</a>
        </span>
      </div>
      <? if(!empty($adoptions)): ?>
      <div class='row justify-content-md-center'>
        <h1>Adoptions</h1>
        <ul class='user_contacts list-group col-md-12'>
          <? foreach($adoptions as $index => $adoption): ?>
          <li 
            class='list-group-item adoption-root'
            data-id='<?= $adoption->id ?>'
            data-animal-type='<?= $adoption->animal->type->id ?>'
          >
            <div class="d-flex justify-content-between align-items-center">
              <span>
                <img width='50px' height='50px' src='<?='static/pictures/' . ($adoption->animal->picture) ?>'>
                <a href='details.php?animal_id=<?= $adoption->animal->id ?>'> <?= $adoption->animal->name ?></a>
              </span>
              <span class='badge badge-light adoption-status' title='Click to revoke'><?= $adoption->status ?></span>
            </div>
          </li>
          <? endforeach; ?>
        </ul>
      </div>
      <hr/>
      <? endif ?>
      <div class='row justify-content-md-center'>
        <h1>All Animals</h1>
      </div>
      <div class='row'>
        <select class='col-2 form-control text-white bg-dark' name='filter_animal_type'>
          <option value='__all'>All</option>
          <? foreach($AnimalsController->animal_types as $index => $animal_type): ?>
          <option value='<?= $animal_type->id ?>'><?= $animal_type->name ?></option>
          <? endforeach ?>
        </select>
      </div>
      <div class='row justify-content-md-center'>
        <ul class='user_contacts list-group col-md-12'>
          <? foreach($all_animals as $index => $animal): ?>
          <li
            class='list-group-item animal-root'
            data-id='<?= $animal->id ?>'
            data-animal-type='<?= $animal->type->id ?>'
           >
            <div class="d-flex justify-content-between align-items-center">
              <span>
                <img width='50px' height='50px' src='<?='static/pictures/' . ($animal->picture) ?>'>
                <a href='details.php?animal_id=<?= $animal->id ?>'> <?= $animal->name ?></a>
              </span>
            </div>
          </li>
          <? endforeach; ?>
        </ul>
      </div>
    </div>
  </body>

</html>