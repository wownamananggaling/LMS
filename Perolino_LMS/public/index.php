<?php // start of PHP code (this top part runs first)
require_once __DIR__ . '/../classes/Student.php'; // load the Student class
require_once __DIR__ . '/../classes/Book.php'; // load the Book class
require_once __DIR__ . '/../classes/Borrow.php'; // load the Borrow class
$student = new Student(); // make a Student object
$book = new Book(); // make a Book object
$borrow = new Borrow(); // make a Borrow object

// today's date, like 2026-10-27
$today = date('Y-m-d'); // year-month-day

// get the numbers for the boxes
$totalStudents = $student->countStudents(); // how many students
$totalBooks = $book->countBooks(); // how many books
$totalActive = $borrow->countActive(); // how many books are borrowed now
$totalOverdue = $borrow->countOverdue(); // how many of those are late

// get all borrow records (we only show the unreturned ones below)
$borrows = $borrow->getBorrows(); // the full list
?>

<!DOCTYPE html> <!-- HTML5 page -->
<html lang="en"> <!-- start of the page -->
<head> <!-- info about the page -->
    <meta charset="UTF-8"> <!-- allows all characters -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- fits phone screens -->
    <title>Dashboard LMS</title> <!-- browser tab text -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"> <!-- loads Bootstrap -->
</head>
<body> <!-- everything shown on screen -->

    <!-- Top menu -->
    <nav class="navbar navbar-expand navbar-dark bg-dark"> <!-- dark top bar -->
        <span class="navbar-brand">Library Management System</span> <!-- title on the bar -->
        <div class="navbar-nav"> <!-- menu links -->
            <a class="nav-link active" href="index.php">Dashboard</a> <!-- this page, highlighted -->
            <a class="nav-link" href="student.php">Student</a> <!-- students link -->
            <a class="nav-link" href="book.php">Books</a> <!-- books link -->
            <a class="nav-link" href="borrow.php">Borrowed &amp; Return Books</a> <!-- borrow link -->
        </div>
    </nav>

    <div class="container mt-5"> <!-- centered area -->
        <h2 class="text-center mb-4">Dashboard</h2> <!-- heading -->

        <!-- The 4 number boxes -->
        <div class="row text-center mb-4"> <!-- a row; text-center centers the text -->
            <div class="col-md-3"> <!-- each box takes 3 of 12 columns = 4 boxes in a row -->
                <div class="card shadow-sm"><div class="card-body"> <!-- white box -->
                    <h2><?php echo $totalStudents; ?></h2> <!-- the big number -->
                    Students <!-- the label -->
                </div></div>
            </div>
            <div class="col-md-3"> <!-- second box -->
                <div class="card shadow-sm"><div class="card-body">
                    <h2><?php echo $totalBooks; ?></h2> <!-- the big number -->
                    Books <!-- the label -->
                </div></div>
            </div>
            <div class="col-md-3"> <!-- third box -->
                <div class="card shadow-sm"><div class="card-body">
                    <h2><?php echo $totalActive; ?></h2> <!-- the big number -->
                    Active Borrows <!-- the label -->
                </div></div>
            </div>
            <div class="col-md-3"> <!-- fourth box -->
                <div class="card shadow-sm"><div class="card-body text-danger"> <!-- text-danger = red text -->
                    <h2><?php echo $totalOverdue; ?></h2> <!-- the big number -->
                    Overdue <!-- the label -->
                </div></div>
            </div>
        </div>

        <!-- Table of books that are borrowed right now -->
        <h4 class="mb-3">Active Borrows</h4> <!-- smaller heading -->
        <table class="table table-bordered table-striped"> <!-- the table -->
            <thead class="thead-dark"> <!-- dark header -->
                <tr>
                    <th>Student</th> <!-- column title -->
                    <th>Book</th> <!-- column title -->
                    <th>Borrowed</th> <!-- column title -->
                    <th>Due</th> <!-- column title -->
                    <th>Status</th> <!-- column title -->
                </tr>
            </thead>

            <?php foreach ($borrows as $r) { ?> <!-- go through every borrow record -->
                <!-- skip the books that were already returned -->
                <?php if ($r['status'] == 'borrowed') { ?> <!-- only show the unreturned ones -->
                <tr> <!-- a row for this record -->
                    <td><?php echo htmlspecialchars($r['student_name']); ?></td> <!-- student name -->
                    <td><?php echo htmlspecialchars($r['book_title']); ?></td> <!-- book title -->
                    <td><?php echo $r['borrow_date']; ?></td> <!-- date borrowed -->
                    <td><?php echo $r['due_date']; ?></td> <!-- due date -->
                    <td>
                        <?php if ($r['due_date'] < $today) { ?> <!-- due date already passed -->
                            <span class="badge badge-danger">Overdue</span> <!-- red label -->
                        <?php } else { ?> <!-- still on time -->
                            <span class="badge badge-primary">Borrowed</span> <!-- blue label -->
                        <?php } ?>
                    </td>
                </tr>
                <?php } ?> <!-- end of the "only borrowed" check -->
            <?php } ?> <!-- end of the loop -->
        </table>
    </div>

</body>
</html>