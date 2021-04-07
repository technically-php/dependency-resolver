# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.6.0] - 2021-04-07
### Changed
- Move reflection-related logic into a standalone package (`technically/callable-reflection`).

### Removed
- Removed `Argument` and `Type` internal classes.

## [0.5.0] - 2021-03-31
### Changed
- Rename `DependencyResolver::resolve()` to `DependencyResolver::construct()`.
- Improve exceptions handling.

### Added
- Add `DependencyResolver::resolve()` to prefer already defined container entries.

### Fixed
- Fix PHP7.1 incompatibility.

## [0.4.0] - 2021-03-24
### Added
- Implement `DependencyResolver::call()` to call an arbitrary callable with arguments auto-wiring.

## [0.3.0] - 2021-03-24
### Fixed
- Fix `self` and `parent` type-hints resolution.

## [0.2.0] - 2021-03-03
### Changed
- Make `container` constructor argument optional. 

## [0.1.0] - 2021-03-03
### Added
- Initial implementation.
