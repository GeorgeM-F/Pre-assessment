Piattaforma: nginx

Database:
- database.db

Pagine web:
- index.php          (pagina di ingresso)
- signup.php         (registrazione)
- login.php          (login)
- area.php           (area riservata (template))
- questionnaireA.php (questionario con le 70 domande (versione vecchia, a form unificato))
- questionnaireB.php (questionario con le 70 domande (versione nuova, a form separati))
- results.php        (risultati del questionario (template))

Altro:
- .git                           (cartella di git)
- style.css                      (aspetto estetico)
- report_template_simplified.ods (template per la generazione del report da scaricare)

I seguenti elementi:
- vendor
- composer.json
- composer.lock
sono stati generati dall'installazione di Composer e PhpSpreadsheet.

Linguaggi utilizzati:
PHP (per il lato backend (logica))
SQL (per il lato backend (interazione col database))
HTML (per il lato frontend (contenuti))
CSS (per il lato frontend (aspetto))
JavaScript ((tramite connessione a una libreria esterna) per il lato frontend (grafici))
