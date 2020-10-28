
# POLLY API

A simple script that generates a set of voices taken from text files in batchs.

### Installation

- Clone this repository to your local machine.
```sh
git clone https://github.com/agarzon/polly-api.git
```
- Install the dependencies with composer
```sh
composer install
```
- Make a local copy of the `.env` file:
```sh
$ cp .env.example .env
```

- Update the `.env` file with your own AWS credentials


### Usage

```sh
php index.php
```
This with run the script and automatically will create all the mp3 files inside the `/output` folder organized by voices

### Todos

 - Write  Tests
 - Add Docker

License
----

MIT
