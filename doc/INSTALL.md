# Installation

This document covers the installation of Connect, as well as the initial import of data. Before you begin, make sure you
have installed the Symfony CLI and are using an Apache webserver.

## Prerequisites

Connect requires PHP 8.1+, as well as the following extensions:

- ctype
- gd
- iconv
- intl
- ldap
- pdo
- posix
- yaml
- zip

Connect also requires `npm` and `yarn`.

## Deployer

Connect comes with an optional [Deployer.php](https://deployer.org/) recipe for atomic deployment.

### First-run setup

Before using Deployer on a new Apache server, you will need to
install [mod_realdoc](https://github.com/etsy/mod_realdoc) so the symlinked DocumentRoot functions correctly in Apache.

For first-run installation, create a host file called `deploy-hosts.yml`. An example is provided below:

```yaml
hosts:
  connect:
    hostname: example.org
    remote_user: username
    deploy_path: /path/to/deployment
    labels:
      stage: production
    symfony_env: prod
```

Replace the `hostname`, `remote_user`, and `deploy_path` as necessary.

In MySQL, create a new database and a user with full permissions on that database.

Run `dep deploy:setup` to create the basic file structure, then create the `.env.local` config file in the `shared`
directory.

Edit this file to set server-specific variables. Make sure to set the `DATABASE_URL` string appropriately for the
database you just created. Set `LDAP_HOST`, `LDAP_PORT`, and `LDAP_DN` for your LDAP authentication server. If Connect
will be deployed under a subdirectory on the web server, set `WEBPACK_PREFIX` to the subdirectory path. Finally, if this
is a production server, set `APP_ENV=prod`.

With the config finished, you can run `dep deploy` to deploy the latest release. Continue to
the [Data import](#data-import) section below to complete first-run setup.

### Updating

To update with deployer, simply run `dep deploy` to deploy the latest commit from the Github repo.

In the case that a release needs to be rolled back, run `dep rollback`. Optionally, you can
specify `-o rollback_candidate=<number>` to roll back to a specific release.

## Manual install

### Symfony setup

In MySQL, create a new database and a user with full permissions on that database.

Create the `.env.local` config file:

```shell
cp .env .env.local
```

Edit this file to set server-specific variables. Make sure to set the `DATABASE_URL` string appropriately for the
database you just created. Set `LDAP_HOST`, `LDAP_PORT`, and `LDAP_DN` for your LDAP authentication server. If Connect
will be deployed under a subdirectory on the web server, set `WEBPACK_PREFIX` to the subdirectory path. Finally, if this
is a production server, set `APP_ENV=prod`.

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

Run the following command to check whether all Symfony installation requirements are met:

```shell
symfony check:requirements
```

If any requirements are not met, install them before proceeding.

### Frontend dependencies

The frontend (javascript/CSS) dependencies are managed and built with yarn. To install and build the frontend
dependencies:

```shell
yarn install
yarn run build
```

You may also choose to build these dependencies on a staging server and copy the `/public/build` folder to the
production server.

## Data import

### Quick install

A single installation command is provided to run through the remaining installation steps. These steps will be outlined
below if you want or need to run them separately.

A configuration file is required to run the installation command. A sample configuration is provided
in `install-config.yml`

```yaml
# Defines an admin user that will be created
initialize-admin-user:
  username: username
  first-name: User
  last-name: Name

# Defines an SQL file with initial settings that will be imported
import-sql: filename.sql

# Defines connection parameters for the IGB People Database
import-people:
  host: 127.0.0.1
  port: 3306
  database: people
  username: people
  password: ChangeMe!

# Defines the path to the faculty master list spreadsheet, and its length
import-faculty:
  file: Faculty.xlsx
  rows: 444
```

This file defines the parameters that are passed to each install step. Copy this file to `install-config.local.yml` and
update the fields to suit your installation. Finally, run the following command and follow the prompts to complete
installation:

```shell
symfony console app:install install-config.local.yml
```

When the command finishes, installation is complete. You do **not** need to continue to the next section.

### Manual install

Initialize the database:

```shell
symfony console doctrine:migrations:migrate
```

#### Admin account creation

Use the following console command to create an initial admin user.

```shell
symfony console app:initialize-admin-user <USERNAME> <FIRSTNAME> <LASTNAME>
```

You will use this IGB username and password to log in to Connect.

#### Intial settings

Units, Keys, Member Categories, Rooms, and Themes are set manually. You can either import these from an existing
installation of Connect or enter them manually in EasyAdmin.

#### People database import

First, copy the `users` directory from the people database images folder into a folder called `people_images`.

Use the following console command to automatically import all pertinent data from the People database and import
portraits, filling in the MySQL password where indicated. For a full list of options for this command, use the `-h`
flag.

```shell
symfony console app:import-people -p <MYSQL_PASSWORD>
```

Note that this command will ignore any IGB members whose details cannot be determined from the People database, such as
those members who have left the IGB and are marked as "alumnus."

#### Faculty/Affiliate master list import

Use the following console command to create or update all faculty and affiliates from the master list spreadsheet.
Replace SPREADSHEET_PATH with the path to the master list spreadsheet, and HIGHEST_ROW with the number of the last row
in the spreadsheet.

```shell
symfony console app:import-faculty-spreadsheet <SPREADSHEET_PATH> <HIGHEST_ROW>
```

#### Key assignment import

Use the following console command to import the list of key assignments:

```shell
symfony console app:import-assigned-keys <SPREADSHEET_PATH> <HIGHEST_ROW>
```