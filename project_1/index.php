<?php
  require_once('utilities/Session.php');


  require_once('controllers/Users.php');
  require_once('controllers/Contacts.php');

  $UsersController = new Users();
  $ContactsController = new Contacts();

  $user = $UsersController->get_user_by_id($_SESSION['user_id']);

  $contacts = $ContactsController->find_contacts_by_user($user);
?>


<!DOCTYPE html>
<html>
  <head>
    <meta charset='utf-8' />
    <title>Your Contacts</title>

    <!-- Bootstrap & Dark mode -->
    <link rel="stylesheet" href="/styles/bootstrap.min.css">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    
    <!-- Material Icons -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">

    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js"> </script>

    <!-- Code for form interactions & AJAX -->
    <script>

      function edit_form(target, json) {

        const contact_row = target.closest('.contact-root')

        // Clear other edit forms
        $('.edit-form').remove()

        // Open new edit form
        contact_row.append(`
          <form class='edit-form' style='padding-top: 12px'>
            <ul class='list-group'>
              <li class='list-group-item form-group'>
                <label class='text-info' for='edit-name'>Name: </label>
                <input class='bg-dark text-white form-control' id='edit-name' value='${json.name}'/>
              </li>
              <li class='list-group-item form-group' id='edit-form-records'>
                <p class='text-info'>Records:</p>
                <ul class='list-group'></ul>
              </li>
            </ul>
            <div style='float:right;'>
              <button class='btn btn-success'>Save</button>
              <button class='btn btn-muted' id='edit-form-cancel' type='button'>Cancel</button>
            </div>
          </form>
        `)

        $('.edit-form').submit((self) => {
          const $form = $(self.target);
          let data = {
            id: $form.closest('.contact-root').data('id'),
            name: $form.find('#edit-name').val(),
            records: $form
              .find('#edit-form-records ul')
              .children('.record')
              .map((index, html) => {
                let $record = $(html);
                return {
                  id: $record.find('.record-id').val() || undefined,
                  type: $record.find('.record-type').val(),
                  value: $record.find('.record-value').val()
                }
              })
              .get()
          };

          if(data.id === 'new'){
            fetch('/project_1/manage.php?operation=create_contact', {
              method: 'post',
              body: JSON.stringify(data),
              headers: {
                'Accept': 'application/json'
              },
              credentials: 'same-origin'
            })
            .then(() => location.reload());
          }
          else {
            fetch('/project_1/manage.php?operation=update_contact', {
              method: 'post',
              body: JSON.stringify(data),
              headers: {
                'Accept': 'application/json'
              },
              credentials: 'same-origin'
            })
            .then(() => location.reload());
          }
        })

        $('#edit-form-cancel').click((event) => {
          const $contact = $(event.target).closest('.contact-root')
          // If the edit form we cancelled is for a new (unsaved) contact,
          // remove it
          if($contact.hasClass('contact-new')){
            $contact.remove()
          }
          $('.edit-form').remove()
        })

        
        json.records.forEach((record) => {
          $('.edit-form #edit-form-records ul').append(`
            <li class='list-group-item input-group record'>
              <div class='form-row'>
                <input type='hidden' class='record-id' value='${record.id || ''}'/>
                <select class='custom-select col-md-3 bg-dark text-white record-type'>
                  <option ${record.type === 'Phone'   ? 'selected' : ''} value='Phone'>Phone</option>
                  <option ${record.type === 'Email'   ? 'selected' : ''} value='Email'>Email</option>
                  <option ${record.type === 'Address' ? 'selected' : ''} value='Address'>Address</option>
                </select>
                <input type='text' class='form-control col-md-8 bg-dark text-white record-value' value='${record.value}'/>
                <a href='#' class='col-md-1'>
                  <i
                    style='line-height: 36px;'
                    class='delete-contact-record material-icons text-danger float-right align-middle'
                    data-id='${record.id}'
                  >delete</i>
                </a>
              </div>
            </li>
          `)
        })

        $('.delete-contact-record').click(event => {
          $(event.target).closest('.record').remove();
        })

        $('.edit-form #edit-form-records ul').append(`
          <li class='list-group-item'>
            <a href='#' class='text-muted' id='edit-form-add-record'>Add More</a>
          </li>
        `)

        $('#edit-form-add-record').click((event) => {
          $form = $('.edit-form');
          let data = {
            id: $form.closest('.contact-root').data('id'),
            name: $form.find('#edit-name').val(),
            records: $form
              .find('#edit-form-records ul')
              .children('.record')
              .map((index, html) => {
                let $record = $(html);
                return {
                  id: $record.find('.record-id').val() || undefined,
                  type: $record.find('.record-type').val(),
                  value: $record.find('.record-value').val()
                }
              })
              .get()
          };

          data.records.push({
            type: 'Phone',
            value: ''
          })

          edit_form($(event.target), data);
        })

        $('.edit-form').data('contact', json);     
      }
      
      $(document).ready(() => {
        $('.edit_contact').click((self) => {
          fetch(`/project_1/manage.php?operation=get_contact_by_id&id=${$(self.target).data('id')}`)
            .then(res => res.json())
            .then((json) => {
              edit_form($(self.target), json);
            })
        })

        $('.delete_contact').click(self => {
          fetch('/project_1/manage.php?operation=delete_contact', {
            method: 'POST',
            body: JSON.stringify(
              {
                id: $(self.target).data('id'),
                user_id: 1 // TODO: User Auth
              }
            )
          })
          .then(() => location.reload())
        })

        $('.add_contact').click(self => {
          $('.user_contacts li:not(.contact-root)').before(`
            <li class='list-group-item contact-root contact-new' data-id='new'>
              <div class="d-flex justify-content-between align-items-center">
                <a href='#'> New Contact </a>
              </div>
            </li>
          `)

          edit_form($('.contact-new'), {
            name: '',
            records: []
          })
        })
      })
    </script>

  </head>

  <body>
    <div class='container'>
      <div class='row justify-content-md-center'>
        <h1 class='text-center'>Your Contacts</h1>
      </div>
      <div class='row justify-content-end'>
        <span class='right' style='padding-right: 20px;'>
          <? if ($user->is_manager()): ?>
            <a href='admin.php' class='text-muted' style='padding-right: 10px;'>Admin</a>
          <? endif ?>
          <a href='logout.php' class='text-muted'>Log Out</a>
        </span>

      </div>
      <div class='row justify-content-md-center'>
        <ul class='user_contacts list-group col-md-12'>
          <? foreach($contacts as $index => $contact): ?>
          <li class='list-group-item contact-root' data-id='<?= $contact->id ?>'>
            <div class="d-flex justify-content-between align-items-center">
              <a href='details.php?contact_id=<?= $contact->id ?>'> <?= $contact->name ?></a>
              <span>
                <span><a href='#'><i data-id='<?= $contact->id ?>' class='edit_contact   material-icons text-primary'>edit  </i></a></span>
                <span><a href='#'><i data-id='<?= $contact->id ?>' class='delete_contact material-icons text-danger '>delete</i></a></span>
              </span>
            </div>
          </li>
          <? endforeach; ?>
          <li class='list-group-item'>
            <a class='add_contact text-muted' href='#'>Add More</a>
          </li>
        </ul>
      </div>
    </div>
  </body>

</html>