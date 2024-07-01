<?php  header("Content-type: text/html");?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pizza Service</title>
</head>
<body>
<?php
$content = <<<EOT
    <section>
        <h1>Übersicht</h1>
        <button tabindex="1" accesskey="o" onclick="window.location.href='bestellung.php'">Bestellung</button>
        <button tabindex="2" accesskey="k" onclick="window.location.href='kunde.php'">Kunde</button>
        <button tabindex="3" accesskey="b" onclick="window.location.href='baecker.php'">Bäcker</button>
        <button tabindex="4" accesskey="f" onclick="window.location.href='fahrer.php'">Fahrer</button>            
    </section>
EOT;

echo $content;

?>

</body>
</html>