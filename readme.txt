=== Contact Form 7 - reCaptcha v2 ===
Contributors: iqcomputing
Tags: contact-form-7, contact-form-7-recaptcha, recaptcha, spam
Requires at least: 4.9
Tested up to: 5.1
Stable tag: 1.1.5
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds reCaptcha v2 from Contact Form 7 5.0.5 that was dropped on Contact Form 7 5.1

== Description ==

Contact Form 7 v5.1 dropped support for reCaptcha v2 along with the `[recaptcha]` tag December 2018. This plugin brings that functionality back from Contact Form 7 5.0.5 and re-adds the `[recaptcha]` tag.

If this plugin is installed before updating Contact Form 7 from v5.0.5 to v5.1.1 then it will carry over your old API keys. At that point you will just need to head to this plugins settings page to tell the website to use reCaptcha v2.

Once installed and configured it should be the same reCaptcha functionality you are used to in previous versions of Contact Form 7.

== IQComputing ==

* Like us on [Facebook](https://www.facebook.com/iqcomputing/ "IQComputing on Facebook")
* Follow us on [Twitter](https://twitter.com/iqcomputing/ "IQComputing on Twitter")
* Fork on [Github](https://github.com/IQComputing/wpcf7-recaptcha "IQComputing on Github")

== Installation ==

[Contact Form 7](https://wordpress.org/plugins/contact-form-7/ "Contact Form 7 plugin page") is required to work with this plugin.

1. Install this (Contact Form 7 - reCaptcha v2) plugin
1. Update Contact Form 7 to the latest version
1. Re-add the reCaptcha version 2 API keys (if no keys are currently set). For more information you may read the [Contact Form 7 documentation](https://contactform7.com/recaptcha-v2/ "Contact Form 7 reCaptcha(v2)")
1. Using the left-hand admin navigation in the Contact Form 7 subpages click "reCaptcha Version" (Contact -> reCaptcha Version)
1. Once on the "Contact Form 7 - reCaptcha v2" settings page, select from the select list "reCaptcha Version 2" and click "save"

Once the version 2 API keys are set, the version 2 has been selected in the plugin settings, all [recaptcha] tags will be replaced with the expected Google reCaptcha on all forms.

== Frequently Asked Questions ==

= Will this plugin work with Version 3 keys? =

No and yes. Google reCaptcha has specific keys for each API. If you are doing an upgrade from a previous version of Contact Form 7 to the current version you will need to re-add the Version 2 API keys using the traditional method. That being said you can set the plugin usage to default and it will use the inherit Contact Form 7 reCaptcha Version 3 API.

= Where do I add my Version 2 keys? =

Under Contact -> Integration you can see a "reCAPTCHA" service box where you may either "Setup Integration" following instructions from the [Contact Form 7 documentation](https://contactform7.com/recaptcha-v2/ "Contact Form 7 reCaptcha(v2)"). If you already have keys set you will need to click the "Remove Keys" button and re-add them following the [Contact Form 7 documentation](https://contactform7.com/recaptcha-v2/ "Contact Form 7 reCaptcha(v2)").

= I've added the Version 2 keys but nothing has changed =

If you have added the Version 2 keys and you still do not see the reCaptcha show up on your forms please check the following:

1. In WordPress admin, under Contact -> reCaptcha Version please ensure that you have "reCaptcha Version 2" selected and saved.
1. On your contact forms please ensure that you have the [recaptcha] tag somewhere in the form.

Should the above be correct, at this point it's time to open a support thread for us to look into the issue further.

== Screenshots ==

1. Settings page

== Changelog ==

= 1.1.5 (2019-02-25) =

* Added link to github in description
* Updated version number to fix json checksum (Thanks @willpresleyev)!

= 1.1.4 (2019-02-21) =
* We need your help translating this plugin! Interested parties may contribute at: https://translate.wordpress.org/projects/wp-plugins/wpcf7-recaptcha
*
* Ensured WordPress 5.1 compatibility
* Removed languages folder to avoid confusion with glotpress.
* Multisite - Network Admins will notice a new menu item under plugins labelled "WPCF7 reCaptcha Settings"
* Multisite - Network Admins now have the ability to add default keys and settings for sites. Individual sites can overwrite these defaults should they choose to.
* Multisite - Default Network settings do not override keys or settings if they are already set/saved on the individual site. These only apply if none are found on the individual site.

= 1.1.3 (2019-02-06) =
* An attempt to make translations easier and better overall.
* Combined a few redundant translation functions into a single translation function.
* Made wording and references more consistent.
* Added a margin-bottom: 0 style to the reCaptcha iframe in an attempt to prevent CSS overlapping.