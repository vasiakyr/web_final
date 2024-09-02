
<?php
            $connect = mysqli_connect("localhost", "root", "", "web_project"); 

            $query = '';
            $table_data = '';
          
            // json file name
            $filename = "categories.json";
          
            // Read the JSON file in PHP
            $data = file_get_contents($filename); 
          
            // Convert the JSON String into PHP Array
            $array = json_decode($data, true); 
          
            // Extracting row by row
            foreach($array as $row) {

                // Database query to insert data 
                // into database Make Multiple 
                // Insert Query 
                $query .= 
                "INSERT INTO cate VALUES 
                ('".$row["id"]."', '".$row["category_name"]."'); "; 
             
                $table_data .= '
                <tr>
                    <td>'.$row["id"].'</td>
                    <td>'.$row["category_name"].'</td>
                
                </tr>
                '; // Data for display on Web page
            }

            if(mysqli_multi_query($connect, $query)) {
                echo '<h3>Inserted JSON Data</h3><br />';
                echo '
                <table class="table table-bordered">
                <tr>
                    <th width="45%">Name</th>
                    <th width="10%">Gender</th>
                    <th width="45%">Subject</th>
                </tr>
                ';
                echo $table_data;  
                echo '</table>';
            }
          ?>
        <br />
    </div>
</body>

</html>
