# Examples for Pricehubble API SDK for PHP

Note that everything is done from the root of the project and not from the examples folder.

## How to run the examples

1. Install the dependencies with Composer:

```bash
$ composer install
```

2. Copy the `.env.example` file in the examples folder as `.env`

```bash
$ cp examples/.env.example examples/.env
```

3. Fill the `.env` file with correct information

    - `PRICEHUBBLE_USERNAME` is your username for Pricehubble
    - `PRICEHUBBLE_PASS` is the password tied to your username

4. Run the PHP built-in web server. Supply the `-t` option to this directory:

```bash
$ php -s localhost:8000 -t examples/
```

5. Point your browser to the host and port you specified.

## How does the Pricehubble API works

Every request should contain a valid access token. use the `Pricehubble::authenticate` method prior any requests.
All operational requests require an authentication to be present and unexpired.
