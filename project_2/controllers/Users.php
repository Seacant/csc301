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
        password,
        role
      FROM users
      WHERE username = ?
        AND disabled=0
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
      "user_id"  => $row['user_id'],
      "name"     => $row['first_name'] . ' ' . $row['last_name'],
      "username" => $row['username'],
      "role"     => $row['role']
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

  public function list_users() : Array {
     $sth = $this->conn->prepare('
      SELECT
        user_id,
        CONCAT(
          COALESCE(first_name, ""),
          IF(ISNULL(CONCAT(first_name,last_name)), "", " "),
          COALESCE(last_name, "")
        ) as name,
        role
      FROM users
      where disabled=0
    ');
    $sth->execute();

    $result = $sth->get_result()->fetch_all(MYSQLI_ASSOC);

    return (array_map([$this, '_fill_user'], $result ?? []));
  }

  public function delete_user_by_id(int $user_id){
    $sth = $this->conn->prepare('
      UPDATE users
      set disabled = 1
      WHERE user_id = ?
    ');
    $sth->bind_param('i', $user_id);
    $sth->execute();

    return null;
  }

  public function get_user_by_id(int $user_id) : ?User {
    $sth = $this->conn->prepare('
      SELECT
        user_id,
        CONCAT(
          COALESCE(first_name, ""),
          IF(ISNULL(CONCAT(first_name,last_name)), "", " "),
          COALESCE(last_name, "")
        ) as name,
        role
      FROM users
      WHERE user_id = ?
        AND disabled=0
    ');
    $sth->bind_param('i', $user_id);

    $sth->execute();

    $row = $sth->get_result()->fetch_assoc();

    if(!isset($row)){
      return null;
    }

    return $this->_fill_user($row);
  }

  public function _fill_user(array $Iuser) : User{
    $user = new User();
    $user->id   = $Iuser['user_id'];
    $user->name = $Iuser['name'];
    $user->role = $Iuser['role'];
    return $user;
  }
}
?>