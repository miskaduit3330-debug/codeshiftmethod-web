<?php
// Pošle Miške e-mail, keď si niekto vypýta zošit „Vezmi si kontrolu späť“.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
$meno  = trim(mb_substr(strip_tags($_POST['meno'] ?? ''), 0, 80));
$email = trim(mb_substr($_POST['email'] ?? '', 0, 120));
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { http_response_code(400); exit; }

$kam = 'miskaduit3330@gmail.com';
$cas = date('d. m. Y H:i');
$predmet = '=?UTF-8?B?' . base64_encode('Nový zošit: ' . ($meno ?: $email)) . '?=';
$text = "Niekto si práve vypýtal zošit Vezmi si kontrolu späť.\n\nMeno: " . ($meno ?: '(nevyplnila)') . "\nE-mail: $email\nČas: $cas\n";
$hlavicky = "From: Code Shift Method <info@codeshiftmethod.com>\r\nReply-To: $email\r\nContent-Type: text/plain; charset=UTF-8\r\n";
@mail($kam, $predmet, $text, $hlavicky);

@file_put_contents(__DIR__ . '/../../zosit-ziadosti.csv', "$cas;$meno;$email\n", FILE_APPEND | LOCK_EX);
echo 'ok';
