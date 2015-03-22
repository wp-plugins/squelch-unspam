=== Squelch Unspam ===
Contributors: squelch
Donate link: http://squelchdesign.com/wordpress-plugin-squelch-unspam/
Tags: comments, spam, filter, spam filter, comment spam filter, stop spam, prevent spam, reduce spam, prevent automated spam, no captcha anti-spam, anti-spam
Requires at least: 2.0
Tested up to: 4.1.1
Stable tag: 1.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Unspam makes it harder for spammers to automatedly send spam to your blog by changing the names of the fields in the comment forms.

== Description ==

Unspam by Squelch Design is the simplest plugin you can find for **reducing your comment spam** problem. Once installed there's nothing
to configure, and nothing changes to your visitors: No captcha or silly games. Once installed
the plugin will simply randomize the names of the fields in the comments form on your blog and reject comments that are sent to the
standard WordPress field names, or where bots have blindly submitted data to the honeypot fields.

What this means for spammers is that they have to do quite a lot more work to send spam to your website. It may also make sending
spam to your website unreliable as changes to your theme may upset their spam submission tools. Or they may have to resort to using
humans to send spam to your website (not much I can do about that I'm afraid) which will cost them more money.

Currently implemented:

*   Names of fields are randomized every night at 12:00,
*   Submissions to the standard WordPress field names are automatically deleted,
*   Honeypot fields added to comments form,
*   WooCommerce support.

Additional (planned) features:

*   Statistical collection,
*   Automated blocking of persistent IPs,
*   Opt-in centralized collection of comment spam and statistics for additional research.

== Installation ==

### Recommended Installation

1. From your admin interface go to Plugins > Add New
1. Search for *unspam*
1. Click "Install Now" under the plugin in the search results
1. Click OK on the popup
1. Click "Activate" to enable the plugin

### Manual Installation

1. Unzip the installation zip file
1. Copy the files to your plugins directory (via FTP or whatever)
1. From the admin interface click Plugins
1. Find the plugin in the list of plugins and click "Activate"

### Configuration

Currently there is no configuration on this plugin.

== Frequently Asked Questions ==

= How do I configure the plugin? =

There is currently no configuration available on this plugin, just install it and activate it and it will start protecting your blog.

= Does this plugin require my readers to fill out a CAPTCHA? =

No. I hate CAPCTHAs as much as the next person, this plugin does not use them nor does it require JavaScript to be enabled on your
visitors' browsers.

= Does this plugin require JavaScript to be enabled on my visitors' browsers? =

As of version 1.3, yes. This plugin will require JavaScript to be enabled by your users as an additional safeguard against spam.

= Don't my users have to do something to prove they're human? =

Nope, spammers just have to prove that they're NOT human. We give the spammers enough rope to hang themselves with.

= What about false positives? =

With the exception of a few rare scenarios and users without JavaScript enabled, there shouldn't be any false positives.

== Changelog ==

= 1.3 =
* Fixed a minor bug that could be exploited to circumvent some of the protection that was in place.
* Added two new honeypot fields to the form.
* Changed the naming scheme for randomized fields to make them legitimate page IDs.
* Added in a JavaScript check (very few spammers will execute JavaScript).
* Modified the hiding of the fields to use inline CSS instead of embedded styles to make it harder to detect honeypots.
* Enhancements to give WooCommerce reviews the same protection as comment forms.
* Created two external dependencies without which the comment form will not work in an attempt to catch out manual spammers.

= 1.2.1 =
* Removed the 'WooCommerce not supported' message

= 1.2 =
* Added in WooCommerce support.

= 1.1 =
* Fix for plugin interfering with 404 Redirected plugin (and potentially other plugins) in the admin interface

= 1.0.1 =
* Removed the 'Field names will automatically update next time a post/page with comments enabled is viewed' message by default, can be re-enabled by appending ?unspam-rmvmsg=showfieldupdatemessage to the page URL (in admin). Only really useful for testing.
* Added "Remove this message" options to messages generated in admin.

= 1.0 =
* Initial version

== Upgrade Notice ==

= 1.3 =
Bug-fixes and enhancements to improve spam filtering, since some spammers are starting to get wise to this plugin. Note that your users WILL have to have JavaScript enabled in order to post comments from version 1.3 on.

= 1.2.1 =
Minor update to 1.2 to remove the 'WooCommerce not supported' message.

= 1.2 =
This release was intended for 1.1, but 1.1 went live prematurely due to an error in the repository. 1.1 fixed a bug with Unspam interfering with forms other than the new comment form, 1.2 adds in support for WooCommerce.

= 1.1 =
1.1 provides bug-fixes to prevent Unspam interfering with other plugins you may be using that share field names with the comment form.

= 1.0.1 =
1.0.1 provides tweaks to the admin interface to make Unspam less intrusive and provides checks for WooCommerce, which it is not currently compatible with.

= 1.0 =
Initial version
