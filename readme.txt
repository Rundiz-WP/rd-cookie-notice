=== ZP Cookie Notice ===

Description:       ZP Cookie Notice allows you to easily inform users that your site uses cookies and helps you comply with the EU GDPR cookie law and CCPA regulations.
Version:           0.1.1
Text Domain:       rd-cookie-notice
Domain Path:       /languages
Requires PHP:      7.0
Author:            ZigPress
Author URI:        https://www.zigpress.com/
Plugin URI:        https://www.zigpress.com/plugins/zp-cookie-notice/
Download link:     https://plugins.zigpress.co/releases/zp-cookie-notice-0.1.1.zip
Donate link:       https://www.zigpress.com/donations/

ZP Cookie Notice allows you to easily inform users that your site uses cookies and helps you comply with the EU GDPR cookie law and CCPA regulations.

== Description ==

[ZP Cookie Notice](https://www.zigpress.com/plugins/zp-cookie-notice/) allows you to easily inform users that your site uses cookies and helps you comply with the EU GDPR cookie law and CCPA regulations.

This is a fork of Cookie Notice 1.3.2 by dFactory and will be developed and supported in the following ways:

* Will always be free (a commercial add-on may be considered in the future but this will NOT affect the free version)
* Will stick to its core purpose (irrelevant Coronavirus-related features have been stripped out)
* Will no longer show unnecessary admin notices
* Will be updateable via your site's plugins page even though it will not be added to the WordPress plugins repository
* Will be developed and supported for both WordPress and ClassicPress
* Will be refactored using object-oriented techniques and properly namespaced
* Will eventually carry a small, unobtrusive marketing message on the admin page (see [other free ZigPress plugins](https://www.zigpress.com/plugins/) for how this looks)
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

`if ( function_exists('cn_cookies_accepted') && cn_cookies_accepted() ) {
	// Your third-party non functional code here
}`

== Installation ==

Download the latest release.

Go to the admin plugins page, click the 'Add New' button, then click the 'Upload Plugin' button and upload the zip file.

Future updates will appear on the admin plugins page for one-click updating, provided that the plugin is active.

== Changelog ==

= 0.1.1 =

- Removed all Coronavirus-related functionality
- Removed unnecessary admin notices
- Removed minified scripts and styles (plugin will use non-minified scripts and styles until refactoring is complete)
- Renamed main class
- Removed uncalled methods

= 0.1.0 =

- Initial release, forked from Cookie Notice 1.3.2 by dFactory (https://wordpress.org/plugins/cookie-notice/)
