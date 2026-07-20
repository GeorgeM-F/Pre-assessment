<?php
session_start();     // Inizio nuova sessione
try {     // Connessione al database
  $pdo = new PDO("sqlite:database.db");
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("ERRORE! NON E' STATO POSSIBILE CONNETTERSI AL DATABASE." . $e->getMessage());
}
$messaggio = "";     // Crea parti di HTML da visualizzare successivamente
$link = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = isset($_POST['email']) ? trim($_POST['email']) : '';
  $password = isset($_POST['password']) ? trim($_POST['password']) : '';
  // QUERY SQL PER IL CONFRONTO TRA I DATI INSERITI E QUELLI NEL DATABASE
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM aziende WHERE indirizzo_email = :email AND password = :password");
  if ($stmt->execute([':email' => $email, ':password' => $password])) {     // Definisce le parti di HTML da visualizzare a seconda dell'esito
    if ($stmt->fetchColumn() > 0) {
      $stmt = $pdo->prepare("SELECT * FROM aziende WHERE indirizzo_email = :email AND password = :password");
      $stmt->execute([':email' => $email, ':password' => $password]);
      $dati = $stmt->fetch(PDO::FETCH_NUM);
      $_SESSION["tuonome"]=$dati[1];     // Riempimento sessione
      $_SESSION["tuaiva"]=$dati[2];
      $_SESSION["tuocodice"]=$dati[3];
      $_SESSION["tuosettore"]=$dati[4];
      $_SESSION["tuacreazione"]=$dati[5];
      $_SESSION["tuasede"]=$dati[6];
      $_SESSION["tuoateco"]=$dati[7];
      $_SESSION["tuotipo"]=$dati[8];
      $_SESSION["tuaemail"]=$dati[9];
      $messaggio = "<p style='color: green;'>Accesso effettuato! Ora puoi visualizzare la tua area personale:</p>
      <p style='color: green;'>(sei loggato come: <strong>".$_SESSION["tuonome"]."</strong>.)</p>";
      $link = "<a class='bot' href='area.php'>ENTRA</a>";
    } else {
      $messaggio = "<p style='color: red;'>ACCESSO NEGATO: non esistono aziende con tali credenziali. Registrati o riprova.</p>";
    }
  } else {
    $messaggio = "<p style='color: red;'>ERRORE: non è stato possibile effettuare l'accesso.</p>";
  }
}
?>



<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<title>Pre-assessment - inserimento dati</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="sez">
  <h1>DATI DI ACCESSO</h1>
  <p>Inserisci le tue credenziali:</p>

  <form action="" method="POST">
    <div>
      <label>Indirizzo e-mail:</label>
      <input type="text" name="email" required>
    </div>
    <div>
      <label>Password:</label>
      <input type="text" name="password" required>
    </div>
    <button type="submit">LOGIN</button>
    <?php echo $messaggio; ?>
    <?php echo $link; ?>
  </form>

  <a class="bot" href="index.php">TORNA INDIETRO</a>
</div>

</body>
</html>
