=== Registration Integration for miRespond ===
Contributors: miware
Tags: mirespond, autoresponders, newsletters, lists, list management, email, marketing, newsletter, subscribe, miware integrations, mirespond integration
Requires at least: 4.1
Tested up to: 4.9.1
Stable tag: 1.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires PHP: 5.2.4

Seamlessly adds WordPress users to your miRespond Autoresponder during registration on your site, either by request or silently.

== Description ==

Integrates the miRespond contact registration script into your WordPress registration process. Users are seamlessly added to your miRespond account during registration on your site, either by request or silently. If you do not yet have a free miRespond account, you will need to go to http://www.miware.co.za/mirespond and sign up for one.
#### miRespond Autoresponder WordPress

*Adding your WordPress users to your miRespond lists should be automatic. With this plugin, it is.*

Registration Integration for miRespond helps you add more subscribers to your miRespond lists.

#### Some of the Registration Integration for miRespond features

- Connect with your miRespond account in seconds.

- Seamless addition of your WordPress users to miRespond Lists

#### What is miRespond?

miRespond is a newsletter service that allows you to send out email campaigns to a list of email subscribers. miRespond is free for up to 2500 subscribers.

This plugin acts as a bridge between your WordPress site and your miRespond account, seamlessly connecting the two together.

If you do not yet have a miRespond account, [creating one is 100% free and only takes you about 20 seconds](http://www.miware.co.za/mirespond-free-autoresponder/).
<blockquote>
<h4>Some of miRespond's Features</h4>
<ul>
<li>Unlimited Broadcast Emails</li>
<li>Pre-Populated Templates for creating great looking emails quickly and easily</li>
<li>Built in Download manager with link protection and limited downloads per subscriber</li>
<li>Open emails, clicked links and number of download tracking</li>
<li>Many more!</li>
</ul>
<p><a href="http://www.miware.co.za/mirespond-autoresponder-features/">View more features</a></p>
</blockquote>


== Installation ==

#### Installing the plugin
1. In your WordPress admin panel, go to *Plugins > New Plugin*, search for **Registration Integration for miRespond** and click "*Install now*"
1. Alternatively, download the plugin and upload the contents of the .zip file to your plugins directory, which usually is `/wp-content/plugins/`.
1. Activate the plugin
1. Configure the plugin

#### Configuring Registration Integration for miRespond
- Use the Settings->miRespond Registration Integration screen to configure the plugin:
	- You need to enter your Campaign ID. This information can be found in [your miRespond controlpanel](http://www.miware.co.za/mirespond/). After logging into your miRespond account, select Tools, then Form from the submenu.  Scroll down below the big block of code towards the bottom and retrieve the required values shown alongside 'For Integration purposes:', only copy the bold characters between the '', ie '20,250' insert 20,250 into the corresponding fields.
	- You need to enter your Campaign Hash. This information can be found in [your miRespond controlpanel](http://www.miware.co.za/mirespond/) as detailed above.
	- You can insert Extra Campaign IDs under Extra Campaigns if you would like to add the users to more than one campaign in miRespond.
	- You can display an optin option by selecting the Display Opt-In? checkbox.  This will give your users an optin option on the registration form and only users who select the optin will be exported to miRespond.
	- You can pause the exporting of users by selecting the Disable Integration? option, this allows you to still keep the details of which users have been posted in miRespond.  Disabling the plugin through the WordPress plugins will delete this data.
	
== Frequently Asked Questions ==

= Where do I find my Campaign ID? =

This information can be found in your miRespond controlpanel. After logging into your miRespond account, select Tools, then Form from the submenu.  Scroll down below the big block of code towards the bottom and retrieve the required value for Campaign ID displayed alongside 'For Integration purposes:', only copy the bold characters between the '', ie '20' insert 20 into the corresponding fields.

= Can I post to multiple Campaigns? =

Yes, you can insert a comma seperated list of Campaign IDs under Extra Campaigns.  In this case, ensure you have only one main Campaign ID inserted under Campaign ID, and all extra Campaigns are added to Extra Campaigns.

= Can I insert multiple Campaigns under Campaign ID? =

No, Campaign ID must be a single campaign, if you want to insert multiple Campaigns, you can add further campaigns under Extra Campaigns using commas to seperate multiple campaigns.

== Screenshots ==


== Changelog ==

= 1.0 =
* First Version.


== Upgrade Notice ==

= 1.0 =
This is the first version.
