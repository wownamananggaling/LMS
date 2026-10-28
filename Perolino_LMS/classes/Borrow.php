<?php // start of PHP code
require_once __DIR__ . '/../config/Database.php'; // load Database.php (only once)

// This class talks to the "borrows" table.
class Borrow extends Database { // Borrow gets the database connection from Database

    public function __construct() { // runs when we write: new Borrow()
        parent::__construct(); // run Database's constructor
        $this->connect(); // open the database connection
    }

    // Borrow a book. Two steps:
    //   1. save the borrow record
    //   2. take 1 copy off the shelf (available_copies minus 1)
    public function borrowBook($studentId, $bookId, $dueDate) { // a student borrows a book
        // Step 1: save the record (CURDATE() means today)
        $sql = "INSERT INTO borrows (student_id, book_id, borrow_date, due_date) VALUES (?, ?, CURDATE(), ?)"; // borrow date = today
        $stmt = $this->conn->prepare($sql); // give it to MySQL
        $stmt->bind_param("iis", $studentId, $bookId, $dueDate); // 2 numbers then 1 text (the date)
        $stmt->execute(); // run it

        // Step 2: one less copy on the shelf
        $sql = "UPDATE books SET available_copies = available_copies - 1 WHERE id = ?"; // minus 1 copy
        $stmt = $this->conn->prepare($sql); // give it to MySQL
        $stmt->bind_param("i", $bookId); // which book
        $stmt->execute(); // run it
    }

    // Return a book. Two steps:
    //   1. mark the record as returned and save today's date
    //   2. put the copy back on the shelf (available_copies plus 1)
    public function returnBook($borrowId, $bookId) { // a student returns a book
        // Step 1: mark as returned
        $sql = "UPDATE borrows SET status = 'returned', return_date = CURDATE() WHERE id = ?"; // status changes, return date = today
        $stmt = $this->conn->prepare($sql); // give it to MySQL
        $stmt->bind_param("i", $borrowId); // which borrow record
        $stmt->execute(); // run it

        // Step 2: one more copy on the shelf
        $sql = "UPDATE books SET available_copies = available_copies + 1 WHERE id = ?"; // plus 1 copy
        $stmt = $this->conn->prepare($sql); // give it to MySQL
        $stmt->bind_param("i", $bookId); // which book
        $stmt->execute(); // run it
    }

    // Get all borrow records.
    // JOIN lets us also get the student's name and the book's title
    // (the borrows table only has their id numbers).
    // Records that are still "borrowed" are listed first.
    public function getBorrows() { // get the whole borrow list
        $sql = "SELECT borrows.*, students.name AS student_name, books.title AS book_title
                FROM borrows
                JOIN students ON students.id = borrows.student_id
                JOIN books ON books.id = borrows.book_id
                ORDER BY borrows.status ASC, borrows.due_date ASC"; // all borrow columns + student name + book title; 'borrowed' sorts before 'returned'
        $result = $this->conn->query($sql); // run it
        return $result->fetch_all(MYSQLI_ASSOC); // give back all rows
    }

    // Did this student already borrow this book and not return it yet?
    // Gives back true (yes) or false (no)
    public function alreadyBorrowed($studentId, $bookId) { // check for a duplicate borrow
        $sql = "SELECT COUNT(*) AS total FROM borrows WHERE student_id = ? AND book_id = ? AND status = 'borrowed'"; // count matching unreturned records
        $stmt = $this->conn->prepare($sql); // give it to MySQL
        $stmt->bind_param("ii", $studentId, $bookId); // two numbers
        $stmt->execute(); // run it
        $result = $stmt->get_result(); // collect the answer
        $row = $result->fetch_assoc(); // get the one row that has the count

        if ($row['total'] > 0) { // if the count is more than 0
            return true; // yes, already borrowed
        } else { // otherwise
            return false; // no, they can borrow it
        }
    }

    // How many books are borrowed right now? (dashboard)
    public function countActive() { // count unreturned books
        $sql = "SELECT COUNT(*) AS total FROM borrows WHERE status = 'borrowed'"; // count records still borrowed
        $result = $this->conn->query($sql); // run it
        $row = $result->fetch_assoc(); // get the one row
        return $row['total']; // give back the number
    }

    // How many borrowed books are late? (due date is before today) (dashboard)
    public function countOverdue() { // count late books
        $sql = "SELECT COUNT(*) AS total FROM borrows WHERE status = 'borrowed' AND due_date < CURDATE()"; // still borrowed AND due before today
        $result = $this->conn->query($sql); // run it
        $row = $result->fetch_assoc(); // get the one row
        return $row['total']; // give back the number
    }
}