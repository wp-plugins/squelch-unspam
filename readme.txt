=== Squelch Unspam ===
Contributors: squelch
Donate link: http://squelchdesign.com/wordpress-plugin-squelch-unspam/
Tags: comments, spam, filter, spam filter, comment spam filter, stop spam, prevent spam, reduce spam, prevent automated spam, no captcha anti-spam, anti-spam
Requires at least: 2.0
Tested up to: 3.5.1
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Unspam makes it harder for spammers to automatedly send spam to your blog by changing the names of the fields in the comment forms.

== Description ==

Unspam by Squelch Design is the simplest plugin you can find for **reducing your comment spam** problem. Once installed there's nothing
to configure, and nothing changes to your visitors: No captcha or silly games, they don't need JavaScript enabled. Once installed
the plugin will simply randomize the names of the fields in the comments form on your blog and reject comments that are sent to the
standard WordPress field names.

What this means for spammers is that they have to do quite a lot more work to send spam to your website. It may also make sending
spam to your website unreliable as changes to your theme may upset their spam submission tools. Or they may have to resort to using
humans to send spam to your website (not much I can do about that I'm afraid) which will cost them more money.

This plugin is still in beta and so I'd appreciate your help in testing it.

Currently implemented:

*   Names of fields are randomized every night at 12:00,
*   Submissions to the standard WordPress field names are automatically deleted.

Planned:

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

= What does "Field names will automatically update next time a post/page with comments enabled is viewed" mean? =

The field names on the comment form randomize every night at midnight. This randomization takes place automatically the next time after
12:00 that somebody views a page that has a comments form enabled on it. If nobody visits such a page the message will remain.

= How do I configure the plugin? =

There is currently no configuration available on this plugin, just install it and activate it and it will start protecting your blog.

= Does this plugin require my readers to fill out a CAPTCHA? =

No. I hate CAPCTHAs as much as the next person, this plugin does not use them nor does it require JavaScript to be enabled on your
visitors' browsers.

= Does this plugin require JavaScript to be enabled on my visitors' browsers? =

No. This plugin does not require JavaScript to be enabled, nor does it use CAPTCHAs.

= What about false positives? =

This plugin is special in that false positives should never really occur with the exception of the small possibility that a human
visitor might open a page just before midnight then submit a comment just after midnight. Their comment would be rejected as spam.
I expect to put in a feature to prevent even this from occurring in the near future.

== Changelog ==

= 1.0 =
* Initial version

== Upgrade Notice ==

= 1.0 =
Initial version
