=== ReCaptcha v2 for Contact Form 7 ===
Contributors: iqcomputing
Tags: contact-form-7, contact-form-7-recaptcha, recaptcha, spam
Requires at least: 4.9
Tested up to: 6.4
Stable tag: 1.4.5
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

1. Install this (ReCaptcha v2 for Contact Form 7) plugin
1. Update Contact Form 7 to the latest version
1. Re-add the reCaptcha version 2 API keys (if no keys are currently set). For more information you may read [How to Generate Google reCAPTCHA v2 Keys](https://www.iqcomputing.com/support/articles/generate-google-recaptcha-v2-keys/) by IQComputing and [Contact Form 7 documentation](https://contactform7.com/recaptcha-v2/ "Contact Form 7 reCaptcha(v2)")
1. Using the left-hand admin navigation in the Contact Form 7 subpages click "reCaptcha Version" (Contact -> reCaptcha Version)
1. Once on the "ReCaptcha v2 for Contact Form 7" settings page, select from the select list "reCaptcha Version 2" and click "save"

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

= 1.4.4 (2023-08-09) =
* Tested and updated to support WordPress 6.3: Lionel

= 1.4.1 (2022-07-29) =
* Fixed issue with validation error message not appearing on submission.

= 1.4.0 (2022-06-01) =
* Updated supported version for WordPress 6.0