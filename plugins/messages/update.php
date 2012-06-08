<?php

require_once(dirname(dirname(__FILE__)).'/lib.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['message'])) {
    dashboard_push_data('messages', $_POST['message']);
}

?>
<html>
    <head>
        <title>Dashboard message</title>
    </head>

    <body>

        <form method="post">
            <textarea name="message" cols="50" rows="5"></textarea><br />
            <input type="submit" value="Send" />
        </form>

    </body>
</html>
