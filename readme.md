# NuoNuo

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Build Status][ico-travis]][link-travis]
[![StyleCI][ico-styleci]][link-styleci]

This is where your description should go. Take a look at [contributing.md](contributing.md) to see a to do list.

## Installation

Via Composer

``` bash
composer require fatryst/nuonuo
```
```bash
# publish config file
php artisan vendor:publish --provider="Fatryst\NuoNuo\NuoNuoServiceProvider"
```
## Usage
```angular2html
# use facade
NuoNuo::invoiceOrder()

# or
(new NuoNuo())->invoiceOrder()
```
## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.


## Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

## Security

If you discover any security related issues, please email fatryst@gmail.com instead of using the issue tracker.

## Credits

- [fa][link-author]
- [All Contributors][link-contributors]

## License

MIT. Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/fatryst/nuonuo.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/fatryst/nuonuo.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/fatryst/nuonuo/master.svg?style=flat-square
[ico-styleci]: https://styleci.io/repos/12345678/shield

[link-packagist]: https://packagist.org/packages/fatryst/nuonuo
[link-downloads]: https://packagist.org/packages/fatryst/nuonuo
[link-travis]: https://travis-ci.org/fatryst/nuonuo
[link-styleci]: https://styleci.io/repos/12345678
[link-author]: https://github.com/fatryst
[link-contributors]: ../../contributors
