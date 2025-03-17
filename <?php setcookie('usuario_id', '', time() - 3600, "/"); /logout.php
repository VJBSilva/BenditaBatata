<?php
setcookie('usuario_id', '', time() - 3600, "/"); // Remove o cookie
header("Location: index.php");
exit();
?>
