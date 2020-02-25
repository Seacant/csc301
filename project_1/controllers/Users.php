<?

require_once('models/User.php');
require_once('utilities/Database.php');

class Users {
  public function __construct(){
    $databaseController = new Database();
    $this->conn = $databaseController->get_connection();
  }

  public function login(string $username, string $password): User {
    $sth = $this->conn->prepare('
      SELECT
        user_id,
        username,
        first_name,
        last_name,
        password
      FROM users
      WHERE username = ?
    ');
    $sth->bind_param('s', $username);
    $sth->execute();

    $row = $sth->get_result()->fetch_assoc();

    if(!isset($row)){
      throw new Exception("User not found");
    }

    // Check password
    if(!password_verify($password, $row['password'])){
      throw new Exception("Bad Password");
    }

    return $this->_fill_user([
      "id"       => $row['user_id'],
      "name"     => $row['first_name'] . ' ' . $row['last_name'],
      "username" => $row['username'],
    ]);
  }

  public function register(string $name, string $username, string $password){
    $names = explode(' ', $name, 2);

    $sth = $this->conn->prepare('
      INSERT INTO users
      SET
        first_name = ?,
        last_name = ?,
        username = ?,
        password = ?
    ');
    $sth->bind_param(
      'ssss',
      $names[0],
      $names[1],
      $username,
      password_hash($password, PASSWORD_BCRYPT)
    );
    $sth->execute();

    return $this->login($username, $password);
  }

  public function get_user_by_id(int $user_id) : ?User {
    $sth = $this->conn->prepare('
      SELECT
        user_id,
        username,
        first_name,
        last_name,
        password
      FROM users
      WHERE user_id = ?
    ');
    $sth->bind_param('i', $user_id);
    $sth->execute();

    $row = $sth->get_result()->fetch_assoc();

    if(!isset($row)){
      return null;
    }

    return $this->_fill_user([
      "id"       => $row['user_id'],
      "name"     => $row['first_name'] . ' ' . $row['last_name'],
      "username" => $row['username'],
    ]);
  }

  private function _fill_user(array $Iuser) : User{
    $user = new User();
    $user->id   = $Iuser['id'];
    $user->name = $Iuser['name'];
    return $user;
  }
}
?>