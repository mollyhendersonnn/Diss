<?php

function clean($input)
{
    $data = trim($input);
    $data = stripslashes($input);
    $data = htmlspecialchars($input);
    return $input;
}

?>


remeber to ask where to put clean function