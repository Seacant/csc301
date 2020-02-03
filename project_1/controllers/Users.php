<?

require('models/User.php');

class Users {
  // TODO: Database
  private $users;

  public function __construct(){
    $this->users = json_decode(file_get_contents('data/users.json'), true);
  }

  public function get_user_by_id(int $user_id) : ?User {
    if(!isset($this->users[$user_id])){
      return null;
    }
    return $this->_fill_user($this->users[$user_id]);
  }

  private function _fill_user(array $Iuser) : User{
    $user = new User();
    $user->id   = $Iuser['id'];
    $user->name = $Iuser['name'];
    return $user;
  }
}
?>