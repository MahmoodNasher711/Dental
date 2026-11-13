<?php
require_once __DIR__ . '/includes/bootstrap.php';

auth_require_booking_login(DENTAL_BASE_URL . '/booking.php');
header('Location: ' . DENTAL_BASE_URL . '/booking.php', true, 302);
exit;
