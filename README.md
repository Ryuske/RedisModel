# RedisModel

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Total Downloads][ico-downloads]][link-downloads]

A Model accessor that makes using Redis as a primary data store easy.

## Install

Via Composer

``` bash
$ composer require Ryuske/RedisModel
```

## Usage

``` php
/**
 * Available Methods:
 * get($id, $fields='all')
 *
 * searchBy($data, $fields='all');
 * searchByWildcard($data, $fields='all');
 * 
 * update($id)
 * save()
 *
 * delete($id)
 * delete()
 */

class Account extends Ryuske\Redis\Model
{
    /**
     * These are fields that are searchable.
     * The order of this list matters!
     * Add additional indexes to the bottom
     *
     * @var array
     */
    protected $indexes = [
        'id',
        'email'
    ];

    /**
     * These are additional, non-searchable indexes.
     * The order of this list doesn't matter.
     *
     * @var array
     */
    protected $fields = [
        'name',
        'password'
    ];
}

class MyController
{
    /**
     * @var Account
     */
    protected $account;

    public function __construct(Account $account)
    {
        $this->account = $account;
    }
    
    public function showAccount($id)
    {
        $account = $this->account->get($id);
        
        return view('account.show', [
            'account' => $account
        ]);
    }
}
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Security

If you discover any security related issues, please email kenyon.jh@gmail.com instead of using the issue tracker.

## Credits

- [Ryuske][link-author]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/Ryuske/RedisModel.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/Ryuske/RedisModel/master.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/Ryuske/RedisModel.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/Ryuske/RedisModel
[link-travis]: https://travis-ci.org/Ryuske/RedisModel
[link-downloads]: https://packagist.org/packages/Ryuske/RedisModel
[link-author]: https://github.com/Ryuske
