# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]
### Added
* Support for `crwlr/crawler` v2.0.

## [1.4.0] - 2024-07-08
### Added
* `Screenshot::take()` in addition to `Screenshot::loadAndTake()`, allowing to take a screenshot of an already opened page, in a separate step. This way you can even add this step after an `Http::crawl()` step and get screenshots of all the crawled pages.

### Fixed
* Change calls to `HttpLoader::browserHelper()` to `HttpLoader::browser()` and require `crwlr/crawler` with constraint `^1.9.3` to make sure the method exists.

## [1.3.0] - 2024-06-18
### Added
* Merge things from the `crwlr/crwl-ext-browser` package to this one, because they are too tightly coupled. The other package will be abandoned.
* Add `timeout` config param to `ScreenshotBuilder`, therefore also require `crwlr/crawler` v1.9.0 (or greater), with the new functionality to configure timeouts for the headless browser.

### Fixed
* Prepare for `crwlr/crawler` v2.0.

## [1.2.1] - 2024-03-04
### Fixed
* Remove input validation in screenshot step, so it automatically uses the validation method of the `HttpBase` step, so it also allows to use the `useInputKeyAs...` methods.

## [1.2.0] - 2024-02-26
### Added
* Option to wait a certain amount of time after loading a page, before taking the screenshot (`Screenshot::waitAfterPageLoaded()`).

## [1.1.0] - 2024-02-22
### Added
* Get all colors, not only the ones making up more than 0.5 percent of the image. But also add a method `onlyAbovePercentageOfImage()` to the `GetColors` step, to manually set a custom threshold.

### Fixed
* Improve memory usage of getting colors from an image.

## [1.0.0] - 2024-02-17
### Changed
* Change the output of the `Screenshot` step, from an array `['response' => RespondedRequest, 'screenshotPath' => string]` to a `RespondedRequestWithScreenshot` object, that has a `screenshotPath` property. The problem with the previous solution was: when using the response cache, the step failed, because it gets a cached response from the loader that was not actually loaded in the headless browser. When the step afterwards tries to take a screenshot from the page that is still open in the browser, it just fails because there is no open page. Now, with the new `RespondedRequestWithScreenshot` object, the `screenshotPath` is also saved in the cached response.

## [0.1.2] - 2024-02-07
### Fixed
* Upgrade to `crwlr/crawler` v1.5.3 and remove the separate `HeadlessBrowserLoader` and `HeadlessBrowserCrawler`. The steps shall simply use the normal `HttpLoader` and automatically switch to use the headless browser for loading and switch back afterwards if the loader was configured to use the HTTP client.

## [0.1.1] - 2024-02-06
### Fixed
* Set required `crwlr/crawler` version to `^1.5`.

## [0.1.0] - 2024-02-06
### Added
* Initial version containing a `HeadlessBrowserLoader`, a `HeadlessBrowserCrawler` (like `HttpCrawler`) and two steps: `Screenshot` and `GetColors`.
