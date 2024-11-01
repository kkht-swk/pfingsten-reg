# SWK Pfingsten Registrierung 

Diese Web-Anwendung erlaubt es, die Anmeldung von Teams und SWK-Spieler:innen komplett ins Internet zu verlagern.

Es werden insgesamt drei Typen von Routen angelegt:

  - `/`  - (Statische) Homepage
  - `/player/*` - Spielerregistrierung
  - `/team/*` - Teamregistrierung

Das ganze ist implementiert mit dem Symfony-Framework, aktuell in der LTS Version 6.4 und PHP 8.3

## Datenbank-Server

Das Projekt benötigt zwangsweise eine Datenbank, in der die entsprechenden Daten abgelegt werden. Auf Produktionssystemen kann das eine beliebige, zur Verfügung stehende Datenbank sein. 

## Email-Server

Die Anwendung versendet Emails. Dazu muss ein Email-Server erreichbar sein, i.d.R. via SMTP. Siehe dazu die [Doku von Symfony](https://symfony.com/doc/current/mailer.html).

## Web-Server

Der Web-Server, der das Projekt hostet, muss zwangsweise PHP unterstützen. Dazu müssen diverse PHP module zur Verfügung stehen, typischerweise `ctype`, `gd`, `intl`, `mbstring`, `mysql` (oder ein anderes DB Modul), `pcre` etc. 

## Konfiguration

In der Datei `.env` bzw. `.env.local` oder `.env.<environment>.local` (siehe [Symfony Doku](https://symfony.com/doc/current/configuration.html#configuration-based-on-environment-variables)) wird die Konfiguration hinterlegt, die durch echte Umgebungsvariablen gleichen Namens überschrieben wird.

Relevant sind vor allem die Angabe der oben erwähnten Datenbank- und Email-Server.
 - `DATABASE_URL`: Datenbank-Server 
 - `MAILER_DSN`: Email-Server


## Erstmalige Nutzung

Wenn das System das erste mal auf einem Rechner installiert wird, ist die Datenbank noch leer und muss initialisiert werden. Das geschieht in zwei Schritten: a) Anlage der DB, b) Anlage der Tabellen etc.

Wenn die DB in einem Docker Container läuft, der via `docker compose` bereitgestellt wurde (siehe oben), kann der erste Schritt entfallen.

```
bin/console doctrine:database:create    # DB anlegen, entfällt bei Docker
bin/console doctrine:migrations:migrate # Tabellen anlegen
```

