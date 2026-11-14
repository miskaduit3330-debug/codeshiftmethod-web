<?php
// Zošit „Vezmi si kontrolu späť“: pošle žene odkaz na zošit a Miške upozornenie.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
$meno  = trim(mb_substr(strip_tags($_POST['meno'] ?? ''), 0, 80));
$email = trim(mb_substr($_POST['email'] ?? '', 0, 120));
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || preg_match('/[\r\n]/', $email)) { http_response_code(400); exit; }

$subj = function ($s) { return '=?UTF-8?B?' . base64_encode($s) . '?='; };
$cas = date('d. m. Y H:i');
$pdf = 'https://codeshiftmethod.com/zosit/vezmi-si-kontrolu-spat.pdf';
$oslovenie = $meno !== '' ? 'Ahoj ' . htmlspecialchars($meno) . ',' : 'Ahoj,';

// 1) E-mail pre ženu so zošitom
$html = '<!doctype html><html lang="sk"><body style="margin:0;background:#f6f1e7">'
 . '<table width="100%" cellpadding="0" cellspacing="0" style="background:#f6f1e7"><tr><td align="center" style="padding:24px 12px">'
 . '<table width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:#fff;border-radius:10px">'
 . '<tr><td style="background:#344936;padding:22px 30px;font-family:Georgia,serif;color:#e8cf9f;font-size:15px;letter-spacing:3px;text-transform:uppercase;font-weight:bold">Code Shift Method</td></tr>'
 . '<tr><td style="padding:30px;font-family:Georgia,serif;font-size:17px;line-height:1.65;color:#2b2b28">'
 . '<p>' . $oslovenie . '</p><p>som rada, že si tu. Tvoj zošit <b>Vezmi si kontrolu späť</b> si stiahneš tu:</p>'
 . '<p style="margin:24px 0"><a href="' . $pdf . '" style="display:inline-block;background:#344936;color:#fff;padding:14px 26px;border-radius:6px;text-decoration:none">Stiahnuť zošit (PDF)</a></p>'
 . '<p>Nemusíš ho prečítať naraz. Začni stranou 4, testom „Spoznávaš sa?“. A ak máš dnes ťažký večer, otvor rovno stranu 8, SOS kartu.</p>'
 . '<p>Do zošita môžeš písať priamo v mobile aj v počítači, stačí ťuknúť na riadok.</p>'
 . '<p style="margin-top:26px">Miška<br><span style="color:#8a8070;font-size:14px">Miška Duit · <a href="https://codeshiftmethod.com" style="color:#344936">codeshiftmethod.com</a></span></p>'
 . '</td></tr><tr><td style="padding:20px 30px;font-family:Arial,sans-serif;font-size:12px;color:#8a8070;border-top:1px solid #eee5d6">'
 . 'Ak sa necítiš v bezpečí, zavolaj na Národnú linku pre ženy zažívajúce násilie 0800 212 212 (bezplatne, nonstop). V ohrození volaj 112.<br><br>'
 . 'Tento e-mail ti prišiel, lebo si si na codeshiftmethod.com vypýtala zošit. Ak ďalšie e-maily nechceš, stačí odpísať „odhlásiť“.<br>Sentio Mio s. r. o., Karpatské námestie 7770/10A, 831 06 Bratislava'
 . '</td></tr></table></td></tr></table></body></html>';
$h1 = "From: Miška Duit <info@codeshiftmethod.com>\r\nReply-To: info@codeshiftmethod.com\r\nMIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n";
$ok = @mail($email, $subj('Tvoj zošit je tu'), $html, $h1, '-finfo@codeshiftmethod.com');

// 2) Upozornenie pre Mišku
$text = "Niekto si práve vypýtal zošit Vezmi si kontrolu späť.\n\nMeno: " . ($meno ?: '(nevyplnila)') . "\nE-mail: $email\nČas: $cas\nZošit jej bol poslaný e-mailom: " . ($ok ? 'áno' : 'NIE, pošli jej ho ručne') . "\n";
$h2 = "From: Code Shift Method <info@codeshiftmethod.com>\r\nReply-To: $email\r\nContent-Type: text/plain; charset=UTF-8\r\n";
@mail('miskaduit3330@gmail.com', $subj('Nový zošit: ' . ($meno ?: $email)), $text, $h2, '-finfo@codeshiftmethod.com');

@file_put_contents(__DIR__ . '/../../zosit-ziadosti.csv', "$cas;$meno;$email;" . ($ok ? 'poslane' : 'chyba') . "\n", FILE_APPEND | LOCK_EX);
echo 'ok';
