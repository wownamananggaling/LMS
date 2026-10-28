<?php // start of PHP code

class Database { // a class is a blueprint; this one handles the database connection
    private $host; // the server name (private = only this class can use it)
    private $username; // the MySQL username
    private $password; // the MySQL password
    private $dbname; // the name of the database

    protected $conn; // the connection (protected = this class AND child classes can use it)

    public function __construct() { // runs automatically when we write: new Database()
        $this->host = 'localhost'; // MySQL is on this same computer
        $this->username = 'root'; // XAMPP's default username
        $this->password = ''; // XAMPP's default password is empty
        $this->dbname = 'Perolino_LMS_db'; // the database we made with schema.sql
    }

    public function connect() { // opens the connection to MySQL

        $this->conn = new mysqli( // create the connection using mysqli
            $this->host, // server name
            $this->username, // username
            $this->password, // password
            $this->dbname // database name

        ); // end of the connection

        if ($this->conn->connect_error) { // if the connection failed
            die ("Connection failed: " . $this->conn->connect_error); // stop and show the error
        }

        return $this->conn; // give back the working connection
    }
}

$database = new Database(); // make a Database object
$database->connect(); // connect right away
?>