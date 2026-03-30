<?php
require "includes/session.php";

// Destroi todas as variáveis da sessão
session_unset();

// Destroi a sessão
session_destroy();

// Redireciona para login
header("Location: login.php");
exit();
