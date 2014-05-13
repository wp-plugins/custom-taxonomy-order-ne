=== Plugin Name ===
Contributors: mpol
Tags: ordering, sorting, terms, custom taxonomies, term order, categories
Requires at least: 3.0
Tested up to: 3.9.0
Stable tag: trunk
License: GPLv2 or later


Allows for the ordering of categories and custom taxonomy terms through a simple drag-and-drop interface

== Description ==

Custom Taxonomy Order New Edition is a plugin for WordPress which allows for the ordering of taxonomies through a
simple drag-and-drop interface using the available WordPress scripts and styles. The plugin is lightweight,
without any unnecessary scripts to load into the admin. It also falls in line gracefully with the look and feel
of the WordPress interface.
This plugin uses it's own menu in the backend.

It is a continuation (or fork) of Custom Taxonomy Order, which has been discontinued.

= Languages =

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

= No questions have been asked yet. =

Email any questions to marcel at zenoweb dot nl

== Screenshots ==

1. Screenshot of the menu page for Custom Taxonomy Order.
The menu completely left lists the different taxonomies.
Left are the main taxonomies. Right (or below) are the sub-taxonomies.

== Changelog ==

= 2.5.1 =
* Added fr_FR (Jean-Christophe Brebion)

= 2.5.0 =
* Added ru_RU (Alex Rumyantsev)
* Small gettext fixes
* update nl_NL

= 2.4.9 =
* Multisite activation doesn't work if it isn't done network wide

= 2.4.8 =
* Don't usort on an array which doesn't contain objects

= 2.4.7 =
* Also filter at the get_terms hook for get_terms() and wp_list_categories()

= 2.4.6 =
* Update pl_PL

= 2.4.5 =
* Improve html/css

= 2.4.4 =
* Remove obsolete images

= 2.4.3 =
* Add settings link

= 2.4.2 =
* New dashicon

= 2.4.1 =
* Add alphabetical sorting to options as well
* Update Polish and Dutch

= 2.4.0 =
* Add Polish translation (Paweł Data)
* Sort Alphabetically (landwire)

= 2.3.9 =
* Fix activation code to really generate term_order column

= 2.3.8 =
* Ouch, remove testing code

= 2.3.7 =
* Fix activation on network install (Matteo Boria)

= 2.3.6 =
* Also add filter for wp_get_object_terms and wp_get_post_terms

= 2.3.5 =
* Only filter categories when auto-sort is enabled

= 2.3.4 =
* Filter added for get_the_categories

= 2.3.3 =
* Fix errors "undefined index" for undefined options

= 2.3.2 =
* Use print for translated substring (Matteo Boria)
* Add Italian Translation (Matteo Boria)

= 2.3.1 =
* Fix PHP error-notice when activating

= 2.3 =
* Add es_ES translataion, thanks Andrew and Jelena

= 2.2 =
* do init stuff in the init function
* also update term_order in term_relationships table
* security update: validate input with $wpdb->prepare()

= 2.1 =
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
