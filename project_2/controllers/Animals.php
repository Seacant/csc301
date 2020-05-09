<?
require_once('models/User.php');
require_once('models/Animal.php');
require_once('models/AnimalType.php');
require_once('models/Adoption.php');
require_once('utilities/Database.php');

class Animals {
  private $conn;

  # This is basically static so let's pre-cache it
  public array $animal_types;

  public function list_adoptions(): array {
    $sth = $this->conn->prepare('
      SELECT 
        a.*,
        an.*,
        u.*,
        at.*
      FROM adoptions a
      INNER JOIN animals an
        USING (animal_id)
      INNER JOIN users u
        USING (user_id)
      INNER JOIN animal_types at
        USING (animal_type_id)
    ');

    $sth->execute();

    $result = $sth->get_result()->fetch_all(MYSQLI_ASSOC);

    return (array_map([$this, '_fill_adoption'], $result ?? []));
  }

  public function list_available_animals(): array {
    $sth = $this->conn->prepare('
      SELECT 
        an.*,
        at.*
      FROM animals an
      INNER JOIN animal_types at
        USING(animal_type_id)
      LEFT JOIN adoptions a
        USING(animal_id)
      WHERE a.status IS NULL
        or a.status = "Inquired"
        or a.status = "Interacted"
      group by an.animal_id, at.animal_type_id
    ');

    $sth->execute();

    $result = $sth->get_result()->fetch_all(MYSQLI_ASSOC);

    return (array_map([$this, '_fill_animal'], $result ?? []));
  } 

  public function get_animal_by_id(int $animal_id): Animal {
    $sth = $this->conn->prepare('
      SELECT 
        an.*,
        at.*
      FROM animals an
      INNER JOIN animal_types at
        USING(animal_type_id)
      WHERE animal_id = ?
    ');

    $sth->bind_param("i", $animal_id);
    $sth->execute();

    $result = $sth->get_result()->fetch_assoc();

    return $this->_fill_animal($result);
  }

  public function list_animals(): array {
    $sth = $this->conn->prepare('
      SELECT 
        an.*,
        at.*
      FROM animals an
      INNER JOIN animal_types at
        USING(animal_type_id)
    ');

    $sth->execute();

    $result = $sth->get_result()->fetch_all(MYSQLI_ASSOC);

    return (array_map([$this, '_fill_animal'], $result ?? []));
  }

  public function find_adoptions_by_user(User $user): array {
    $sth = $this->conn->prepare('
      SELECT 
        a.*,
        an.*,
        u.*
      FROM adoptions a
      INNER JOIN animals an
        USING (animal_id)
      INNER JOIN users u
        USING (user_id)
      WHERE u.user_id = ?
    ');

    $sth->bind_param("i", $user->id);
    $sth->execute();

    $result = $sth->get_result()->fetch_all(MYSQLI_ASSOC);

    return (array_map([$this, '_fill_adoption'], $result ?? []));
  }

  public function find_adoptions_by_animal(Animal $animal): array {
    $sth = $this->conn->prepare('
      SELECT 
        a.*,
        an.*,
        u.*
      FROM adoptions a
      INNER JOIN animals an
        USING (animal_id)
      INNER JOIN users u
        USING (user_id)
      WHERE an.animal_id = ?
    ');

    $sth->bind_param("i", $animal->id);
    $sth->execute();

    $result = $sth->get_result()->fetch_all(MYSQLI_ASSOC);

    return (array_map([$this, '_fill_adoption'], $result ?? []));
  }

  public function get_adoption_by_id(int $adoption_id): ?Adoption {
    $sth = $this->conn->prepare('
      SELECT 
        a.*,
        an.*,
        u.*
      FROM adoptions a
      INNER JOIN animals an
        USING (animal_id)
      INNER JOIN users u
        USING (user_id)
      WHERE a.adoption_id = ?
    ');


    $sth->bind_param("i", $adoption_id);
    $sth->execute();

    $result = $sth->get_result()->fetch_assoc();
    return $this->_fill_adoption($result);
  }

  public function create_animal(array $IAnimal): Animal {
    $sth = $this->conn->prepare('
      INSERT INTO animals
      SET
        animal_type_id = ?,
        breed = ?,
        name = ?
    ');

    $sth->bind_param(
      'iss',
      $IAnimal['animal_type_id'],
      $IAnimal['breed'],
      $IAnimal['name'],
    );

    $sth->execute();

    $animal_id = $sth->insert_id;

    return $this->get_animal_by_id($animal_id);
  }

  public function create_adoption(array $IAdoption): Adoption {
    # Insert main contact record
    $sth = $this->conn->prepare('
      INSERT INTO adoptions
      SET
        user_id = ?,
        animal_id = ?
    ');

    $sth->bind_param(
      'ii',
      intval($IAdoption['user_id']),
      intval($IAdoption['animal_id'])
    );

    $sth->execute();

    $adoption_id = $sth->insert_id;

    return $this->get_adoption_by_id($adoption_id);
  }

  public function update_animal(array $IAnimal): Animal {

    # Fill with post data
    $animal = $this->_fill_animal($IAnimal);

    $sth = $this->conn->prepare('
      UPDATE animals
      SET
        name = ?
    ');

    $sth->bind_param(
      's',
      $animal->name
    );

    $sth->execute();

    return $this->get_animal_by_id($animal->id);
  }

  public function update_adoption(array $IAdoption): Adoption {
    $adoption = $this->get_adoption_by_id($IAdoption['id']);
    $adoption_id = $adoption->id;

    # Update main contact record
    $sth = $this->conn->prepare('
      UPDATE adoptions
      SET
        status = ?
      WHERE adoption_id = ?
    ');

    $sth->bind_param(
      'si',
      $IAdoption['status'],
      $adoption_id
    );

    $sth->execute();
    error_log($sth->error);

    return $this->get_adoption_by_id($adoption_id);
  }

  public function delete_adoption(array $IAdoption): Adoption {
    $adoption = $this->get_adoption_by_id($IAdoption['adoption_id']);

    $sth = $this->conn->prepare('
      DELETE FROM adoptions
      WHERE adoption_id = ?
    ');
    $sth->bind_param('i', $adoption->id);
    $sth->execute();

    return $adoption;
  }

  public function delete_animal(array $IAnimal): Animal {
    $animal = $this->get_animal_by_id($IAnimal['animal_id']);

    $sth = $this->conn->prepare('
      DELETE FROM animals
      WHERE animal_id = ?
    ');
    $sth->bind_param('i', $animal->id);
    $sth->execute();

    return $animal;
  }

  private function _fill_animal(array $IAnimal): Animal {
    $animal = new Animal();
    $animal->id = intval($IAnimal['animal_id']);
    $animal->type = $this->animal_types[intval($IAnimal['animal_type_id'])];
    $animal->breed = $IAnimal['breed'];
    $animal->name = $IAnimal['name'];
    $animal->picture = $IAnimal['picture'] ?? 'default.jpg';

    return $animal;
  }

  private function _fill_adoption(array $IAdoption): Adoption {
    $UsersController = new Users();

    $adoption = new Adoption();
    $adoption->id = intval($IAdoption['adoption_id']);
    $adoption->animal = $this->_fill_animal($IAdoption);
    $adoption->status = $IAdoption['status'];
    $adoption->user = $UsersController->_fill_user([
      "user_id" => $IAdoption['user_id'],
      "name" => $IAdoption['first_name'] . ' ' . $IAdoption['last_name'],
      "role" => $IAdoption['role'],
    ]);
    return $adoption;
  }

  private function _fill_animal_type(array $IAnimalType): AnimalType {
    $animalType = new AnimalType();
    $animalType->id = intval($IAnimalType['animal_type_id']);
    $animalType->name = $IAnimalType['animal_name'];
    return $animalType;
  }

  public function __construct(){
    if(!isset($_SESSION) || !isset($_SESSION['user_id'])){
      throw new Exception('You must be logged in to manage contacts');
    }

    $databaseController = new Database();
    $this->conn = $databaseController->get_connection();

    # Cache animal types
    {
      $sth = $this->conn->prepare("
        SELECT * from animal_types
      ");
      $sth->execute();

      # animal_types = { intval(animal_type_id) => AnimalType, ... }
      $result = $sth->get_result()->fetch_all(MYSQLI_ASSOC);

      $this->animal_types = array_combine(
        array_map( function($row) {return intval($row['animal_type_id']);}, $result),
        array_map([$this, '_fill_animal_type'], $result)
      );

    }
  }

}
?>