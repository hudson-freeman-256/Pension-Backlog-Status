<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

// Check if the form is submitted
if (isset($_POST["submit"])) {

    // Check if file was uploaded without errors
    if (isset($_FILES["fileToUpload"]) && $_FILES["fileToUpload"]["error"] == 0) {
        $allowed = array("xlsx" => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        $filename = $_FILES["fileToUpload"]["name"];
        $filetype = $_FILES["fileToUpload"]["type"];
        $filesize = $_FILES["fileToUpload"]["size"];

        // Verify file extension
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (!array_key_exists($ext, $allowed)) {
            die("Error: Please select a valid file format.");
        }

        // Verify file size - 10MB maximum
        $maxsize = 10 * 1024 * 1024;
        if ($filesize > $maxsize) {
            die("Error: File size is larger than the allowed limit.");
        }

        // Verify MIME type of the file
        if (in_array($filetype, $allowed)) {
            // Handle Excel File Upload
            $fileTmpPath = $_FILES['fileToUpload']['tmp_name'];
            
            // Load the uploaded Excel file
            $spreadsheet = IOFactory::load($fileTmpPath);
            $sheet = $spreadsheet->getActiveSheet();

            // SQL Statement Template
            $sqlStatements = "";

            // Loop through each row in the sheet (start from row 2 to skip headers)
            foreach ($sheet->getRowIterator(2) as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false); // Loop through all cells

                // Fetch cell values for each column
                $category = $cellIterator->current()->getValue(); $cellIterator->next();
                $armyNo = $cellIterator->current()->getValue(); $cellIterator->next();
                $rank = $cellIterator->current()->getValue(); $cellIterator->next();
                $name = $cellIterator->current()->getValue(); $cellIterator->next();
                $status = $cellIterator->current()->getValue(); $cellIterator->next();
                $district = $cellIterator->current()->getValue(); $cellIterator->next();

                // Create SQL INSERT statement
                $sql = sprintf(
                    "INSERT INTO army_personnel (category, army_no, rank, name, status, district) VALUES ('%s', '%s', '%s', '%s', '%s', '%s');\n",
                    $category, $armyNo, $rank, $name, $status, $district
                );

                // Append to the SQL statements string
                $sqlStatements .= $sql;
            }

            // Display the SQL statements
            echo "<h3>Generated SQL INSERT Statements:</h3>";
            echo "<pre>$sqlStatements</pre>";

            // Save SQL to a file
            $sqlFile = 'output.sql';
            file_put_contents($sqlFile, $sqlStatements);
            echo "<p>SQL output saved to <a href='$sqlFile' download>output.sql</a></p>";
        } else {
            echo "Error: There was a problem uploading your file. Please try again.";
        }
    } else {
        echo "Error: " . $_FILES["fileToUpload"]["error"];
    }
}
