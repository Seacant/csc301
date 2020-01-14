 <?php 
  $user = [
    'name'      => 'Travis Fletcher',
    'email'     => 'travis@tfletch.tech',
    'age'       => 21,
    'enrolment' => [
      'major' => ['Data Science'],
      'minor' => ['Management']
    ],
    'avatar' => "https://avatars0.githubusercontent.com/u/9843883",
    'city'   => 'Cincinnati',

    'elevator_pitch' => "
      Hello. My name is Travis Fletcher. I am a Data Science major with a minor
      in Data Science. I am a Software Engineer at an EdTech company called
      Certica Solutions, and I work full-time between classes. I've been
      there for 5 years and love going to work every day. In my free time, I
      like to cook and work on personal projects.
    "
  ];
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset='utf-8' />

    <!-- Bootstrap & Dark mode -->
    <link rel="stylesheet" href="/styles/bootstrap.min.css">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

  </head>

  <body>
    <main class='container'>
      <div class="jumbotron">
        <div class='row'>
          <div class='col-sm-10'>
            <h1 class="display-4"><?php echo $user['name']; ?></h2>
            <p class="lead"><?php 
              echo join(', ', $user['enrolment']['major']) 
            ?>; <?php
              echo join(', ', $user['enrolment']['minor']) 
            ?> Minor </p>
          </div>

          <div class='col-sm-2'>
            <img
              height=100
              width=100
              src='<?php echo $user['avatar'] ?>'
            />
          </div>
        </div>

        <hr class="my-4" />

        <div class='row px-5'>
          <div class='container'>
            <h3 class='text-center'>Background</h3>
            <br />
            <div class='container lead'><?php
                echo $user['elevator_pitch']
            ?></div>
          </div>
        </div>

        <hr class="my-4" />

        <div class='row px-5'>
          <div class='container'>
            <h3 class='text-center'> Reason For Taking CSC-301 </h3>
            <br />
            <div class='container lead'>
              I am taking this class becase it's what I do for a living so I
              figured I should take a class on it. I'm looking forward to 
              seeing what I learn this semester.
            </div>
          </div>
        </div>

      </div>
    </main>
  </body>

</html>