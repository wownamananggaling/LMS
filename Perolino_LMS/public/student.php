<?php // start of PHP code (this top part runs first, before anything is shown)
require_once __DIR__ . '/../classes/Student.php'; // load the Student class
$student = new Student(); // make a Student object so we can use its methods

// $message shows a green or red box on top of the form
$message = ""; // empty for now = nothing shown

// These hold what is shown inside the form.
// They are empty when we are adding a NEW student.
$id = ""; // which student we are editing (empty = new student)
$name = ""; // name box
$email = ""; // email box
$phone = ""; // phone box
$registrationNumber = ""; // registration number box


// ---------- 1. DELETE button was clicked ----------
if (isset($_POST['delete_id'])) { // did the Delete form send a delete_id?
    $deleteId = $_POST['delete_id']; // the id of the student to delete

    if ($student->hasUnreturned($deleteId) == true) { // does the student still have a book?
        // don't delete a student who still has a book
        $message = "<div class='alert alert-danger'>Can't delete: this student still has unreturned books.</div>"; // red box
    } else { // no unreturned books
        $student->DeleteStudent($deleteId); // delete the student
        $message = "<div class='alert alert-success'>Student deleted</div>"; // green box
    }
}


// ---------- 2. Add / Update button was clicked ----------
if (isset($_POST['save'])) { // did the form's submit button (named "save") send something?
    // get what the user typed in the form
    $id = $_POST['id']; // hidden id (empty if new)
    $name = $_POST['name']; // typed name
    $email = $_POST['email']; // typed email
    $phone = $_POST['phone']; // typed phone
    $registrationNumber = $_POST['registration_no']; // typed registration number

    // "try" runs the code. If MySQL gives an error (for example the email
    // is already used), the code inside "catch" runs instead of crashing.
    try { // start trying
        if ($id == "") { // no id = this is a NEW student
            // no id = this is a NEW student
            $student->addStudent($name, $email, $phone, $registrationNumber); // save the new student
            $message = "<div class='alert alert-success'>Student successfully added</div>"; // green box
        } else { // there is an id = we are editing
            // has an id = we are UPDATING an old student
            $student->UpdateStudent($id, $name, $email, $phone, $registrationNumber); // save the changes
            $message = "<div class='alert alert-success'>Student successfully updated</div>"; // green box
        }

        // it worked, so clear the form
        $id = ""; // back to "new student" mode
        $name = ""; // empty the name box
        $email = ""; // empty the email box
        $phone = ""; // empty the phone box
        $registrationNumber = ""; // empty the registration box

    } catch (Exception $e) { // MySQL gave an error (like a duplicate)
        $message = "<div class='alert alert-danger'>Error: that email or registration number is already used.</div>"; // red box
    }
}


// ---------- 3. Edit button was clicked ----------
// The link looks like student.php?edit=5, so we get the student with id 5
// and put the data inside the form.
if (isset($_GET['edit'])) { // is there ?edit=something in the link?
    $found = $student->getStudent($_GET['edit']); // find that student

    if ($found != null) { // did we find one?
        $id = $found['id']; // remember the id (so the form knows it's an update)
        $name = $found['name']; // fill the name box
        $email = $found['email']; // fill the email box
        $phone = $found['phone']; // fill the phone box
        $registrationNumber = $found['registration_no']; // fill the registration box
    }
}


// ---------- 4. Title and button text of the form ----------
if ($id == "") { // no id = adding
    $formTitle = "Add New Student"; // heading text
    $buttonText = "Add"; // button text
} else { // has id = editing
    $formTitle = "Edit Student"; // heading text
    $buttonText = "Update"; // button text
}


// ---------- 5. Get the students for the table ----------
// If something was typed in the search box, only show matches.
$search = ""; // empty = no search
if (isset($_GET['search'])) { // is there ?search=something in the link?
    $search = $_GET['search']; // use it
}

if ($search == "") { // nothing typed
    $students = $student->getStudents(); // get all students
} else { // something typed
    $students = $student->searchStudents($search); // get only the matches
}
?>

<!DOCTYPE html> <!-- tells the browser this is an HTML5 page -->
<html lang="en"> <!-- start of the page, language is English -->
<head> <!-- info about the page (not shown on screen) -->
    <meta charset="UTF-8"> <!-- allows all characters, like ñ -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- makes it fit phone screens -->
    <title>Students LMS</title> <!-- text on the browser tab -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"> <!-- loads Bootstrap (the nice design) from the internet -->
</head> <!-- end of head -->
<body> <!-- everything shown on screen is inside body -->

    <!-- Top menu -->
    <nav class="navbar navbar-expand navbar-dark bg-dark"> <!-- dark top bar -->
        <span class="navbar-brand">Library Management System</span> <!-- the title on the bar -->
        <div class="navbar-nav"> <!-- holds the menu links -->
            <a class="nav-link" href="index.php">Dashboard</a> <!-- link to the dashboard -->
            <a class="nav-link active" href="student.php">Student</a> <!-- this page, so it is "active" (highlighted) -->
            <a class="nav-link" href="book.php">Books</a> <!-- link to the books page -->
            <a class="nav-link" href="borrow.php">Borrowed &amp; Return Books</a> <!-- link to the borrow page (&amp; shows as &) -->
        </div> <!-- end of links -->
    </nav> <!-- end of top bar -->

    <div class="container mt-5"> <!-- centered area; mt-5 = space on top -->
        <div class="row mt-4"> <!-- a row that holds the left and right sides -->

            <!-- Left side: the form -->
            <div class="col-md-4 mx-auto"> <!-- takes 4 of 12 columns (a third of the width) -->
                <div class="card shadow-sm"> <!-- a white box with a small shadow -->
                    <div class="card-body"> <!-- space inside the box -->
                        <?php echo $message; ?> <!-- show the green/red message (empty if none) -->

                        <h2 class="text-center mb-4"><?php echo $formTitle; ?></h2> <!-- "Add New Student" or "Edit Student" -->
                        <form method="POST" action="student.php"> <!-- when submitted, send the data to this same page -->
                            <!-- hidden: remembers which student we are editing -->
                            <input type="hidden" name="id" value="<?php echo $id; ?>"> <!-- invisible box that carries the id -->

                            <div class="form-group"> <!-- one label + input -->
                                <label for="studentName">Student Name</label> <!-- text above the box -->
                                <input type="text" class="form-control" id="studentName" name="name" placeholder="Enter student name" value="<?php echo htmlspecialchars($name); ?>" required> <!-- name box; required = can't be empty -->
                            </div>
                            <div class="form-group"> <!-- one label + input -->
                                <label for="studentEmail">Email</label> <!-- text above the box -->
                                <input type="email" class="form-control" id="studentEmail" name="email" placeholder="Enter student email" value="<?php echo htmlspecialchars($email); ?>" required> <!-- type="email" checks it looks like an email -->
                            </div>
                            <div class="form-group"> <!-- one label + input -->
                                <label for="studentPhone">Phone Number</label> <!-- text above the box -->
                                <input type="text" class="form-control" id="studentPhone" name="phone" placeholder="Enter phone number" value="<?php echo htmlspecialchars($phone); ?>" required> <!-- phone box -->
                            </div>
                            <div class="form-group"> <!-- one label + input -->
                                <label for="studentRegistrationNumber">Registration Number</label> <!-- text above the box -->
                                <input type="number" class="form-control" id="studentRegistrationNumber" name="registration_no" placeholder="Registration Number" value="<?php echo htmlspecialchars($registrationNumber); ?>" required> <!-- only numbers allowed -->
                            </div>

                            <button type="submit" name="save" class="btn btn-success btn-block"><?php echo $buttonText; ?></button> <!-- green button; name="save" is what step 2 looks for -->

                            <!-- Cancel only shows while editing -->
                            <?php if ($id != "") { ?> <!-- only when editing -->
                                <a href="student.php" class="btn btn-secondary btn-block">Cancel</a> <!-- reloads the page = empty form -->
                            <?php } ?> <!-- end of the if -->
                        </form> <!-- end of the form -->
                    </div>
                </div>
            </div> <!-- end of left side -->

            <!-- Right side: the table -->
            <div class="col-md-8 mx-auto"> <!-- takes 8 of 12 columns -->
                <h2 class="text-center mb-4">Students Table</h2> <!-- heading -->

                <!-- Search box -->
                <form method="GET" action="student.php" class="form-inline mb-3"> <!-- GET puts the text in the link: ?search=... -->
                    <input type="text" class="form-control mr-2" name="search" placeholder="Search name, email or reg. no." value="<?php echo htmlspecialchars($search); ?>"> <!-- keeps what you typed -->
                    <button type="submit" class="btn btn-dark mr-2">Search</button> <!-- search button -->
                    <a href="student.php" class="btn btn-secondary">Clear</a> <!-- reloads the page without a search -->
                </form> <!-- end of search form -->

                <table class="table table-bordered table-striped"> <!-- the table with borders and striped rows -->
                    <thead class="thead-dark"> <!-- header row, dark -->
                        <tr> <!-- one row -->
                            <th>Name</th> <!-- column title -->
                            <th>Email</th> <!-- column title -->
                            <th>Phone</th> <!-- column title -->
                            <th>Registration Number</th> <!-- column title -->
                            <th>Action</th> <!-- column title (Edit/Delete buttons) -->
                        </tr>
                    </thead>

                    <!-- one table row for each student -->
                    <?php foreach ($students as $s) { ?> <!-- repeat for every student; $s is the current one -->
                    <tr> <!-- a row for this student -->
                        <td><?php echo htmlspecialchars($s['name']); ?></td> <!-- name (htmlspecialchars makes it safe to show) -->
                        <td><?php echo htmlspecialchars($s['email']); ?></td> <!-- email -->
                        <td><?php echo htmlspecialchars($s['phone']); ?></td> <!-- phone -->
                        <td><?php echo htmlspecialchars($s['registration_no']); ?></td> <!-- registration number -->
                        <td> <!-- the buttons cell -->
                            <!-- Edit: opens this page again with ?edit=ID -->
                            <a href="student.php?edit=<?php echo $s['id']; ?>" class="btn btn-primary btn-sm">Edit</a> <!-- blue Edit button -->

                            <!-- Delete: a tiny form that sends the id -->
                            <form method="POST" action="student.php" class="d-inline" onsubmit="return confirm('Delete this student?');"> <!-- d-inline keeps it next to Edit; confirm() asks "are you sure?" -->
                                <input type="hidden" name="delete_id" value="<?php echo $s['id']; ?>"> <!-- carries this student's id -->
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button> <!-- red Delete button -->
                            </form>
                        </td>
                    </tr>
                    <?php } ?> <!-- end of the loop -->
                </table>
            </div> <!-- end of right side -->

        </div>
    </div>

</body>
</html>