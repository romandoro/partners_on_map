<?php
$password = 'demo'; // indicare la passw
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);
echo "Password hash: " . $hashedPassword;
