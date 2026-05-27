<?php
session_start();
session_destroy();
setcookie(session_name(), '', time()-3600, '/');
echo "<script>window.location.href='login.php';</script>";
echo "<p>Session cleared! <a href='login.php'>Click here if not redirected</a></p>";