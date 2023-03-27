## v3.1.0 - 2023-27-03

### added
- Laravel 10 & PHP 8.2 support

## v3.0.0 - 2022-24-02

### removed
- php 7 and php < 8.1 no longer supported minimum php requirement is 8.1
### added
- Laravel 9 support

## v2.1.0 - 2021-12-06

### fix
- service provider rename fixed

## v2.0.1 - 2021-12-06

### changed
- minor patch release has no impact on V2.0.0 users
- requirements & test workflow updated

## v2.0.0 - 2021-12-05

### BREAKING CHANGES INTRODUCTED
- `HasActions` trait renammed to `HasScheduledAction`
- no more recurring support
- column names changed
    - `act_on` -> `act_date`
    - `act_at` -> `act_time`
    - `recurring` removed
- config file renammed to `scheduled-action.php`
- action status type getter local scopes to attribute accessor - [refer c26444a](https://github.com/devsrv/laravel-scheduled-model-action/commit/c26444a86521efb742a2029ec7cd2790041b8b53)
- fluent create method `forModel` renammed to `for` - [refer](https://github.com/devsrv/laravel-scheduled-model-action/commit/3862bd057dea76e43fa22cb7258a2c1db0b72885#diff-6594cc5a0ca7713d827cf28b57232041050abce29f57255122807f5224855504R20)

- fluent create method signatures changed - for chaining needs to start with `ModelAction::for($model)->...`
## v1.1.0 - 2021-07-08

### added
- delete any scheduled action when main model is deleted

## v1.0.0 - 2021-07-01

- initial release
