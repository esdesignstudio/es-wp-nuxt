=== Post Types Order  ===
Contributors: nsp-code, tdgu
Donate link: http://www.nsp-code.com/donate.php
Tags: post order, posts order, post sort, posts sort, post types order
Requires at least: 2.8
Tested up to: 6.8.2
Stable tag: 2.3.7
Requires PHP: 5.6
License: GPLv2 or later

Sort posts and custom post type objects using a drag-and-drop, sortable JavaScript AJAX interface, or through the default WordPress dashboard 

== Description ==

<strong>Over 12 MILLIONS DOWNLOADS and near PERFECT rating out of 200 REVIEWS</strong>. <br />
Easily Sort Posts and Custom Post Types with Drag-and-Drop

Take full control of your post order with a powerful plugin that lets you effortlessly reorder posts and custom post types using a simple drag-and-drop interface.

Customize the order directly from the default WordPress post archive list or use the dedicated Re-Order interface, which displays all available items for easy management. Whether you're working with default posts or custom post types, organizing your content has never been easier.
  
= Usage =
This plugin was designed to be user-friendly, ensuring that anyone can easily use its sorting feature, regardless of their WordPress experience:

* Install the plugin via the "Install Plugins" interface or by uploading the post-types-order folder to the /wp-content/plugins/ directory.
* Activate the Post Types Order plugin.
* A new settings page will be added under Settings > Post Types Order. Visit this page and save the options for the first time.
* With the <strong>AutoSort</strong> option enabled, no code changes are needed, the plugin will automatically apply the customized post order.
* Use the Re-Order interface, available for every non-hierarchical custom post type, to change the post order as needed.
* For sorting posts via code, include 'orderby' => 'menu_order' within the custom query arguments. For more details, visit this guide [Sample Usage](http://www.nsp-code.com/sample-code-on-how-to-apply-the-sort-for-post-types-order-plugin/)
  
= Example of Usage =
[youtube https://www.youtube.com/watch?v=6-so4UH-n6M] 

As you can see, reordering posts is as simple as dragging and dropping, with the changes instantly reflected on the front end. 

If the post order doesn’t update on your site, it could be due to one of two reasons: either there was a mistake during setup, or your theme/plugin is using a custom query that doesn't follow WordPress Codex standards. But don’t worry—we’re here to help! You can report the issue in the forum, where many users are happy to assist, or you can contact us directly.

If you encounter any problems with the plugin, feel free to reach out via the forum or contact us directly through our [support page](http://www.nsp-code.com), and we’ll take a look.


= Need advanced features ? = 
For advanced features and functionality, check out the extended version of this plugin at [Advanced Post Types Order](http://www.nsp-code.com/premium-plugins/wordpress-plugins/advanced-post-types-order/)
 * Hierarchically post types order
 * Manual Drag & Drop / Automatic Sorting
 * Specify exact area where to apply through conditionals
 * Advanced query interface filtering and complex sorts including multiple post types and taxonomies
 * Posts Order by Custom Taxonomies
 * Enhanced Interface, List / Grid View
 * Allow Interface Filters (Categories, Dates, Search etc)
 * Post Types Thumbnails
 * Advanced query usage
 * MultiSite Network Support, WPML, Polylang, WooCommerce, WP E-Commerce, Platform Pro, Genesis etc
 * WPML 100% compatibility with sort synchronization across languages
 * Mobile Touch Drag & Drop Ready
 * Sort interfaces through admin and front end
 * Pagination for sort lists
 * Free Updates
 * Free Support


<br />
<br />This plugin is developed by <a target="_blank" href="http://www.nsp-code.com">Nsp-Code</a>

== Installation ==

1. Upload `post-types-order` folder to your `/wp-content/plugins/` directory.
2. Activate the plugin from Admin > Plugins menu.
3. Once activated you should check with Settings > Post Types Order 
4. Use Re-Order link which appear into each post type section or use default WordPress interface to make your sorting.


== Screenshots ==

1. The ReOrder interface through which the sort can be created.

2. Sort can be managed within default WordPress post type interface.


== Frequently Asked Questions  ==

Feel free to contact us at electronice_delphi@yahoo.com.

= Since I have no PHP knowledge at all, is this plugin for me? =

Absolutely! You don't need any PHP knowledge to use this plugin. 
Unlike many other plugins, you won't have to make any code changes for your custom post order to apply. There’s an option to automatically update WordPress queries so posts are displayed in your custom order. If you prefer to handle this manually, you can disable the **AutoSort** option.

= How to manually apply the sort in queries =

Simply include `'orderby' => 'menu_order'` in your custom query arguments.

= What types of posts/pages can I sort with this plugin? =

You can sort **all** post types you’ve defined in WordPress, as long as they are not hierarchical. This includes default post types like Posts, as well as custom types like Movies, Reviews, Data, etc.

= How does the post order apply in the admin interface? =

There’s an option to display the custom post order, as defined in the sort list, directly within the main admin post list interface.

= I have a feature request. Can it be implemented? =

All ideas are welcome! I add them to my list for future versions. However, this may take some time. If you're in a hurry, consider making a small donation, and I can prioritize the feature for you.

= Can I exclude certain queries from the custom sort when AutoSort is enabled? =

Yes, you can! To exclude certain queries, include the `ignore_custom_sort` argument in your custom query. An example is available at [Sample Usage](http://www.nsp-code.com/sample-code-on-how-to-apply-the-sort-for-post-types-order-plugin/)

= How can I force custom sorting for specific queries when AutoSort is enabled? =

You can use the `pto/posts_orderby` filter to force the sort. An example is provided at [Sample Usage](http://www.nsp-code.com/sample-code-on-how-to-apply-the-sort-for-post-types-order-plugin/)

= I need additional features like front-end sorting, shortcodes, filters, conditionals, advanced queries, or taxonomy/category sorting =

Consider upgrading to our advanced version of this plugin, which offers these features at a very reasonable price [Advanced Post Types Order](http://www.nsp-code.com/premium-plugins/wordpress-plugins/advanced-post-types-order/)


== Changelog ==

= 2.3.7 = 
 - Code improvements.
 - Reduce the outputted HTML for the ReOrder interface to avoid memory limitation on very long lists. 
 - Re-Order interface style updates.
 - WordPress 6.82 compatibility check and tag update.

= 2.3.5 = 
 - WordPress 6.8 compatibility check and tag update.

= 2.3.4 = 
 - PHP 8.3.4 tag and compatibility check.
 - WordPress 6.7.2 compatibility check and tag update.
 - Readme revision. 

= 2.3.3 = 
 - Add version to the plugin assets to avoid caching issues. 
 - Improve the options description for easier understanding. 
 - New filter pto/interface/table/tbody
 - WordPress 6.7 compatibility check and tag update.

= 2.3.2 = 
 - Fix: Change the CPT_VERSION constant to avoid conflict with CPT UI plugin.

= 2.3.1 = 
 - Improve the descriptions in the readme.txt file for better clarity and user understanding.
 - Add a version number to the CSS file to ensure that browsers load the latest version correctly and avoid caching issues.

= 2.3 = 
 - Enhanced re-order interface to better align with the default WordPress styling.
 - Added additional object actions (e.g., Edit, View) directly within each item row. New setting to control the visibility of actions.
 - Introduced new filters for extending the re-order table: pto/interface/table/thead, pto/interface/table/tfoot, and pto/interface/table/tbody.
 - Completed compatibility check and tagged update for WordPress 6.6.2.

= 2.2.6 = 
 - Remove boolval on the filter 'pto/posts_orderby' to avoid returning wrong FALSE.
 - FlyingPress cache lear method update.

= 2.2.4 = 
 - Add code comments hints for easier understanding and follow.
 - Use strval when comparing strings using ===
 - Use additional sanitize_text_field and isset checks to avoid PHP notices.
 - Use === "strict equality" comparison operator instead simple
 - Include the check for JetPack mobile, if plugin is active.
 - Check if there is a post_status filter and if set to 'all' to continue scripts enqueue.
 - New filter pto/interface/query/args to allow adjustments for the re-order interface query arguments.
 - WordPress 6.6.1 compatibility check and tag update.

= 2.2.3 = 
 - When order update, attempt to clear the site / server caches. 
 - Set Yes as default for the option to Enable sortable drag-and-drop functionality within the default WordPress post type archive.
 - WordPress 6.5.3 compatibility tag.

= 2.2.1 = 
 - Options interface layout updates.
 - Code cleanup
 - Remove unused svg icon file.
 
= 2.2 =
 - Formidable style fix when Autosort is active.
 - Plugin headers format update.
 - WordPress 6.5 compatibility check and tag update ( RC1 )
 
= 2.1.8 =
 - Fix: Media/images order, retrieve the items per page from user upload_per_page
 
= 2.1.4 =
 - PHP Deprecated fix: Constant FILTER_SANITIZE_STRING is deprecated
 - WordPress 6.4.3 compatibility check and tag update

= 2.1.2 =
 - PHP 8.2.4 check for compatibility
 - WordPress 6.4.2 compatibility check and tag update

= 2.1 =
 - Trigger wp_cache_flush when saving the order to clear the internal caches
 - WordPress 6.3 compatibility check and tag
 - Compatibility update for lite speed cache

= 2.0.9 =
 - Fix: is_plugin_active

= 2.0.7 =
 - Compatibility class re-build
 - Fix: Enfold templates when using Admin Sort

= 2.0.5 =
 - Merge the "default archive&drop" option and keep the individual menu Yes/No for the default WordPress interfaces, to avoid confusion.

= 2.0.2 =
 - The Archive drag & drop is disabled by default
 - Small layout changes
 - WordPress 6.2 compatibility tag

= 2.0 =
 - Update cmoposer.json to use the wpackagist.org
 - New option to select the drag & drop available for post types. 
 - The drag & drop within the default WordPress interfaces can be done now through the new icon, under the checkbox, for each item.
 - Check if ajax call to avoid applying the order when autosort is disabled. 
 - Update plugin header image

= 1.9.9.2 =
  - WordPress 6.1.1 compatibility tag

= 1.9.9.1 =
  - WordPress 6.0 compatibility tag
  
= 1.9.9 =
  - Fix layout change when sorting by drag&drop within default WordPress interface.
  - Add placeholder row size by setting a tr colspan of the dragable element.

= 1.9.8 =
  - Readme file updates, typos fixes.
  - WordPress 5.9 compatibility tag

= 1.9.7 =
  - Remove Twitter button
  - Remove unused gif image
  - HTPML and CSS cleanup
  
= 1.9.5.7 =
  - Code cleanup
  - WordPress 5.8.1 compatibility tag

= 1.9.5.6 =
  - Fix PHP implode() notice

= 1.9.5.5 =
  - Fix PHP implode() notice

= 1.9.5.4 =
  - Fix PHP implode() notice
  - Ensure the drag & drop interface show for correct post types, non hierarchically to ensure correct functionality
  - WordPress 5.6 compatibility tag

= 1.9.5.2 =
  - Clean post cache on order update to allow menu_order to change on cached data
  - WordPress 5.5 compatibility tag

= 1.9.5.1 =
  - Fix: Outputs the admin save notice through admin_notices filter

= 1.9.5 =
  - Fix: disable drag & drop within taxonomies interfaces; fix WooCommerce attributes sort issue
  - Reorder interface slight styles improvements
  - Compatibility tag update for WordPress 5.4.2

= 1.9.4.3 =
  - Option text translation update
  - Changed the posts class to to wp-list-table when applying sortable for better compatibility  
  - Compatibility tag update for WordPress 5.4

= 1.9.4.2 =
  - Compatibility tag update for WordPress 5.3

= 1.9.4.1 =
  - Ignore the Events Calendar posts
  - Filter typo fix

= 1.9.3.9 =
  - Ignore sorting when doing Search and there's a search key-phrase specified.
  - Ignore sorting when doing Search within admin dashboard
  - Removed Google Social as it produced some JavaScript errors
  - WordPress 4.9.7 tag update 

= 1.9.3.6 =
  - Clear LiteSpeed Cache on order update to reflect on front side
  - WordPress 4.9.1 tag update 

= 1.9.3.5 =
  - Fix: updated capability from switch_theme to manage_options within 'Minimum Level to use this plugin' option
  - Default admin capability changed from install_plugins to manage_options to prevent DISALLOW_FILE_MODS issue. https://wordpress.org/support/topic/plugin-breaks-when-disallow_file_mods-is-set-to-true/
  - Prepare plugin for Composer package

= 1.9.3.3 =
  - Plugin option to include query argument ASC / DESC

= 1.9.3.2 =
  - Include ASC / DESC if there is a query order argument
  - Avada fix 'product_orderby' ignore

= 1.9.3.1 =
  - WordPress 4.8 compatibility notice
  - Slight code changes, remove unused activate / deactivate hooks
  - Updated po translation file
  - Updated assets

= 1.9.3 =
  - Fix for custom post type objects per page when using default archive interface drag & drop sort
  - Plugin code redo and re-structure
  - Improved compatibility with other plugins
  - Security improvements for AJAX order updates

= 1.9 =
  - Remove translations from the package
  - Remove link for donate
  - Wp Hide plugin availability notification
  - New Filter pto/get_options to allow to change default options; Custom capability can be set for 'capability'
  - New Filter pto/admin/plugin_options/capability to allow custom capability option to be inserted within html
 
 
== Upgrade Notice ==

Make sure you get the latest version.


== Localization ==

Would you like to contribute a translation in your language? Please check at https://translate.wordpress.org/projects/wp-plugins/post-types-order

There isn't any Editors for your native language on plugin Contributors? You can help to moderate! https://translate.wordpress.org/projects/wp-plugins/post-types-order/contributors
