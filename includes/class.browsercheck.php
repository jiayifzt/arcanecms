<?php
    $ua = $_SERVER['HTTP_USER_AGENT'];
    if (strpos($ua,'MSIE') != false && strpos($ua,'Opera') === false)
    {
        if (substr($ua,strpos($ua,'MSIE')+5,1) < 8)
        {
            /* the browser claims to be IE7 or older, and is not Opera, Safari or iCab */
            header("Location: unsupportedbrowser.php");
        }
    }
?>
