<?php // start of PHP code (this top part runs first)
require_once __DIR__ . '/../classes/Book.php'; // load the Book class
$book = new Book(); // make a Book object

// $message shows a green or red box on top of the form
$message = ""; // empty for now

// These hold what is shown inside the form.
// They are empty when we are adding a NEW book.
$id = ""; // which book we are editing (empty = new book)
$title = ""; // title box
$author = ""; // author box
$isbn = ""; // isbn box
$category = ""; // category box
$copies = 1; // total copies box (starts at 1)


// ---------- 1. DELETE button was clicked ----------
if (isset($_POST['delete_id'])) { // did the Delete form send a delete_id?
    $deleteId = $_POST['delete_id']; // the id of the book to delete

    if ($book->hasUnreturned($deleteId) == true) { // is any copy still borrowed?
        // don't delete a book that is still borrowed
        $message = "<div class='alert alert-danger'>Can't delete: some copies are still borrowed.</div>"; // red box
    } else { // all copies are back
        $book->deleteBook($deleteId); // delete the book
        $message = "<div class='alert alert-success'>Book deleted</div>"; // green box
    }
}


// ---------- 2. Add / Update button was clicked ----------
if (isset($_POST['save'])) { // did the submit button (named "save") send something?
    // get what the user typed in the form
    $id = $_POST['id']; // hidden id (empty if new)
    $title = $_POST['title']; // typed title
    $author = $_POST['author']; // typed author
    $isbn = $_POST['isbn']; // typed isbn
    $category = $_POST['category']; // typed category
    $copies = $_POST['copies']; // typed total copies

    // "try" runs the code. If MySQL gives an error (for example the ISBN
    // is already used), the code inside "catch" runs instead of crashing.
    try { // start trying
        if ($id == "") { // no id = this is a NEW book
            // no id = this is a NEW book
            $book->addBook($title, $author, $isbn, $category, $copies); // save the new book
            $message = "<div class='alert alert-success'>Book successfully added</div>"; // green box

            // it worked, so clear the form
            $id = ""; // back to "new book" mode
            $title = ""; // empty the title box
            $author = ""; // empty the author box
            $isbn = ""; // empty the isbn box
            $category = ""; // empty the category box
            $copies = 1; // reset copies to 1

        } else { // there is an id = we are editing
            // has an id = we are UPDATING an old book
            $worked = $book->updateBook($id, $title, $author, $isbn, $category, $copies); // true if saved, false if copies too low

            if ($worked == true) { // it saved
                $message = "<div class='alert alert-success'>Book successfully updated</div>"; // green box

                // it worked, so clear the form
                $id = ""; // back to "new book" mode
                $title = ""; // empty the title box
                $author = ""; // empty the author box
                $isbn = ""; // empty the isbn box
                $category = ""; // empty the category box
                $copies = 1; // reset copies to 1
            } else { // updateBook said no
                $message = "<div class='alert alert-danger'>Total copies can't be less than the copies that are borrowed.</div>"; // red box (form keeps what was typed)
            }
        }

    } catch (Exception $e) { // MySQL gave an error (like a duplicate ISBN)
        $message = "<div class='alert alert-danger'>Error: that ISBN is already used.</div>"; // red box
    }
}


// ---------- 3. Edit button was clicked ----------
// The link looks like book.php?edit=5, so we get the book with id 5
// and put the data inside the form.
if (isset($_GET['edit'])) { // is there ?edit=something in the link?
    $found = $book->getBook($_GET['edit']); // find that book

    if ($found != null) { // did we find one?
        $id = $found['id']; // remember the id (so the form knows it's an update)
        $title = $found['title']; // fill the title box
        $author = $found['author']; // fill the author box
        $isbn = $found['isbn']; // fill the isbn box
        $category = $found['category']; // fill the category box
        $copies = $found['copies']; // fill the copies box
    }
}


// ---------- 4. Title and button text of the form ----------
if ($id == "") { // no id = adding
    $formTitle = "Add New Book"; // heading text
    $buttonText = "Add"; // button text
} else { // has id = editing
    $formTitle = "Edit Book"; // heading text
    $buttonText = "Update"; // button text
}


// ---------- 5. Get the books for the table ----------
// If something was typed in the search box, only show matches.
$search = ""; // empty = no search
if (isset($_GET['search'])) { // is there ?search=something in the link?
    $search = $_GET['search']; // use it
}

if ($search == "") { // nothing typed
    $books = $book->getBooks(); // get all books
} else { // something typed
    $books = $book->searchBooks($search); // get only the matches
}
?>

<!DOCTYPE html> <!-- HTML5 page -->
<html lang="en"> <!-- start of the page -->
<head> <!-- info about the page -->
    <meta charset="UTF-8"> <!-- allows all characters -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- fits phone screens -->
    <title>Books LMS</title> <!-- browser tab text -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"> <!-- loads Bootstrap -->
</head>
<body> <!-- everything shown on screen -->

    <!-- Top menu -->
    <nav class="navbar navbar-expand navbar-dark bg-dark"> <!-- dark top bar -->
        <span class="navbar-brand">Library Management System</span> <!-- title on the bar -->
        <div class="navbar-nav"> <!-- menu links -->
            <a class="nav-link" href="index.php">Dashboard</a> <!-- dashboard link -->
            <a class="nav-link" href="student.php">Student</a> <!-- students link -->
            <a class="nav-link active" href="book.php">Books</a> <!-- this page, highlighted -->
            <a class="nav-link" href="borrow.php">Borrowed &amp; Return Books</a> <!-- borrow link -->
        </div>
    </nav>

    <div class="container mt-5"> <!-- centered area -->
        <div class="row mt-4"> <!-- row for left and right sides -->

            <!-- Left side: the form -->
            <div class="col-md-4 mx-auto"> <!-- a third of the width -->
                <div class="card shadow-sm"> <!-- white box with shadow -->
                    <div class="card-body"> <!-- space inside the box -->
                        <?php echo $message; ?> <!-- green/red message -->

                        <h2 class="text-center mb-4"><?php echo $formTitle; ?></h2> <!-- "Add New Book" or "Edit Book" -->
                        <form method="POST" action="book.php"> <!-- send the data to this same page -->
                            <!-- hidden: remembers which book we are editing -->
                            <input type="hidden" name="id" value="<?php echo $id; ?>"> <!-- invisible box with the id -->

                            <div class="form-group"> <!-- label + input -->
                                <label for="bookTitle">Title</label> <!-- text above the box -->
                                <input type="text" class="form-control" id="bookTitle" name="title" placeholder="Enter book title" value="<?php echo htmlspecialchars($title); ?>" required> <!-- title box -->
                            </div>
                            <div class="form-group"> <!-- label + input -->
                                <label for="bookAuthor">Author</label> <!-- text above the box -->
                                <input type="text" class="form-control" id="bookAuthor" name="author" placeholder="Enter author" value="<?php echo htmlspecialchars($author); ?>" required> <!-- author box -->
                            </div>
                            <div class="form-group"> <!-- label + input -->
                                <label for="bookIsbn">ISBN</label> <!-- text above the box -->
                                <input type="text" class="form-control" id="bookIsbn" name="isbn" placeholder="Enter ISBN" value="<?php echo htmlspecialchars($isbn); ?>" required> <!-- isbn box -->
                            </div>
                            <div class="form-group"> <!-- label + input -->
                                <label for="bookCategory">Category</label> <!-- text above the box -->
                                <input type="text" class="form-control" id="bookCategory" name="category" placeholder="Enter category" value="<?php echo htmlspecialchars($category); ?>"> <!-- category box (not required) -->
                            </div>
                            <div class="form-group"> <!-- label + input -->
                                <label for="bookCopies">Total Copies</label> <!-- text above the box -->
                                <input type="number" min="1" class="form-control" id="bookCopies" name="copies" value="<?php echo htmlspecialchars($copies); ?>" required> <!-- number box; min="1" = at least 1 -->
                            </div>

                            <button type="submit" name="save" class="btn btn-success btn-block"><?php echo $buttonText; ?></button> <!-- green Add/Update button -->

                            <!-- Cancel only shows while editing -->
                            <?php if ($id != "") { ?> <!-- only when editing -->
                                <a href="book.php" class="btn btn-secondary btn-block">Cancel</a> <!-- reloads page = empty form -->
                            <?php } ?>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right side: the table -->
            <div class="col-md-8 mx-auto"> <!-- two thirds of the width -->
                <h2 class="text-center mb-4">Books Table</h2> <!-- heading -->

                <!-- Search box -->
                <form method="GET" action="book.php" class="form-inline mb-3"> <!-- puts the text in the link: ?search=... -->
                    <input type="text" class="form-control mr-2" name="search" placeholder="Search title, author, ISBN or category" value="<?php echo htmlspecialchars($search); ?>"> <!-- search box -->
                    <button type="submit" class="btn btn-dark mr-2">Search</button> <!-- search button -->
                    <a href="book.php" class="btn btn-secondary">Clear</a> <!-- clear the search -->
                </form>

                <table class="table table-bordered table-striped"> <!-- the table -->
                    <thead class="thead-dark"> <!-- dark header -->
                        <tr>
                            <th>Title</th> <!-- column title -->
                            <th>Author</th> <!-- column title -->
                            <th>ISBN</th> <!-- column title -->
                            <th>Category</th> <!-- column title -->
                            <th>Available / Total</th> <!-- copies on the shelf / copies owned -->
                            <th>Action</th> <!-- Edit/Delete -->
                        </tr>
                    </thead>

                    <!-- one table row for each book -->
                    <?php foreach ($books as $b) { ?> <!-- repeat for every book -->
                    <tr> <!-- a row for this book -->
                        <td><?php echo htmlspecialchars($b['title']); ?></td> <!-- title -->
                        <td><?php echo htmlspecialchars($b['author']); ?></td> <!-- author -->
                        <td><?php echo htmlspecialchars($b['isbn']); ?></td> <!-- isbn -->
                        <td><?php echo htmlspecialchars($b['category']); ?></td> <!-- category -->
                        <td><?php echo $b['available_copies']; ?> / <?php echo $b['copies']; ?></td> <!-- for example: 2 / 3 -->
                        <td> <!-- the buttons cell -->
                            <!-- Edit: opens this page again with ?edit=ID -->
                            <a href="book.php?edit=<?php echo $b['id']; ?>" class="btn btn-primary btn-sm">Edit</a> <!-- blue Edit button -->

                            <!-- Delete: a tiny form that sends the id -->
                            <form method="POST" action="book.php" class="d-inline" onsubmit="return confirm('Delete this book?');"> <!-- asks "are you sure?" -->
                                <input type="hidden" name="delete_id" value="<?php echo $b['id']; ?>"> <!-- carries this book's id -->
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button> <!-- red Delete button -->
                            </form>
                        </td>
                    </tr>
                    <?php } ?> <!-- end of the loop -->
                </table>
            </div>

        </div>
    </div>

</body>
</html>