<?php
session_start();
session_destroy();
unset($_SESSION['email']);
header("location:https://localhost/RawVi/Admin/index.php");
?>