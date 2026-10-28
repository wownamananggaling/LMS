<?php // start of PHP code (this top part runs first)
require_once __DIR__ . '/../classes/Student.php'; // load the Student class (for the student dropdown)
require_once __DIR__ . '/../classes/Book.php'; // load the Book class (for the book dropdown)
require_once __DIR__ . '/../classes/Borrow.php'; // load the Borrow class
$student = new Student(); // make a Student object
$book = new Book(); // make a Book object
$borrow = new Borrow(); // make a Borrow object

// $message shows a green or red box on top of the form
$message = ""; // empty for now

// today's date, like 2026-10-27
$today = date('Y-m-d'); // year-month-day


// ---------- 1. Return button was clicked ----------
if (isset($_POST['return_id'])) { // did a Return form send a return_id?
    $borrowId = $_POST['return_id']; // which borrow record
    $bookId = $_POST['book_id']; // which book (we need to put the copy back)

    $borrow->returnBook($borrowId, $bookId); // mark returned + add the copy back
    $message = "<div class='alert alert-success'>Book returned</div>"; // green box
}


// ---------- 2. Borrow button was clicked ----------
if (isset($_POST['borrow'])) { // did the submit button (named "borrow") send something?
    $studentId = $_POST['student_id']; // chosen student
    $bookId = $_POST['book_id']; // chosen book
    $dueDate = $_POST['due_date']; // chosen due date

    if ($dueDate < $today) { // is the due date before today?
        // the due date can't be a day that already passed
        $message = "<div class='alert alert-danger'>Due date can't be in the past.</div>"; // red box

    } elseif ($borrow->alreadyBorrowed($studentId, $bookId) == true) { // did this student already borrow this book?
        // same student, same book, not returned yet
        $message = "<div class='alert alert-danger'>This student already borrowed this book.</div>"; // red box

    } else { // everything is okay
        $borrow->borrowBook($studentId, $bookId, $dueDate); // save the borrow + take a copy off the shelf
        $message = "<div class='alert alert-success'>Book borrowed</div>"; // green box
    }
}


// ---------- 3. Get the data for the page ----------
$students = $student->getStudents();            // for the Student dropdown
$availableBooks = $book->getAvailableBooks();   // for the Book dropdown (only books with a free copy)
$borrows = $borrow->getBorrows();               // for the table

// the due date box starts at 14 days from today
$defaultDueDate = date('Y-m-d', strtotime('+14 days')); // today + 14 days
?>

<!DOCTYPE html> <!-- HTML5 page -->
<html lang="en"> <!-- start of the page -->
<head> <!-- info about the page -->
    <meta charset="UTF-8"> <!-- allows all characters -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- fits phone screens -->
    <title>Borrow LMS</title> <!-- browser tab text -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"> <!-- loads Bootstrap -->
</head>
<body> <!-- everything shown on screen -->

    <!-- Top menu -->
    <nav class="navbar navbar-expand navbar-dark bg-dark"> <!-- dark top bar -->
        <span class="navbar-brand">Library Management System</span> <!-- title on the bar -->
        <div class="navbar-nav"> <!-- menu links -->
            <a class="nav-link" href="index.php">Dashboard</a> <!-- dashboard link -->
            <a class="nav-link" href="student.php">Student</a> <!-- students link -->
            <a class="nav-link" href="book.php">Books</a> <!-- books link -->
            <a class="nav-link active" href="borrow.php">Borrowed &amp; Return Books</a> <!-- this page, highlighted -->
        </div>
    </nav>

    <div class="container mt-5"> <!-- centered area -->
        <div class="row mt-4"> <!-- row for left and right sides -->

            <!-- Left side: the form -->
            <div class="col-md-4 mx-auto"> <!-- a third of the width -->
                <div class="card shadow-sm"> <!-- white box with shadow -->
                    <div class="card-body"> <!-- space inside the box -->
                        <?php echo $message; ?> <!-- green/red message -->

                        <h2 class="text-center mb-4">Borrow a Book</h2> <!-- heading -->
                        <form method="POST" action="borrow.php"> <!-- send the data to this same page -->

                            <!-- choose the student -->
                            <div class="form-group"> <!-- label + dropdown -->
                                <label for="studentId">Student</label> <!-- text above the dropdown -->
                                <select class="form-control" id="studentId" name="student_id" required> <!-- dropdown list -->
                                    <option value="">-- choose student --</option> <!-- first choice = nothing chosen -->
                                    <?php foreach ($students as $s) { ?> <!-- repeat for every student -->
                                        <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['name']); ?></option> <!-- value = id (sent to PHP), text = name (what you see) -->
                                    <?php } ?> <!-- end of the loop -->
                                </select>
                            </div>

                            <!-- choose the book -->
                            <div class="form-group"> <!-- label + dropdown -->
                                <label for="bookId">Book</label> <!-- text above the dropdown -->
                                <select class="form-control" id="bookId" name="book_id" required> <!-- dropdown list -->
                                    <option value="">-- choose book --</option> <!-- first choice = nothing chosen -->
                                    <?php foreach ($availableBooks as $b) { ?> <!-- repeat for every available book -->
                                        <option value="<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['title']); ?> (<?php echo $b['available_copies']; ?> available)</option> <!-- shows the title and copies left -->
                                    <?php } ?> <!-- end of the loop -->
                                </select>
                            </div>

                            <!-- choose the due date -->
                            <div class="form-group"> <!-- label + date box -->
                                <label for="dueDate">Due Date</label> <!-- text above the box -->
                                <input type="date" class="form-control" id="dueDate" name="due_date" value="<?php echo $defaultDueDate; ?>" required> <!-- date picker, starts at 14 days from today -->
                            </div>

                            <button type="submit" name="borrow" class="btn btn-success btn-block">Borrow</button> <!-- name="borrow" is what step 2 looks for -->
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right side: the table -->
            <div class="col-md-8 mx-auto"> <!-- two thirds of the width -->
                <h2 class="text-center mb-4">Borrowed &amp; Returned Books</h2> <!-- heading -->

                <table class="table table-bordered table-striped"> <!-- the table -->
                    <thead class="thead-dark"> <!-- dark header -->
                        <tr>
                            <th>Student</th> <!-- column title -->
                            <th>Book</th> <!-- column title -->
                            <th>Borrowed</th> <!-- date borrowed -->
                            <th>Due</th> <!-- date it must be returned -->
                            <th>Returned</th> <!-- date it was returned -->
                            <th>Status</th> <!-- Returned / Overdue / Borrowed -->
                            <th>Action</th> <!-- Return button -->
                        </tr>
                    </thead>

                    <!-- one table row for each borrow record -->
                    <?php foreach ($borrows as $r) { ?> <!-- repeat for every record; $r is the current one -->
                    <tr> <!-- a row for this record -->
                        <td><?php echo htmlspecialchars($r['student_name']); ?></td> <!-- student name (from the JOIN) -->
                        <td><?php echo htmlspecialchars($r['book_title']); ?></td> <!-- book title (from the JOIN) -->
                        <td><?php echo $r['borrow_date']; ?></td> <!-- date borrowed -->
                        <td><?php echo $r['due_date']; ?></td> <!-- due date -->

                        <!-- return date: show "-" if the book is not returned yet -->
                        <td>
                            <?php
                            if ($r['return_date'] == null) { // not returned yet
                                echo "-"; // show a dash
                            } else { // already returned
                                echo $r['return_date']; // show the date
                            }
                            ?>
                        </td>

                        <!-- status: Returned, Overdue (late) or Borrowed -->
                        <td>
                            <?php if ($r['status'] == 'returned') { ?> <!-- already returned -->
                                <span class="badge badge-success">Returned</span> <!-- green label -->
                            <?php } elseif ($r['due_date'] < $today) { ?> <!-- not returned AND due date passed -->
                                <span class="badge badge-danger">Overdue</span> <!-- red label -->
                            <?php } else { ?> <!-- not returned, not late yet -->
                                <span class="badge badge-primary">Borrowed</span> <!-- blue label -->
                            <?php } ?>
                        </td>

                        <!-- Return button: only for books not returned yet -->
                        <td>
                            <?php if ($r['status'] == 'borrowed') { ?> <!-- only if still borrowed -->
                            <form method="POST" action="borrow.php"> <!-- tiny form for the Return button -->
                                <input type="hidden" name="return_id" value="<?php echo $r['id']; ?>"> <!-- which borrow record -->
                                <input type="hidden" name="book_id" value="<?php echo $r['book_id']; ?>"> <!-- which book -->
                                <button type="submit" class="btn btn-primary btn-sm">Return</button> <!-- blue Return button -->
                            </form>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php } ?> <!-- end of the loop -->
                </table>
            </div>

        </div>
    </div>

</body>
</html>