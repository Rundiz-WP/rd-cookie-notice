=== Rundiz Cookie Notice ===
Contributors: okvee
Tags: cookie consent
Tested up to: 6.8
Stable tag: 0.2.9
License: MIT
License URI: https://opensource.org/licenses/MIT
Requires at least: 4.0
Requires PHP: 7.0

Rundiz Cookie Notice allows you to easily inform users that your site uses cookies and helps you comply with the EU GDPR cookie law and CCPA regulations.

== Description ==

Rd Cookie Notice allows you to easily inform users that your site uses cookies and helps you comply with the EU GDPR cookie law and CCPA regulations.

This is a fork of Cookie Notice 1.3.2 by dFactory and will be developed and supported in the following ways:

* Will always be free
* Will stick to its core purpose (irrelevant Coronavirus-related features have been stripped out)
* Will no longer show unnecessary admin notices
* Will become more customisable (in particular, button colours)

= Features include: =

* Customizable message
* Redirects users to specified page for more information
* Multiple cookie expiry options
* Link to Privacy Policy page
* WordPress Privacy Policy page synchronization
* Option to accept the notice on scroll
* Option to set on scroll offset
* Option to accept the notice with any page click
* Option to refuse the consent
* Option to revoke the consent
* Option to manually block scripts
* Option to reload the page after accepting the notice
* Select the position of the notice container
* Select the position of the privacy policy link
* Animate the container after notice is accepted
* Select from 3 buttons style
* Set the text and bar background colors
* WPML and Polylang compatible
* SEO friendly
* .pot file for translations included

= Usage: =

If you'd like to code a functionality depending on the cookie notice value use the function below:

```
if (function_exists('cn_cookies_accepted') && cn_cookies_accepted()) {
	// Your code to work when cookies consent was accepted.
}
```

== Installation ==

Download the latest release.

Go to the admin plugins page, click the 'Add New' button, then click the 'Upload Plugin' button and upload the zip file.

== Changelog ==

= 0.2.9 =
2025-03-18

* Update load text domain to be inside `init` hook.
