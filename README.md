[![Build Status](https://travis-ci.org/ManifestWebDesign/DABL.svg?branch=master)](https://travis-ci.org/ManifestWebDesign/DABL)

# DABL
A lightweight PHP MVC framework.  It consists of 4 primary components:

* [DABL ORM](https://github.com/ManifestWebDesign/dabl-orm) - Reads your database schema and creates active record classes for your tables
* [DABL Controller](https://github.com/ManifestWebDesign/dabl-controller) - Maps request routes to controller classes
* [DABL View](https://github.com/ManifestWebDesign/dabl-view) - Simple PHP view renderer
* [DABL Generator](https://github.com/ManifestWebDesign/dabl-generator) - Generator for your models, views and controllers

## Creating a New Project
### Checkout Code
 ```bash
git clone https://github.com/ManifestWebDesign/dabl.git your-project
cd your-project
composer install
```

### Configure the Database Connection
Edit `config/connections.ini` with your database credentials

### Configuring the Web Server
Point your host to the `/{your-project}/public/` directory

## Generate Models, Views and Controllers
Open `http://{your-project}/generator/` in a web browser

## Running Tests
Run the following from the project root
```bash
phpunit
```

## Project File Structure
**/config/** - Constains configuration files

**/controllers/** - Contains classes for handling http requests

**/logs/** - By default, the application will log errors to `error_log` in this directory

**/models/** - Contains generated classes for interacting with database tables

**/public/** - Contains static assets (JavaScript, CSS), and `server.php`, which is the entry point for all requests

**/tests/** - Contains test sources

**/views/** - Contains PHP/HTML views

## License
DABL is released under the MIT license. See the file LICENSE for the full text.