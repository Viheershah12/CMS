<script>
    tinymce.init({
        selector: "#post_content"
    });
</script>

<?php
if (isset($_POST['create_post'])) {
    $post_title = escape($_POST['title']);
    $post_user = escape(get_user_name());
    $post_userId = loggedInUserId();
    $post_category_id = escape($_POST['post_category']);
    $post_status = escape($_POST['post_status']);

    $post_image = escape($_FILES['image']['name']);
    $post_image_temp = $_FILES['image']['tmp_name']; // Do not escape the temporary name

    $post_tags = escape($_POST['post_tags']);
    $post_content = escape($_POST['post_content']);
    $post_date = escape(date('d-m-y'));

    // Insert the post data into the database initially with an empty image field to get the post ID
    $query = "INSERT INTO posts(post_category_id, post_title, user_id, post_author, post_user, post_date, post_image, post_content, post_tags, post_status) ";
    $query .= "VALUES({$post_category_id}, '{$post_title}', '{$post_userId}', '{$post_user}', '{$post_user}', now(), '', '{$post_content}', '{$post_tags}', '{$post_status}')";

    $create_post_query = mysqli_query($connection, $query);

    confirmQuery($create_post_query);

    $the_post_id = mysqli_insert_id($connection); // Get the last inserted ID

    // Generate a new image name by concatenating the post ID
    $post_image_new_name = $the_post_id . "_" . basename($post_image);

    // Define the target directory
    $target_directory = $_SERVER['DOCUMENT_ROOT'] . "/images/";

    // Ensure the directory exists
    if (!is_dir($target_directory)) {
        mkdir($target_directory, 0777, true); // Create the directory if it doesn't exist
    }

    // Define the target file path
    $target_file = $target_directory . $post_image_new_name;

    // Move the uploaded file to the target directory
    if (move_uploaded_file($post_image_temp, $target_file)) {
        //echo "The file " . basename($post_image_new_name) . " has been uploaded.";

        // Update the post with the new image name
        $query = "UPDATE posts SET post_image = '{$post_image_new_name}' WHERE post_id = {$the_post_id}";
        $update_image_query = mysqli_query($connection, $query);

        confirmQuery($update_image_query);
    } else {
        //echo "Sorry, there was an error uploading your file.";
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
            $select_categories = mysqli_query($connection, $query);
            confirmQuery($select_categories);

            while ($row = mysqli_fetch_assoc($select_categories)) {
                $cat_id = $row['cat_id'];
                $cat_title = $row['cat_title'];
                echo "<option value='$cat_id'>{$cat_title}</option>";
            }
            ?>
        </select>
    </div>

    <div class="form-group">
        <select name="post_status" id="">
            <option value="draft">Post Status</option>
            <option value="published">Published</option>
            <option value="draft">Draft</option>
        </select>
    </div>

    <div class="form-group">
        <label for="post_image">Post Image</label>
        <input type="file" name="image">
    </div>

    <div class="form-group">
        <label for="post_tags">Post Tags</label>
        <input type="text" class="form-control" name="post_tags">
    </div>

    <div class="form-group">
        <label for="post_content">Post Content</label>
        <textarea class="form-control" name="post_content" id="post_content" cols="30" rows="10"></textarea>
    </div>

    <div class="form-group">
        <input class="btn btn-primary" type="submit" name="create_post" value="Publish Post">
    </div>
</form>
