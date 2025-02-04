<?php

function clean($input)
{
    $data = trim($input);
    $data = stripslashes($input);
    $data = htmlspecialchars($input);
    return $data;
}

?>
