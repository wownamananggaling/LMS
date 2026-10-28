<?php // start of PHP code
require_once __DIR__ . '/../config/Database.php'; // load Database.php (only once)

// This class talks to the "books" table.
class Book extends Database { // Book gets the database connection from Database

    public function __construct() { // runs when we write: new Book()
        parent::__construct(); // run Database's constructor
        $this->connect(); // open the database connection
    }

    // Add a new book. A new book starts with ALL copies available.
    public function addBook($title, $author, $isbn, $category, $copies) { // add one book
        $sql = "INSERT INTO books (title, author, isbn, category, copies, available_copies) VALUES (?, ?, ?, ?, ?, ?)"; // 6 blanks
        $stmt = $this->conn->prepare($sql); // give it to MySQL
        $stmt->bind_param("ssssii", $title, $author, $isbn, $category, $copies, $copies); // 4 texts, 2 numbers; $copies is used twice (total AND available)
        return $stmt->execute(); // run it
    }

    // Get all books
    public function getBooks() { // get every book
        $sql = "SELECT * FROM books ORDER BY title"; // all books, A to Z
        $result = $this->conn->query($sql); // run it
        return $result->fetch_all(MYSQLI_ASSOC); // give back all rows
    }

    // Get only the books that still have at least 1 copy on the shelf
    public function getAvailableBooks() { // books that can be borrowed
        $sql = "SELECT * FROM books WHERE available_copies > 0 ORDER BY title"; // skip books with 0 copies left
        $result = $this->conn->query($sql); // run it
        return $result->fetch_all(MYSQLI_ASSOC); // give back all rows
    }

    // Get ONE book by id (used when you click the Edit button)
    public function getBook($id) { // find one book
        $sql = "SELECT * FROM books WHERE id = ?"; // the question for MySQL
        $stmt = $this->conn->prepare($sql); // give it to MySQL
        $stmt->bind_param("i", $id); // put the id in the blank
        $stmt->execute(); // run it
        $result = $stmt->get_result(); // collect the answer
        return $result->fetch_assoc();   // one book, or null if not found
    }

    // Search books by title, author, isbn or category
    public function searchBooks($keyword) { // find books that match the typed text
        $like = "%" . $keyword . "%"; // % means "anything before or after"
        $sql = "SELECT * FROM books WHERE title LIKE ? OR author LIKE ? OR isbn LIKE ? OR category LIKE ? ORDER BY title"; // match any of the 4 columns
        $stmt = $this->conn->prepare($sql); // give it to MySQL
        $stmt->bind_param("ssss", $like, $like, $like, $like); // the same text goes in all 4 blanks
        $stmt->execute(); // run it
        $result = $stmt->get_result(); // collect the answer
        return $result->fetch_all(MYSQLI_ASSOC); // give back all matching rows
    }

    // Update a book.
    // Gives back false if the new total copies is too small.
    public function updateBook($id, $title, $author, $isbn, $category, $copies) { // change a book
        // Step 1: get the old data of the book
        $oldBook = $this->getBook($id); // load the book as it is now

        // Step 2: how many copies are borrowed right now?
        $borrowedNow = $oldBook['copies'] - $oldBook['available_copies']; // total minus on-the-shelf = borrowed

        // Step 3: new available copies = new total - borrowed
        $newAvailable = $copies - $borrowedNow; // the borrowed ones are still out

        // Step 4: the total can't be smaller than what is borrowed
        if ($newAvailable < 0) { // for example: total set to 1 but 2 are borrowed
            return false; // refuse the update
        }

        // Step 5: save the changes
        $sql = "UPDATE books SET title = ?, author = ?, isbn = ?, category = ?, copies = ?, available_copies = ? WHERE id = ?"; // 7 blanks
        $stmt = $this->conn->prepare($sql); // give it to MySQL
        $stmt->bind_param("ssssiii", $title, $author, $isbn, $category, $copies, $newAvailable, $id); // 4 texts then 3 numbers
        return $stmt->execute(); // run it
    }

    // Delete a book
    public function deleteBook($id) { // delete one book
        $sql = "DELETE FROM books WHERE id = ?"; // delete only the book with this id
        $stmt = $this->conn->prepare($sql); // give it to MySQL
        $stmt->bind_param("i", $id); // put the id in the blank
        return $stmt->execute(); // run it
    }

    // Is any copy of this book still borrowed?
    // Gives back true (yes) or false (no)
    public function hasUnreturned($id) { // check for borrowed copies
        $sql = "SELECT COUNT(*) AS total FROM borrows WHERE book_id = ? AND status = 'borrowed'"; // count unreturned borrows of this book
        $stmt = $this->conn->prepare($sql); // give it to MySQL
        $stmt->bind_param("i", $id); // put the book id in the blank
        $stmt->execute(); // run it
        $result = $stmt->get_result(); // collect the answer
        $row = $result->fetch_assoc(); // get the one row that has the count

        if ($row['total'] > 0) { // if the count is more than 0
            return true; // yes, some copies are still out
        } else { // otherwise
            return false; // no, all copies are back
        }
    }

    // How many books are there? (used in the dashboard)
    public function countBooks() { // count all books
        $sql = "SELECT COUNT(*) AS total FROM books"; // count the rows in books
        $result = $this->conn->query($sql); // run it
        $row = $result->fetch_assoc(); // get the one row
        return $row['total']; // give back the number
    }
}