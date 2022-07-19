# Installation

This document covers the installation of CONNECT, as well as the initial import of data. Before you begin, make sure you
have installed the Symfony CLI and are using an Apache webserver.

## Prerequisites

CONNECT requires PHP 8.1+, as well as the following extensions:

- ctype
- gd
- iconv
- intl
- ldap
- pdo
- posix
- zip

CONNECT also requires `npm`.

## Symfony setup

In MySQL, create a new database and a user with full permissions on that database.

Create the `.env.local` config file:

```shell
cp .env .env.local
```

Edit this file to set server-specific variables. Make sure to set the `DATABASE_URL` string appropriately for the
database you just created. Set `LDAP_HOST`, `LDAP_PORT`, and `LDAP_DN` for your LDAP authentication server. If CONNECT
will be deployed under a subdirectory on the web server, set `WEBPACK_PREFIX` to the subdirectory path. Finally, if this
is a production server, set `APP_ENV=prod`

Next, make sure permissions are set up properly for file uploads and the image cache:

```shell
mkdir -p public/media
chmod 777 public/media
mkdir -p public/uploads
chmod 777 public/uploads
```

Now, install the required composer packages:

```shell
symfony composer install
```

Run the following command to check whether all Symfony installation requirements
are met:

```shell
symfony check:requirements
```

If any requirements are not met, install them before proceeding.

Now, initialize the database:

```shell
symfony console doctrine:migrations:migrate
```

Finally, install and build the frontend dependencies:

```shell
npm install
npm run build
```

You may also choose to build these dependencies on a staging server and copy the `/public/build` folder to the
production server.

## Data import

### Admin account creation

Use the following console command to create an initial admin user.

```shell
symfony console app:first-run <USERNAME>
```

You will use this IGB username and password to log in to CONNECT.

### Intial settings

Colleges, Departments, Keys, Member Categories, Rooms, and Themes are set manually. You can either import these from an
existing installation of CONNECT or enter them manually in EasyAdmin.

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

### Faculty/Affiliate master list import

Use the following console command to create or update all faculty and affiliates from the master list spreadsheet.
Replace SPREADSHEET_PATH with the path to the master list spreadsheet, and HIGHEST_ROW with the number of the last row
in the spreadsheet.

```shell
symfony console app:import-faculty-spreadsheet <SPREADSHEET_PATH> <HIGHEST_ROW>
```

### Key assignment import

Use the following console command to import the list of key assignments:

```shell
symfony console app:import-assigned-keys 
```