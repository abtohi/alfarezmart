<?php
$conn = mysqli_connect('127.0.0.1', 'root', '', 'alfarezmart');
if (!$conn) die('Connect Error');
$res = mysqli_query($conn, 'SELECT * FROM digi_deposit_logs ORDER BY id DESC LIMIT 5');
while ($row = mysqli_fetch_assoc($res)) {
    echo "ID: " . $row['id'] . "\nBank: " . $row['bank'] . "\nNotes: " . $row['notes'] . "\nRaw: " . $row['raw'] . "\n\n";
}
