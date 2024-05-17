
<script>
      tinymce.init({
        selector: "#post_content"
      });
    </script>

<?php
   

   if(isset($_POST['create_post'])) {
   
            $post_title        = escape($_POST['title']);
            $post_user         = escape(get_user_name());
            $post_userId       = loggedInUserId();
            $post_category_id  = escape($_POST['post_category']);
            $post_status       = escape($_POST['post_status']);
    
            $post_image        = escape($_FILES['image']['name']);
            $post_image_temp   = escape($_FILES['image']['tmp_name']);
    
    
            $post_tags         = escape($_POST['post_tags']);
            $post_content      = escape($_POST['post_content']);
            $post_date         = escape(date('d-m-y'));

       
      // Move the uploaded file to a designated directory
      // $target_dir = "/images/";
      // $target_file = $target_dir . basename(base64_encode($_FILES['image']));     
       
      $query = "INSERT INTO posts(post_category_id, post_title, user_id, post_author, post_user, post_date,post_image,post_content,post_tags,post_status) ";
             
      $query .= "VALUES({$post_category_id},'{$post_title}','{$post_userId}', '{$post_user}', '{$post_user}',now(),'{$post_image}','{$post_content}','{$post_tags}', '{$post_status}') "; 
             
      $create_post_query = mysqli_query($connection, $query);  
          
      confirmQuery($create_post_query);

      $the_post_id = mysqli_insert_id($connection);

      // Check if the file is uploaded successfully
      if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        // File details
        $file_name = $_FILES['image']['name'];
        $file_tmp_name = $_FILES['image']['tmp_name'];
        $file_type = $_FILES['image']['type'];

        // Convert image to binary data and then to base64
        $image_data = file_get_contents($file_tmp_name);
        $image_data_base64 = base64_encode($image_data);

        // Sanitize inputs
        $file_name = mysqli_real_escape_string($connection, $file_name);
        $image_data_base64 = mysqli_real_escape_string($connection, $image_data_base64);
        $file_type = mysqli_real_escape_string($connection, $file_type);

        // Insert query
        $query = "INSERT INTO picture(post_id, name, binary_value, content_type) ";
        $query .= "VALUES ('$the_post_id', '$file_name', '$image_data_base64', '$file_type')";

        // Execute the query
        if (mysqli_query($connection, $query)) {
            echo "File inserted successfully.";
        } else {
            echo "Error inserting file: " . mysqli_error($connection);
        }
      } else {
        echo "Error uploading file.";
      }


      echo "<p class='bg-success'>Post Created. <a href='../post.php?p_id={$the_post_id}'>View Post </a> or <a href='posts.php'>Edit More Posts</a></p>";
       


   }
    

    
    
?>

    <form action="" method="post" enctype="multipart/form-data">    
     
     
      <div class="form-group">
         <label for="title">Post Title</label>
          <input type="text" class="form-control" name="title">
      </div>

         <div class="form-group">
       <label for="category">Category</label>
       <select name="post_category" id="">
           
<?php

        $query = "SELECT * FROM categories";
        $select_categories = mysqli_query($connection,$query);
        
        confirmQuery($select_categories);


        while($row = mysqli_fetch_assoc($select_categories )) {
        $cat_id = $row['cat_id'];
        $cat_title = $row['cat_title'];
            
            
            echo "<option value='$cat_id'>{$cat_title}</option>";
         
            
        }

?>
           
        
       </select>
      
      </div>


       <!-- <div class="form-group">
       <label for="users">Users</label>
       <select name="post_user" id="">
           
<?php

        // $users_query = "SELECT * FROM users";
        // $select_users = mysqli_query($connection,$users_query);
        
        // confirmQuery($select_users);


        // while($row = mysqli_fetch_assoc($select_users)) {
        // $user_id = $row['user_id'];
        // $username = $row['username'];
            
            
        //     echo "<option value='{$username}'>{$username}</option>";
         
            
        // }

?>
           
        
       </select>
      
      </div> -->





      <!-- <div class="form-group">
         <label for="title">Post Author</label>
          <input type="text" class="form-control" name="author">
      </div> -->
      
      

       <div class="form-group">
         <select name="post_status" id="">
             <option value="draft">Post Status</option>
             <option value="published">Published</option>
             <option value="draft">Draft</option>
         </select>
      </div>
      
      
      
    <!-- <div class="form-group">
         <label for="post_image">Post Image</label>
          <input type="file"  name="image">
      </div> -->

      <div class="form-group">
         <label for="post_tags">Post Tags</label>
          <input type="text" class="form-control" name="post_tags">
      </div>
      
      <div class="form-group">
         <label for="post_content">Post Content</label>
         <textarea class="form-control "name="post_content" id="post_content" cols="30" rows="10">
         </textarea>
      </div>
      
      

       <div class="form-group">
          <input class="btn btn-primary" type="submit" name="create_post" value="Publish Post">
      </div>


</form>
    