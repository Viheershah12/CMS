<?php  include "includes/db.php"; ?>
<?php  include "includes/header.php"; ?>

<?php

if(is_the_logged_in_user_owner(156)){

    echo "YES";

} else {

    echo "NO";

}