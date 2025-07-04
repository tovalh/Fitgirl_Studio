<?php
// admin/logout.php

// 1. Reanudamos la sesión existente.
// Es necesario para poder acceder a la sesión antes de destruirla.
session_start();

// 2. Destruimos todas las variables de sesión.
// Esto "vacía la mochila" del visitante, borrando su estado de "logueado".
$_SESSION = array();

// 3. Destruimos la sesión completamente.
// Esto invalida el "ticket" del visitante en el servidor.
session_destroy();

// 4. Redirigimos al usuario a la página de login.
// Como la sesión ya no existe, no podrá volver al dashboard sin loguearse de nuevo.
header("location: login.html");
exit;
?>