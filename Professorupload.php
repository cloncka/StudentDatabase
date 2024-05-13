<?php
$import_attempted = false;
$import_succeeded = false;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $import_attempted = true;

    // connect to the database
    $con = @mysqli_connect(
        hostname: "localhost",
        username: "cs370",
        password: "group4",
        database: "csproject"
    );

    // check if connection failed
    if (mysqli_connect_errno()) {
        $import_error_message = "Failed to connect to MySQL: "
            . mysqli_connect_error();
    } else {
        // process CSV files
        $csv_files = ["CS_Project_File1.csv"];

        foreach ($csv_files as $csv_file) {
            try {
                // read CSV file
                $filename = $_FILES["importFile"]["tmp_name"];
                $contents = file_get_contents($filename);
                $lines = explode("\n", $contents);

                // initialize counters
                $inserted_rows = 0;
                $updated_rows = 0;

                // process each line in the CSV file
                foreach ($lines as $line) {
                    // validate the CSV line
                    // ...

                    // parse CSV line
                    $parsed_csv_line = str_getcsv($line);
                    //Department, Professor, ProfessorsCourses
                    // check if the row already exists in the database
                    $query = "SELECT COUNT(*) FROM departments WHERE DepartmentID = '{$parsed_csv_line[0]}'";
                    $result = mysqli_query($con, $query);
                    $row_exists = mysqli_fetch_array($result)[0];

                    if ($row_exists) {
                        // update row
                        $query = "UPDATE departments SET DepartmentName = '{$parsed_csv_line[1]}' WHERE DepartmentID = '{$parsed_csv_line[0]}'";
                        mysqli_query($con, $query);
                        $updated_rows++;
                    } else {
                        // insert row
                        $query = "INSERT INTO departments (DepartmentID, DepartmentName) VALUES ('{$parsed_csv_line[0]}', '{$parsed_csv_line[1]}')";
                        mysqli_query($con, $query);
                        $inserted_rows++;
                    }
                    /////////////////////////////////////////////////
                    $query = "SELECT COUNT(*) FROM professors WHERE ProfessorID = '{$parsed_csv_line[2]}'";
                    $result = mysqli_query($con, $query);
                    $row_exists = mysqli_fetch_array($result)[0];

                    if ($row_exists) {
                        // update row
                        $query = "UPDATE professors SET DepartmentID = '{$parsed_csv_line[0]}', ProfessorName= '{$parsed_csv_line[1]}', Office= '{$parsed_csv_line[2]}', OfficeHours= '{$parsed_csv_line[3]}'  WHERE ProfessorID= '{$parsed_csv_line[4]}'";
                        mysqli_query($con, $query);
                        $updated_rows++;
                    } else {
                        // insert row
                        $query = "INSERT INTO professors (ProfessorID, DepartmentID, ProfessorName, Office, OfficeHours) VALUES ('{$parsed_csv_line[2]}', '{$parsed_csv_line[3]}', '{$parsed_csv_line[4]}','{$parsed_csv_line[5]}','{$parsed_csv_line[6]}')";
                        mysqli_query($con, $query);
                        $inserted_rows++;

                    }
                    ///////////////////////////////
                    //SELECT COUNT(*) FROM table_c WHERE col1 = 'value1' AND col2 = 'value2'

                    $query = "SELECT COUNT(*) FROM professorscourses WHERE professorID= '{$parsed_csv_line[7]}' AND  CourseID = '{$parsed_csv_line[8]}'";
                    $result = mysqli_query($con, $query);
                    $row_exists = mysqli_fetch_array($result)[0];

                    if ($row_exists) {
                        // update row
                        $query = "UPDATE professorscourses SET ProfessorID='{$parsed_csv_line[7]} WHERE CourseID= '{$parsed_csv_line[8]}'";
                        mysqli_query($con, $query);
                        $updated_rows++;
                    } else {
                        // insert row
                        $query = "INSERT INTO professorscourses (ProfessorID, CourseID) VALUES ('{$parsed_csv_line[7]}', '{$parsed_csv_line[8]}' )";
                        mysqli_query($con, $query);
                        $inserted_rows++;

                    }


                    // TODO: insert or update data in the other tables as needed
                    ////////////


                    $import_succeeded = true;

                }
            } catch (Exception $exception) {
                $import_error_message = $exception->getMessage()
                    . "at: " . $exception->getFile()
                    . " (line " . $exception->getLine() . ") <br/>";
            }
        }

        mysqli_close($con); // close database connection
    }
}
?>

<html>

<head>
    <title>Student Data Import</title>
</head>

<body>
<h1>Student Data Import</h1>

<?php
if ($import_attempted) {
    if ($import_succeeded) {
        // if import succeeded, show success message
        ?>
        <h1><span style='color: green;'>Import Succeeded!</span></h1>
        <p>
            <?php echo "{$inserted_rows} rows inserted, {$updated_rows} rows updated"; ?>
        </p>
    <?php } else {
        // if import failed, show error message
        ?>
        <h1><span style='color: red;'>Import Failed!</span></h1>
        <span style='color: red;'>
                <?php echo $import_error_message; ?>
            </span>


        <br /><br />
    <?php }
}
?>

<form method="post" enctype="multipart/form-data">
    File: <input type="file" name="importFile" />
    <br /><br />
    <input type="submit" value="Upload Data" />
</form>
</body>

</html>