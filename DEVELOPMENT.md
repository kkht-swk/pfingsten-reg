# Entwicklungsnotizen

In dieser Datei halte ich wesentliche Teile der Entwicklung fest, eher als Gedankenstütze.

## Symfony-Framework

Das Projekt nutzt Symfony und das entsprechende Symfony MakerBundle. Dazu die wichtigsten Standards (ORM, Twig, Forms, Validation, Asset Mgmt, Logging) sowie einige spezifische Tools (CSRF zur Absicherung von Forms, UUID zur Erzeugung von uuids, Mailer zum Mailversand und International).

```
# initialize new project, cd into dir and install maker bundle for DEV
symfony new p2024 --version=lts
cd p2024
composer require --dev symfony/maker-bundle
# Debugging
composer require symfony/debug-pack
# install basic packs: ORM, Twig, 
composer require symfony/orm-pack symfony/twig-pack 
# Forms and validation
composer require symfony/form symfony/validator
# Asset management
composer require symfony/asset-mapper symfony/asset 
# Logging
composer require symfony/monolog-bundle
# CSRF
composer require symfony/security-csrf
# UUID
composer require symfony/uid
# Mailer
composer require symfony/mailer
# International
composer require symfony/intl
```

### Neuaufsetzen/Aktualisieren der Pakete

Die oben geladenen Pakete werden alle in `/vendor` installiert und NICHT in das git Repository eingecheckt - sondern in der Datei `composer.json` bzw. `composer.lock` dokumentiert. Um sie gesamthaft neu zu installieren, muss man einfach `composer` verwenden:

```
# Install from composer.lock
composer install
```

Möchte man aktualisieren, kommt auch `composer` zum Einsatz:

```
# Update from composer.json and rewrite composer.lock
composer update
```

Siehe https://getcomposer.org/doc/01-basic-usage.md


## Datenbank & Email-Server: Docker

Das Projekt benötigt eine Datenbank sowie einen Email-Server. In der Entwicklung wird beides über vorkonfigurierte Docker-Container bereitgestellt. Es handelt sich dabei um eine *MariaDB* sowie den SMTP Server *Mailpit*, der alle Mails lokal abfängt und über ein lokales Web-Interface (http://localhost:8025/) zugänglich macht - siehe [Doku von mailpit](https://mailpit.axllent.org/). Zusätzlich wird der Datenbankclient *adminer* bereitgestellt, erreichbar vie http://localhost:8080/ 

Die Nutzung via Docker ist in der Datei `compose.yaml` und der Standard-Konfiguration in `.env` vorbereitet.

```
# Start docker
docker compose up
```

Der Docker-Container legt die notwendige Datenbank selbst schon direkt an.

## Web-Server: Symfony

In der Entwicklung kann man zweckmäßigerweise auf Symfony als Web_Server zurückgreifen.

```
# Start development webserver, logging to stdout
symfony server:start
# In a separate terminal, start webbrowser
symfony open:local
```

## Entwicklungszyklus

### Server Start/Stop

Vorzugsweise in separaten Terminals, da man dort direkt die Ausgabe sieht:

```
docker compose up
symfony server:start
```

Am Ende der Arbeiten:

```
symfony server:stop
docker compose stop
```


### Anlegen von Entities etc

Während der Entwicklung hilft das Maker-Bundle bei der Anlage von Entities und Controllern

```
bin/console make:entity
bin/console make:controller
# ... whatever is needed ...
```

### Übernehmen der Anpassungen in die DB

Nach Anpassung der Entities immer die entsprechenden Migration-Dateien erzeugen und sukzessive zur DB hinzufügen.

```
bin/console make:migration    # nur in dev nach Anpassungen entity
bin/console doctrine:migrations:migrate # nach anpassungen
```

### Fehlfunktion?

Es hilft oft, einfach den Cache leeren zu lassen:

```
bin/console cache:clear
```

### Debugging?

TBD

## Deployment in Produktion

Die `.env` Datei auf dem Server entsprechend anpassen (u.a. `APP_ENV=prod` und DB sowie Mail-Konfiguration).

Auf dem Server eine DB anlegen und die entsprechenden Strukturen anlegen:

```
bin/console doctrine:database:create    # DB anlegen, falls noch nicht existent
bin/console doctrine:migrations:migrate # Tabellen anlegen/anpassen
```

Dann die assets für die Produktion komplieren, sowie das richtige Environment in der Datei `.env.local.php` speichern

```
bin/console asset-map:compile
composer dump-env prod
```

Und den Cache leeren - empfiehlt sich nach jeder relevanten Änderung
```
bin/console cache:clear
```

## Offene Punkte

- Erläuterung der Entity-Validierung
- Echtes Admin-Interface anstelle der `/list`-Routen
- Assets und Asset-Kompilierung
- Deployment auf non-Platform.sh server?
