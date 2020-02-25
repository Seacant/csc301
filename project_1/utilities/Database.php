<?
class Database {
  private $server   = '172.18.0.2';
  private $username = 'root';
  private $password = 'tiger';
  private $database = 'contacts';

  // TODO: Connection Pool?
  private $conn;

  public function get_connection(): mysqli {
    if(isset($this->conn)) {
      return $this->conn;
    }

    $this->conn = new mysqli($this->server, $this->username, $this->password, $this->database);
    return $this->conn;
  }

}
?>