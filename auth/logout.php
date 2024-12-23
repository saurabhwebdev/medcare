<?php
session_start();
session_destroy();
header('Location: /pms/index.php');
exit();
?> 