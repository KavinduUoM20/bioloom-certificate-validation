<?php
/**
 * Default entry – redirect to login or register (Bioloom Islands Pvt Ltd)
 */
session_start();
if (!empty($_SESSION['auth_ok'])) {
    header('Location: register.php', true, 302);
} else {
    header('Location: login.php', true, 302);
}
exit;
