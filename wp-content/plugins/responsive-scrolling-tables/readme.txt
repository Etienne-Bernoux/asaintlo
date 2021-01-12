=== Responsive Scrolling Tables ===
Contributors: TJDixonLimited
Tags: tables, responsive tables, responsive
Requires at least: 3.9.1
Tested up to: 4.1
Stable tag: 4.1
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Responsive Scrolling Tables detects when tables are bigger then their containers and makes them scroll instead of flowing over the boundary of the container.

== Description ==

What is Responsive Scrolling Tables (RST)?
----
RST is a plugin for your Joomla! website that prevents tables from breaking your responsive layout.

Who is RST for?
----
RST is for anyone wishing to use tables in a mobile friendly way without having to add classes or special formatting to the table. It's aimed at WYSIWYG users to help them focus more on the content and worry less about the mobile display.

What does RST do?
----
It detects when tables are bigger then their containers and makes them scroll instead of flowing over the boundary of the container.

Please see screen shots for examples.

Why create RST?
----
We created RST because our clients like to write their own content and sometimes it is necessary to use tables. We respect that our clients are not HTML masters and learning HTML is often not one of their long term goals, so we needed an easy to use way of making their tables display correctly and easily on mobile devices.

How does RST work?
----
It inserts a JavaScript file into the document that detects if the document contains any tables. With each table it finds, it checks whether the table's width is larger then its parent, when a table is going to break the display of a page and makes it scroll instead by inserting a scrollable parent around the table. It also runs when the page is resized allowing users to maintain usability even after the page has loaded.

== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Upload `responsivescrollingtables-wordpress-version.zip` via the plugin installer
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Sit back and relax

== Frequently Asked Questions ==

= The plugin doesn't work =

The first step is to make sure that you have activated the Responsive Scrolling Tables plugin.

== Screenshots ==

1. The table without responsive scrolling tables. Notice how the whole page has a scroll bar.
2. With RST only the table has a scroll bar. This creates a much more pleasent experience on mobiles.

== Changelog ==

= 1.2.1 =
* Moved from overriding the document.onreadystatechange to using document.addEventListener

= 1.2.0 =
* Changed to use wp_enqueue_script

= 1.1.0 =
* Removing the 'echo' that outputed the RST file location within the plugin

= 1.0.0 =
* Initial release of RST

== Upgrade Notice ==

= 1.2.1 =
Small change to use an event listener, resolves issues with other javascript not playing nice.

= 1.2.0 =
Updating to this version will fix errors about head already being sent/changed (essential if experiencing issues with other plugins).

= 1.1.0 =
Upgrade to 1.1.0 for php error fix.

= 1.0.0 =
Initial release.
