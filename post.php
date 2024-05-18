<?php include "includes/db.php"; ?>
<?php include "includes/header.php"; ?>

<!-- Navigation -->
<?php include "includes/navigation.php"; ?>

<?php
if (isset($_POST['liked'])) {
    $post_id = $_POST['post_id'];
    $user_id = $_POST['user_id'];

    // 1. Fetching the right post
    $query = "SELECT * FROM posts WHERE post_id=$post_id";
    $postResult = mysqli_query($connection, $query);
    $post = mysqli_fetch_array($postResult);
    $likes = $post['likes'];

    // 2. Update - Incrementing likes
    mysqli_query($connection, "UPDATE posts SET likes=$likes+1 WHERE post_id=$post_id");

    // 3. Create like for post
    mysqli_query($connection, "INSERT INTO likes(user_id, post_id) VALUES($user_id, $post_id)");
    exit();
}

if (isset($_POST['unliked'])) {
    $post_id = $_POST['post_id'];
    $user_id = $_POST['user_id'];

    // 1. Fetching the right post
    $query = "SELECT * FROM posts WHERE post_id=$post_id";
    $postResult = mysqli_query($connection, $query);
    $post = mysqli_fetch_array($postResult);
    $likes = $post['likes'];

    // 2. Delete like
    mysqli_query($connection, "DELETE FROM likes WHERE post_id=$post_id AND user_id=$user_id");

    // 3. Update - Decrementing likes
    mysqli_query($connection, "UPDATE posts SET likes=$likes-1 WHERE post_id=$post_id");
    exit();
}
?>

<!-- Page Content -->
<div class="container">
    <div class="row">
        <!-- Blog Entries Column -->
        <div class="col-md-8">
            <?php
            if (isset($_GET['p_id'])) {
                $the_post_id = $_GET['p_id'];

                // Update post views count
                $update_statement = mysqli_prepare($connection, "UPDATE posts SET post_views_count = post_views_count + 1 WHERE post_id = ?");
                mysqli_stmt_bind_param($update_statement, "i", $the_post_id);
                mysqli_stmt_execute($update_statement);

                if (!$update_statement) {
                    die("Query failed");
                }

                if (isset($_SESSION['username']) && is_admin()) {
                    $stmt = mysqli_prepare($connection, "SELECT post_title, post_author, post_date, post_image, post_content FROM posts WHERE post_id = ?");
                    mysqli_stmt_bind_param($stmt, "i", $the_post_id);
                } else {
                    $stmt = mysqli_prepare($connection, "SELECT post_title, post_author, post_date, post_image, post_content FROM posts WHERE post_id = ? AND post_status = ?");
                    $published = 'published';
                    mysqli_stmt_bind_param($stmt, "is", $the_post_id, $published);
                }
                
                mysqli_stmt_execute($stmt);
                mysqli_stmt_bind_result($stmt, $post_title, $post_author, $post_date, $post_image, $post_content);
                
                while (mysqli_stmt_fetch($stmt)) {
                    ?>
                    <h1 class="page-header">Posts</h1>
                    <!-- First Blog Post -->
                    <h2>
                        <a href="#"><?php echo $post_title ?></a>
                    </h2>
                    <p class="lead">
                        by <a href="index.php"><?php echo $post_author ?></a>
                    </p>
                    <p><span class="glyphicon glyphicon-time"></span> <?php echo $post_date ?></p>

                    <?php 
                    if($post_image != ""){
                        echo '<hr>
                        <img class="img-responsive" src="/images/'.$post_image.'" alt="">
                        <hr>';
                    }
                    ?>
                    
                    
                    <p><?php echo $post_content ?></p>
                    <hr>
                    <?php
                }
                
                mysqli_stmt_free_result($stmt);
                mysqli_stmt_close($stmt);

                if(isLoggedIn()){ ?>
                    <div class="row">
                        <p class="pull-right">
                            <a
                                class="<?php echo userLikedThisPost($the_post_id) ? 'unlike' : 'like'; ?>"
                                href=""><span class="glyphicon glyphicon-thumbs-up"
                                data-toggle="tooltip"
                                data-placement="top"
                                title="<?php echo userLikedThisPost($the_post_id) ? ' I liked this before' : 'Want to like it?'; ?>"
                                ></span>
                                <?php echo userLikedThisPost($the_post_id) ? ' Unlike' : ' Like'; ?>
                            </a>
                        </p>
                    </div>
                <?php  
                } 
                else { 
                ?>
                    <div class="row">
                        <p class="pull-right login-to-post">You need to <a href="/login.php">Login</a> to like </p>
                    </div>
                <?php } ?>
                
                <div class="row">
                    <p class="pull-right likes">Likes: <?php getPostlikes($the_post_id); ?></p>
                </div>

                <?php 
				if (isset($_POST['create_comment'])) {
					$comment_author = $_POST['comment_author'];
					$comment_email = $_POST['comment_email'];
					$comment_content = $_POST['comment_content'];
					$user_id = loggedInUserId();

					if (!empty($comment_author) && !empty($comment_content)) {
						$query = "INSERT INTO comments (comment_post_id, comment_author, comment_email, comment_content, comment_status, comment_date, user_id) VALUES ($the_post_id, '{$comment_author}', '{$comment_email}', '{$comment_content}', 'unapproved', now(), '{$user_id}')";
						$create_comment_query = mysqli_query($connection, $query);

						if (!$create_comment_query) {
							die('Query failed: ' . mysqli_error($connection));
						}
					} else {
						echo 'Ensure all comment data is entered';
					}
				}
				?>

				<!-- Comments Form -->
				<div class="well">
					<h4>Leave a Comment:</h4>
					<form action="#" method="post" role="form">
						<div class="form-group">
							<label for="Author">Author</label>
							<input type="text" name="comment_author" class="form-control">
						</div>
						<div class="form-group">
							<label for="Author">Email</label>
							<input type="email" name="comment_email" class="form-control">
						</div>
						<div class="form-group">
							<label for="comment">Your Comment</label>
							<textarea name="comment_content" class="form-control" rows="3"></textarea>
						</div>
						<button type="submit" name="create_comment" class="btn btn-primary">Submit</button>
					</form>
				</div>

				<hr>
                
                <!-- Posted Comments -->
                <?php
                $query = "SELECT * FROM comments WHERE comment_post_id = {$the_post_id} AND comment_status = 'approved' ORDER BY comment_id DESC";
                $select_comment_query = mysqli_query($connection, $query);
                if (!$select_comment_query) {
                    die('Query failed: ' . mysqli_error($connection));
                }

                while ($row = mysqli_fetch_array($select_comment_query)) {
                    $comment_date = $row['comment_date'];
                    $comment_content = $row['comment_content'];
                    $comment_author = $row['comment_author'];
                    ?>
                    <div class="media">
                        <div class="media-body">
                            <h4 class="media-heading"><?php echo $comment_author; ?>
                                <small><?php echo $comment_date; ?></small>
                            </h4>
                            <?php echo $comment_content; ?>
                        </div>
                    </div>
                <?php
                }

                // Free the result set
                mysqli_free_result($select_comment_query);
                
            }
            ?>
        </div>

        <!-- Blog Sidebar Widgets Column -->
        <?php include "includes/sidebar.php"; ?>
    </div>
    <!-- /.row -->

    <hr>

<?php include "includes/footer.php"; ?>

<script>
    $(document).ready(function() {

        $("[data-toggle='tooltip']").tooltip();

        var post_id = <?php echo $the_post_id; ?>;
        var user_id = <?php echo loggedInUserId(); ?>;

        // LIKING
        $('.like').click(function() {
            $.ajax({
                url: "/post.php?p_id=<?php echo $the_post_id; ?>",
                type: 'post',
                data: {
                    'liked': 1,
                    'post_id': post_id,
                    'user_id': user_id
                },
                success: function(response) {
                    // Optionally handle success response
                    location.reload(); // Reload the page to update the likes count
                },
                error: function(xhr, status, error) {
                    console.log("An error occurred: " + error);
                }
            });
        });

        // UNLIKING
        $('.unlike').click(function() {
            $.ajax({
                url: "/post.php?p_id=<?php echo $the_post_id; ?>",
                type: 'post',
                data: {
                    'unliked': 1,
                    'post_id': post_id,
                    'user_id': user_id
                },
                success: function(response) {
                    // Optionally handle success response
                    location.reload(); // Reload the page to update the likes count
                },
                error: function(xhr, status, error) {
                    console.log("An error occurred: " + error);
                }
            });
        });

    });
</script>