# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.1.2] - 2024-02-07
* Upgrade to `crwlr/crawler` v1.5.3 and remove the separate `HeadlessBrowserLoader` and `HeadlessBrowserCrawler`. The steps shall simply use the normal `HttpLoader` and automatically switch to use the headless browser for loading and switch back afterwards if the loader was configured to use the HTTP client.

## [0.1.1] - 2024-02-06
### Fixed
* Set required `crwlr/crawler` version to `^1.5`.

## [0.1.0] - 2024-02-06
### Added
* Initial version containing a `HeadlessBrowserLoader`, a `HeadlessBrowserCrawler` (like `HttpCrawler`) and two steps: `Screenshot` and `GetColors`.
