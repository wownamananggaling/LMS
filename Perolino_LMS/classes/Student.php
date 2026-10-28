<?php // start of PHP code
require_once __DIR__ . '/../config/Database.php'; // load Database.php (only once)

class Student extends Database { // Student gets everything Database has, like $this->conn
    public function __construct() { // runs when we write: new Student()
        parent::__construct(); // run Database's constructor (sets host, username, ...)
        $this->connect(); // open the database connection
    }

    public function addStudent($name, $email, $studentPhone, $registrationNumber) { // add a new student
        $stmt = $this->conn->prepare("INSERT INTO students (name, email, phone, registration_no) VALUES (?, ?, ?, ?)"); // the ? are blanks we fill in next
        $stmt->bind_param("ssss", $name, $email, $studentPhone, $registrationNumber); // fill the 4 blanks; "ssss" = four strings (text)
        return $stmt->execute(); // run it; gives back true if it worked
    }

    // NEW: get all students from the database
    public function getStudents() { // get every student
        $result = $this->conn->query("SELECT * FROM students ORDER BY name"); // ask MySQL for all students, A to Z
        return $result->fetch_all(MYSQLI_ASSOC); // give back all rows as a list of arrays
    }
    public function Allstudents(){ // does the same thing as getStudents (your original)
        $result = $this->conn->query("SELECT * FROM students ORDER BY name"); // ask MySQL for all students
        return $result->fetch_all(MYSQLI_ASSOC); // give back all rows
    }

    public function UpdateStudent($id, $name, $email, $studentPhone, $registrationNumber) { // change an existing student
        $stmt = $this->conn->prepare("UPDATE students SET name = ?, email = ?, phone = ?, registration_no = ? WHERE id = ?"); // WHERE id = ? so only ONE student changes
        $stmt->bind_param("ssssi", $name, $email, $studentPhone, $registrationNumber, $id); // 4 strings then 1 integer (the id)
        return $stmt->execute(); // run it
    }

    public function DeleteStudent($id) { // delete a student
        $stmt = $this->conn->prepare("DELETE FROM students WHERE id = ?"); // delete only the student with this id
        $stmt->bind_param("i", $id); // "i" = integer
        return $stmt->execute(); // run it
    }

    // Get ONE student by id (used when you click the Edit button)
    public function getStudent($id) { // find one student
        $sql = "SELECT * FROM students WHERE id = ?"; // the question for MySQL
        $stmt = $this->conn->prepare($sql); // give it to MySQL
        $stmt->bind_param("i", $id); // put the id in the blank
        $stmt->execute(); // run it
        $result = $stmt->get_result(); // collect the answer
        return $result->fetch_assoc();   // one student, or null if not found
    }

    // Search students by name, email or registration number
    public function searchStudents($keyword) { // find students that match the typed text
        $like = "%" . $keyword . "%";    // % means "anything before or after"
        $sql = "SELECT * FROM students WHERE name LIKE ? OR email LIKE ? OR registration_no LIKE ? ORDER BY name"; // match any of the 3 columns
        $stmt = $this->conn->prepare($sql); // give it to MySQL
        $stmt->bind_param("sss", $like, $like, $like); // the same text goes in all 3 blanks
        $stmt->execute(); // run it
        $result = $stmt->get_result(); // collect the answer
        return $result->fetch_all(MYSQLI_ASSOC); // give back all matching rows
    }

    // Does this student still have a book that was not returned yet?
    // Gives back true (yes) or false (no)
    public function hasUnreturned($id) { // check for unreturned books
        $sql = "SELECT COUNT(*) AS total FROM borrows WHERE student_id = ? AND status = 'borrowed'"; // count this student's unreturned books
        $stmt = $this->conn->prepare($sql); // give it to MySQL
        $stmt->bind_param("i", $id); // put the student id in the blank
        $stmt->execute(); // run it
        $result = $stmt->get_result(); // collect the answer
        $row = $result->fetch_assoc(); // get the one row that has the count

        if ($row['total'] > 0) { // if the count is more than 0
            return true; // yes, they still have a book
        } else { // otherwise
            return false; // no, they returned everything
        }
    }

    // How many students are there? (used in the dashboard)
    public function countStudents() { // count all students
        $sql = "SELECT COUNT(*) AS total FROM students"; // count the rows in students
        $result = $this->conn->query($sql); // run it (no blanks, so no prepare needed)
        $row = $result->fetch_assoc(); // get the one row
        return $row['total']; // give back the number
    }
}
?>