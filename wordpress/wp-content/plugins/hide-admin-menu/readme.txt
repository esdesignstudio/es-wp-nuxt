=== Hide Admin Menu ===
Contributors: bhavinthummar
Donate link: https://www.paypal.me/BThummar
Tags:  menu hide, admin menu hide, admin menu show, admin menu plugin, user role
Requires at least: 4.6
Tested up to: 6.5.4
Stable tag: 1.1.2
License: GPLv2 or later
License URI: 
 
Using this plugin, we can hide the admin menu easily.
 
== Description ==

This plugin gives the facility for hiding and showing the admin menu of the side and top bars.

This plugin gives an easy way to hide admin menus by checking the checkbox of a particular menu in the form and then submitting the form so that checked menus hide from the admin.

Admin also can hide menu according to the role of users.

<iframe width="560" height="315" src="https://www.youtube.com/embed/LiXcE6aEvdI" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
 
== Installation ==
 
1. Upload hide-admin-menu to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Open Hide Menu from the menu bar and then check or tick mark those menus that you won't hide from the admin bar.
 
== Frequently Asked Questions ==
 
= How can I show hide menus? =
 
 You can show the menu that is hidden by this plugin doing the uncheck those menus from the menu list in the form.
 
= What should you do when you hide all the menus with this plugin? =

You should open the below URL
YOUR-WEBSITE-URL/wp-admin/admin.php?page=hide-admin-menu

Update the necessary setting to show any menu again by this URL.	
 
== Screenshots ==

1. This is the page of menu hide where the user can hide and show the admin menu.

 
== Changelog ==
1. Solved some warnings and notices at the time of the save process at version WordPress 4.8.  
2. Solved 2 character error issue at the time of activation of the plugin and remove deprecate function on version 1.0.4
3. Tested with WordPress 5.4.1
4. Solved the error shown in the site health tools.
5. Solved the issue of the session related to version 1.0.7. So please update the version of 1.0.8 which is the latest one.
6. Removed the use of the $_SESSION of PHP and used the wp_session in version 1.0.9 to solve the session-related warning in the website. 
7. In version 1.1.0, solved the issue of not hiding the customized menu. Also removed unnecessary CSS for one class from the CSS file which conflicts with other CSS.
8. In version 1.1.1, Tested with WordPress 6.2 and solved the warning related to the already sent content during the updating of the form.
9. Version 1.1.2 is tested with WordPress 6.5.4.

== Upgrade Notice ==
1. This new version is where you can also hide the sub-menu of the sidebar of admin. 
2. Tested up to WordPress version 5.0.2
3. We have added a parent and child structure for the top admin menu to version 1.0.3 according to one user request. 
4. We have added the new feature of menu hides according to a user role.