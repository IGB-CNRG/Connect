# Installation

This document covers the installation of CONNECT, as well as the initial import of data. Before you begin, make sure you
have installed the Symfony CLI and are using an Apache webserver.

## Prerequisites

CONNECT requires PHP 8.1+, as well as the following extensions:

- ctype
- gd
- iconv
- ldap
- pdo

CONNECT also requires npm.

Once these prerequisites are installed, run the following command to check whether all Symfony installation requirements
are met:

```shell
symfony check:requirements
```

If any requirements are not met, install them before proceeding.

## Symfony setup

Create the `.env.local` config file:

```shell
cp .env .env.local
```

Edit this file to set server-specific variables. Make sure to set the `DATABASE_URL` string appropriately for the server
you are on. If this is a production server, set `APP_ENV=prod`

Next, install the composer packages:

```shell
symfony composer install
```

Now, initialize the database:

```shell
symfony console doctrine:database:create
symfony console doctrine:migrations:migrate
```

Finally, install and build the frontend dependencies:

```shell
npm install
npm run build
```

## Data import

### Intial settings

TBD How to import departments, rooms, themes, etc.

### People database import

First, copy the `users` directory from the people database images folder into a folder called `people_images`.

Use the following console command to automatically import all pertinent data from the People database and import
portraits, filling in the MySQL password where indicated. For a full list of options for this command, use the `-h`
flag.

```shell
symfony console app:import-people -p <MYSQL_PASSWORD>
```

Note that this command will ignore any IGB members whose details cannot be determined from the People database, such as
those members who have left the IGB and are marked as "alumnus."