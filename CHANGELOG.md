# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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

[Unreleased]: https://github.com/antistatique/pricehubble-php-sdk/compare/1.0.3...HEAD
[1.0.3]: https://github.com/antistatique/pricehubble-php-sdk/compare/1.0.2...1.0.3
[1.0.2]: https://github.com/antistatique/pricehubble-php-sdk/compare/1.0.1...1.0.2
[1.0.1]: https://github.com/antistatique/pricehubble-php-sdk/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/antistatique/pricehubble-php-sdk/releases/tag/v1.0.0
