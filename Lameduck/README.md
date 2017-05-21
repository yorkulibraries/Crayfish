# ![image](https://cloud.githubusercontent.com/assets/2371345/24111014/dbc65c56-0d73-11e7-91f0-06af315f78a8.png) Lameduck
[![Contribution Guidelines][2]](./CONTRIBUTING.md)
[![LICENSE][3]](./LICENSE)

## Introduction

[Lame][9] as a microservice.

## Installation

- Install `lame`. On Ubuntu, this can be done with `sudo apt-get install lame`.
- Clone this repository somewhere in your web root.
- Install `composer`. [Install instructions here.][4]
- `$ cd /path/to/Lameduck` and run `$ composer install`
- Then either
 - For production, configure your web server appropriately (e.g. add a VirtualHost for Lameduck in Apache) some documentation (here)[http://silex.sensiolabs.org/doc/2.0/web_servers.html].
 - For development, run the PHP built-in webserver `$ php -S localhost:8888 -t src` from Lameduck root.

### Apache2

To use Lameduck with Apache you need to configure your Virtualhost with a few options:
- Redirect all requests to the Lameduck index.php file
- Make sure Lameduck has access to Authorization headers

Here is an example configuration for Apache 2.4:
```apache
 Alias "/lameduck" "/path/to/Crayfish/Lameduck/src"
 <Directory "/path/to/Crayfish/Lameduck/src">
  FallbackResource /lameduck/index.php
  Require all granted
  DirectoryIndex index.php
  SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1
 </Directory>
```

This will put the Lameduck at the /lameduck endpoint on the webserver.

## Configuration

If `lame` is not in your path, then you can configure Lameduck to use a specific executable by editing `executable` entry in [config.yaml](./cfg/config.example.yaml).

You also will need to set the `fedora base url` entry to point to your Fedora installation.

In order to work on larger audio files, be sure `post_max_size` is sufficiently large and `max_execution_time` is set to 0 in your PHP installation's ini file. You can determine which ini file is getting used by running the command `$ php --ini`.

## Usage

Lameduck only accepts one request, a `GET` containing the path a WAV or FLAC in Fedora.

For example, suppose if you have a WAV or FLAC in Fedora at `http://localhost:8080/fcrepo/rest/foo/bar`. If running the PHP built-in server command described in the Installation section:
```
$ curl -H "Authorization: Bearer islandora" "localhost:8888/foo/bar"
```

This will return an MP3 generated from the WAV or FLAC in Fedora. Additional arguments to `lame` can be provided using the `X-Islandora-Args` header. For example, to change the quality:
```
$ curl -H "Authorization: Bearer islandora" -H "X-Islandora-Args: -q 0" "localhost:8888/foo/bar"
```

## Maintainers

Current maintainers:

* [Nick Ruest](https://github.com/ruebot)

## License

[MIT](https://opensource.org/licenses/MIT)

[2]: http://img.shields.io/badge/CONTRIBUTING-Guidelines-blue.svg
[3]: https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square
[4]: https://getcomposer.org/download/
[9]: http://lame.sourceforge.net/
