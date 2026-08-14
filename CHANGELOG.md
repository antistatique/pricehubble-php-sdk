# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]
### Removed
- chore(deps): drop unused phpmd/phpmd dev dependency

### Changed
- fix(composer): bound the php constraint (>=8.4 => ^8.4)

### Fixed
- fix(ci): run workflows on pull_request so fork contributions get CI
- fix(dependabot): remove config that silently disabled all version updates
- fix(php85): remove deprecated calls and fail the suite on new ones
- fix(curl): type cURL handles as CurlHandle and check curl_init failure

## [1.2.0] - 2026-05-01
### Security
- update(phpunit): phpunit/phpunit (12.5.14 => 12.5.24)
- update(linter): vimeo/psalm (6.15.1 => 6.16.1)
- update(linter): friendsofphp/php-cs-fixer (v3.94.2 => v3.95.1)
- test(update): upgrade phpunit/phpunit (12.5.24 => 13.1.8)
- test(update): upgrade symfony/dotenv (v7.4.9 => v8.0.9)

### Removed
- build(php): removed support PHP 8.3

## [1.1.0] - 2026-02-23
### Security
- update PHP versions to run on PHP 8.5
- update symfony/dotenv (v5.4.48 => v7.4.0)
- upgrade vimeo/psalm (4.30.0 = => 6.15.1)
- upgrade phpunit/php-code-coverage (9.2.32 => 12.5.3)
- upgrade phpunit/phpunit (9.6.34 => 12.5.14)

### Removed
- removed support PHP 8.0
- removed support PHP 8.1
- removed support PHP 8.2
- remove phpcpd as abandoned

## [1.0.4] - 2025-05-02
### Added
- add tests on PHP 8.4

## [1.0.3] - 2024-06-24
### Added
- run tests over multiple PHP version
- add tests on PHP 8.3

### Security
- update friendsofphp/php-cs-fixer (v3.4.0 => v3.21.1)

### Removed
- drop support for PHP 7.4 and below

## [1.0.2] - 2022-09-06
### Added
- cover the scenario of inconsistency on Pricehubble API with nested message

### Changed
- use an Authorization HTTP header to transmit access_token on requests instead of passing the access_token in query parameter

## [1.0.1] - 2022-06-10
### Security
- update library guzzlehttp/guzzle 7.4.2 => 7.4.3 (CVE-2022-29248)
- update library guzzlehttp/guzzle 7.4.3 => 7.4.4 (CVE-2022-31042 & CVE-2022-31043)

## [1.0.0] - 2022-05-11
### Added
- Under heavy development

### Security
- update library guzzlehttp/psr7 1.6.1 => 1.8.5 (CVE-2022-24775)
- upgrade symfony/dotenv 4.3 > 5.4
- upgrade all dev dependencies

### Changed
- remove sensiolabs/security-checker in favor of Github Actions security-checker

[Unreleased]: https://github.com/antistatique/pricehubble-php-sdk/compare/1.2.0...HEAD
[1.2.0]: https://github.com/antistatique/pricehubble-php-sdk/compare/1.1.0...1.2.0
[1.1.0]: https://github.com/antistatique/pricehubble-php-sdk/compare/1.0.4...1.1.0
[1.0.4]: https://github.com/antistatique/pricehubble-php-sdk/compare/1.0.3...1.0.4
[1.0.3]: https://github.com/antistatique/pricehubble-php-sdk/compare/1.0.2...1.0.3
[1.0.2]: https://github.com/antistatique/pricehubble-php-sdk/compare/1.0.1...1.0.2
[1.0.1]: https://github.com/antistatique/pricehubble-php-sdk/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/antistatique/pricehubble-php-sdk/releases/tag/v1.0.0
