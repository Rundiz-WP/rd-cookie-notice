# Version history
---

## V0.x
### 0.2.7
2023-01-24

* Fix CSS & JS did not load in admin.
* Update new line to Unix style on all files.
* Add old class name `\RdCookieNotice()` to keep backward compatible but show warning.

### 0.2.5
2022-03-19

* Remove `UserAgent` class because it won't work with W3TC plugin.

### 0.2.4
2022-03-18

* Add [`data-nosnippet="data-nosnippet"`](https://developers.google.com/search/blog/2019/09/more-controls-on-search#using-the-new-data-nosnippet-html-attribute) to prevent search engine collect cookie notice text in `HTML` class.
* Add `UserAgent` class to detect search engine bot and do not display cookie notice text when detected that user is bots.

### 0.2.3
2022-01-12

* Fix errors that `$revoke_opts` property is missing and cause invalid `foreach`.
* Add colors form description.
* Hot fix notice bar appears everywhere in widgets admin & customizer pages. This still need to check when WordPress has been updated.

### 0.2.2
2021-10-09

* Fix errors with Polylang. This also fix to allow string translation work properly.
* Fix prevent display message in admin widget page.

### 0.2.1
2021-08-20

* Split code into multiple files to make it more readable.
* Removed unused variables and functions.
* Rewrite the code to use PHP 7.0+.
* Update translation to use this plugin's text domain only.

### 0.1.0
2021-08-19

* Forked from ZP Cookie Notice which its original is Cookie Notice by dFactory (https://wordpress.org/plugins/cookie-notice/)
* Renamed main class
* Rewrite JS, CSS, and some part of PHP.