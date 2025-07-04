<?php
// admin/login_handler.php

// --- PASO 1: INICIAR LA SESIÓN ---
// session_start() es lo primero que debemos hacer.
// Crea una sesión o reanuda la actual, permitiéndonos guardar
// información (como que el usuario está logueado) entre diferentes páginas.
session_start();


// --- PASO 2: DEFINIR LAS CREDENCIALES CORRECTAS ---
// Por ahora, las credenciales estarán "hardcodeadas" (escritas directamente en el código).
// En un proyecto más grande, esto vendría de una consulta a la base de datos.
$usuario_correcto = "admin";
$password_correcto = "fitgirl2025"; // ¡Usa una contraseña más segura en un proyecto real!


// --- PASO 3: RECIBIR Y VERIFICAR LOS DATOS DEL FORMULARIO ---
// Verificamos que los datos han sido enviados vía POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Recogemos el usuario y la contraseña enviados desde el formulario
    $usuario_ingresado = $_POST['username'];
    $password_ingresada = $_POST['password'];

    // Comparamos los datos ingresados con las credenciales correctas
    if ($usuario_ingresado === $usuario_correcto && $password_ingresada === $password_correcto) {

        // --- PASO 4: CREDENCIALES CORRECTAS ---
        // Si las credenciales son correctas, guardamos una "bandera" en la sesión
        // para recordar que este visitante está autenticado.
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $usuario_ingresado;

        // Redirigimos al usuario al panel principal (que crearemos a continuación)
        header("Location: dashboard.php");
        exit(); // Detenemos la ejecución del script

    } else {

        // --- PASO 5: CREDENCIALES INCORRECTAS ---
        // Si las credenciales son incorrectas, guardamos un mensaje de error en la sesión.
        $_SESSION['error'] = "Usuario o contraseña incorrectos.";

        // Redirigimos de vuelta a la página de login para que lo intente de nuevo.
        header("Location: login.html");
        exit();
    }
} else {
    // Si alguien intenta acceder a este archivo directamente sin enviar datos,
    // simplemente lo redirigimos al login.
    header("Location: login.html");
    exit();
}
?>