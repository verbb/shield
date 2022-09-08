# Changelog

## 2.0.1 - 2022-09-08

### Fixed
- Fix an error when logging submissions due to typo.

## 2.0.0 - 2022-07-26

> {note} The plugin’s package name has changed to `verbb/shield`. Shield will need be updated to 2.0 from a terminal, by running `composer require verbb/shield && composer remove selvinortiz/shield`.

### Changed
- Migration to `verbb/shield`.
- Now requires Craft 3.7+.

## 1.0.4 - 2019-01-18

### Fixed
- Fixed issue where deleting log would simply reload the page
- Fixed issue where deleting all logs did nothing

### Updated
- Updated user agent string that is sent to the Akismet service
- Updated example `config.php` to include Akismet API Key via `.env`

### Removed
- Removed todos related to implementation fo Sprout Forms and Verbb Comments

## 1.0.3 - 2019-01-05

### Fixed
- Fixed issue where forwards slash in the icon svg id broke settings page

## 1.0.2 - 2019-01-05

### Fixed
- Fixed issue where icons where not saved properly

## 1.0.1 - 2019-01-05

### Updated
- Updated branding, docs, and prepped for commercial release

## 1.0.0 - 2018-08-25

### Added
- Added initial release
