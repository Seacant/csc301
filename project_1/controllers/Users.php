<?

require('models/User.php');

class Users {
  // TODO: Database
  private $users = [
    1 => [
      "id" => 1,
      "name" => 'Travis Fletcher'
    ]
  ];

  public function get_user_by_id($user_id) {
    if(!isset($this->users[$user_id])){
      return null;
    }
    return $this->_fill_user($this->users[$user_id]);
  }

  private function _fill_user($Iuser) {
    $user = new User();
    $user->id   = $Iuser['id'];
    $user->name = $Iuser['name'];
    return $user;
  }
}
?>