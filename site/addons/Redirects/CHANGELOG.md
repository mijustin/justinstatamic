# Changelog

## [1.5.0] - 2021-07-01

### Changed

* Execute redirects if logging fails ([#8](https://github.com/gridonic/statamic-redirects/issues/8)) 

## [1.4.0] - 2020-02-26

### Added

* Support query string retention for target URLs which already contain a query string - thanks @PhilJ

## [1.3.0] - 2019-12-23

### Fixed

* Fix redirects for secondary locales

### Added

* Add possibility to disable logging redirects

## [1.2.0] - 2019-09-26

### Added

* Add pagination for the 404 monitor and auto redirects in Statamic's control panel
* Emit the `AddonSettingsSaved` when saving manual or auto redirects, allowing [Spock](https://statamic.com/marketplace/addons/spock) to detect changed redirects  

## [1.1.1] - 2019-09-10

### Fixed

* Fix another undefined index warning if the `slug` index is not available in a multi language context

## [1.1.0] - 2019-07-03

### Added

* Add support for external domains as redirect targets ([#2](https://github.com/gridonic/statamic-redirects/issues/2))

### Fixed

* Fix undefined index warnings if the `slug` index is not set by Statamic when saving content

## [1.0.0] - 2019-05-17

* Initial release of the addon 🐣

[Unreleased]: https://github.com/gridonic/statamic-redirects/compare/v1.4.0...HEAD
[1.0.0]: https://github.com/gridonic/statamic-redirects/releases/tag/v1.0.0
[1.1.0]: https://github.com/gridonic/statamic-redirects/releases/tag/v1.1.0
[1.1.1]: https://github.com/gridonic/statamic-redirects/releases/tag/v1.1.1
[1.2.0]: https://github.com/gridonic/statamic-redirects/releases/tag/v1.2.0
[1.3.0]: https://github.com/gridonic/statamic-redirects/releases/tag/v1.3.0
[1.4.0]: https://github.com/gridonic/statamic-redirects/releases/tag/v1.4.0
[1.5.0]: https://github.com/gridonic/statamic-redirects/releases/tag/v1.5.0
