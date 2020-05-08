<?
class User {
  public int $id;
  public string $name;
  public string $username;

  public int $role = 999;

  public function is_admin()
  {
    return $this->role <= 1;
  }

  public function is_manager()
  {
    return $this->role <= 2;
  }
}
?>