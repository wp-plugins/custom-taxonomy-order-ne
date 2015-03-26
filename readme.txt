=== Plugin Name ===
Contributors: mpol
Tags: ordering, sorting, terms, custom taxonomies, term order, categories, category order
Requires at least: 3.0
Tested up to: 4.2
Stable tag: 2.6.3
License: GPLv2 or later


Allows for the ordering of categories and custom taxonomy terms through a simple drag-and-drop interface

== Description ==

Custom Taxonomy Order New Edition is a plugin for WordPress which allows for the ordering of taxonomies.

It supports the following features:

* Order (custom) taxonomies through a simple drag-and-drop interface.
* No custom coding needed. It uses standard WordPress filters.
* It uses the available WordPress scripts and styles.
* The plugin is lightweight, without any unnecessary scripts to load into the admin.
* It falls in line gracefully with the look and feel of the WordPress interface.
* It uses it's own menu in the backend.
* Translated or translatable.
* There is no Pro version, everything works in the Free version.

It is a continuation (or fork) of Custom Taxonomy Order, which has been discontinued.

= Languages =

* de_DE [Patrick Skiebe](http://skiebe.eu)
* es_ES [Andrew Kurtis](http://webhostinghub.com)
* fr_FR [Jean-Christophe Brebion](http://jcbrebion.com)
* it_IT Matteo Boria
* nl_NL [Marcel Pol](http://zenoweb.nl)
* pl_PL [Paweł Data](webidea.pl)
* ru_RU Alex Rumyantsev

== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Order posts from the 'Term Order' menu in the admin
4. Optionally set whether or not to have queries of the selected taxonomy be sorted by this order automatically.
5. Optionally set `'orderby' => 'term_order', 'order => 'ASC'` to manually sort queries by this order.
6. Enjoy!

== Upgrade Notice ==

If you update from the original Custom Taxonomy Order please deactivate that first, then activate this plugin.

== Frequently Asked Questions ==

= I sorted the terms in the WordPress backend, but I don't see it changed in the frontend =

Did you set the option for that taxonomy to use that custom order? Make sure to check it so the filters run
with your taxonomy.

= My custom taxonomy is not available in the menu page =

This plugin will only offer to sort them when the taxonomy is set to public. Make sure you use 'register_taxonomy'
with the public parameter set to true (default).

= How do I sort the terms when using a custom query? =

You can apply the sorting for the taxonomy by using:
	'orderby' => 'term_order'.

= I have a custom taxonomy that uses the Tag Cloud functionality, but it doesn't sort like it should. =

Can you tell me what is the name for the taxonomy?
In the customtaxorder_wp_get_object_terms_order_filter it needs to be added, and the get_terms filter should not run
on that taxonomy. The tag_cloud_sort filter should do that.

= I'm using the_tags function, but it doesn't sort as it should. =

There is a bug with the the_tags function, where it will sort according to the setting for categories.
And yes, that is strange :).

= What capabilities are needed? =

For sorting the terms you need the manage_categories capability.

= Is there an API? =

There is an action that you can use with add_action. It is being run when saving the order of terms in the admin page.
You could add the following example to your functions.php and work from there.

	<?php
	function custom_action($new_order) {
		print_r($new_order);
	}
	add_action('customtaxorder_update_order', 'custom_action');
	?>

Email any other questions to marcel at zenoweb dot nl

== Screenshots ==

1. Screenshot of the menu page for Custom Taxonomy Order.
The menu completely left lists the different taxonomies.
Left are the main taxonomies. Right (or below) are the sub-taxonomies.

== Changelog ==

= 2.6.3 =
* 2015-03-25
* Support Link Manager plugin.

= 2.6.2 =
* 2015-03-21
* Better suppport for WPMU, also set up new blogs (thanks Andrew Patton).

= 2.6.1 =
* 2015-03-13
* Wrap radio buttons inside label, so the label works (thanks Andrew Patton).

= 2.6.0 =
* 2015-02-28
* Separate settingspage to own php-file.
* Add de_DE (thanks Patrick Skiebe).

= 2.5.9 =
* 2015-01-21
* Add test for capability inside admin page as well.

= 2.5.8 =
* 2014-12-11
* Fix conflict with wp-catalogue plugin

= 2.5.7 =
* 2014-09-12
* Fix notices with defensive programming

= 2.5.6 =
* 2014-08-22
* More compatibility with WPML

= 2.5.5 =
* 2014-08-20
* Some Compatibility with WPML Plugin

= 2.5.4 =
* 2014-08-15
* Add action for saving the terms

= 2.5.3 =
* 2014-08-06
* New default settings page
* Filter added for get_the_terms
* Don't filter tags at get_terms filtering
* Updated nl_NL

= 2.5.2 =
* 2014-06-30
* Also be able to sort the builtin taxonomies
* Fix bug with sorting tags

= 2.5.1 =
* 2014--5-13
* Added fr_FR (Jean-Christophe Brebion)

= 2.5.0 =
* 2014-05-02
* Added ru_RU (Alex Rumyantsev)
* Small gettext fixes
* update nl_NL

= 2.4.9 =
* 2014-04-15
* Multisite activation doesn't work if it isn't done network wide

= 2.4.8 =
* 2014-04-11
* Don't usort on an array which doesn't contain objects

= 2.4.7 =
* 2014-03-29
* Also filter at the get_terms hook for get_terms() and wp_list_categories()

= 2.4.6 =
* 2014-03-24
* Update pl_PL

= 2.4.5 =
* 2014-03-23
* Improve html/css

= 2.4.4 =
* 2014-03-23
* Remove obsolete images

= 2.4.3 =
* 2014-03-22
* Add settings link

= 2.4.2 =
* 2014-03-22
* New dashicon

= 2.4.1 =
* 2014-03-22
* Add alphabetical sorting to options as well
* Update Polish and Dutch

= 2.4.0 =
* 2014-03-18
* Add Polish translation (Paweł Data)
* Sort Alphabetically (landwire)

= 2.3.9 =
* 2014-02-25
* Fix activation code to really generate term_order column

= 2.3.8 =
* 2014-02-18
* Ouch, remove testing code

= 2.3.7 =
* 2014-02-18
* Fix activation on network install (Matteo Boria)

= 2.3.6 =
* 2014-01-26
* Also add filter for wp_get_object_terms and wp_get_post_terms

= 2.3.5 =
* 2014-01-26
* Only filter categories when auto-sort is enabled

= 2.3.4 =
* 2014-01-25
* Filter added for get_the_categories

= 2.3.3 =
* 2014-01-25
* Fix errors "undefined index" for undefined options

= 2.3.2 =
* 2014-01-03
* Use print for translated substring (Matteo Boria)
* Add Italian Translation (Matteo Boria)

= 2.3.1 =
* 2013-12-30
* Fix PHP error-notice when activating

= 2.3 =
* 2013-12-10
* Add es_ES translataion, thanks Andrew and Jelena

= 2.2 =
* 2013-10-20
* do init stuff in the init function
* also update term_order in term_relationships table
* security update: validate input with $wpdb->prepare()

= 2.1 =
* 2013-10-10
* renamed/forked as Custom Taxonomy Order New Edition
* fixed a bug with ordering in the backend
* add localisation
* add nl_NL lang

= 2.0 =
* Complete code overhaul and general rewrite to bring things up to speed
* Updated for WordPress 3.2 Admin Design
* Added auto-sort query option
* Several text fixes for overall consistency and clarity.
* Various small bugfixes and optimizations

= 1.0 =
* First Version
