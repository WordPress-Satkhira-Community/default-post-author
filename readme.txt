=== Default Post Author ===
Contributors: wpdelower, wpsatkhira
Tags: post author, default post author, wp post, posts, author management
Requires at least: 6.1
Tested up to: 7.1
Requires PHP: 8.0
Stable tag: 2.3.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Donate Link: https://www.wpsatkhira.com/donate

Easily set a default post author for new posts, pages and custom post types. Capability-aware, bulk update and WP-CLI support.

== Description ==

**Try Default Post Author on a demo site: Click here => [https://tastewp.com/plugins/default-post-author](https://tastewp.com/plugins/default-post-author/).**

The **Default Post Author** plugin is a powerful tool designed to simplify the process of setting a default post author on your WordPress site. With this plugin, you can easily define a default author for all new posts (and other post types), ensuring consistency and saving you valuable time.

= ✨ Key Features =

* **Easy Setup** - Quick and straightforward configuration
* **Modern Admin Interface** - Beautiful, intuitive settings page with smooth animations
* **Toggle Controls** - Enable/disable features with modern toggle switches
* **Multi Post-Type Support** - Apply the default author to Posts, Pages and any public Custom Post Type that supports authors
* **Capability-Aware Assignment** - Prevents permission errors when Authors create posts (new in 2.3.0)
* **Bulk Update** - Update existing posts to the default author with real AJAX batching and accurate progress
* **Dry-Run Mode** - Preview how many posts would be changed before running a bulk update
* **Post Status Filter** - Limit bulk updates to Published, Draft, Pending, etc.
* **Revision Sync** - Keep revision authors in sync with parent posts
* **WP-CLI Support** - Manage settings and run bulk updates from the command line
* **AJAX-Powered** - Real-time saving without page reloads
* **Progress Tracking** - Visual progress bar for bulk operations
* **Responsive Design** - Works perfectly on all devices
* **Lightweight** - Optimized code for maximum performance
* **Developer Friendly** - Useful filters and actions for customization

= 🎨 Modern Admin Interface =

* Clean, card-based layout
* Smooth animations and transitions
* Modern toggle switches
* Interactive buttons with loading states
* Toast notifications for instant feedback
* Accurate progress indicators for bulk operations
* Fully responsive design

= 🚀 How It Works =

1. Install and activate the plugin
2. Navigate to **Settings > Default Post Author**
3. Select your preferred default author from the dropdown
4. Choose which post types should receive the default author
5. Enable/disable features using the toggle switches
6. Save your settings – that’s it!

By selecting a default author, you ensure that every new post (of the selected types) created on your site will automatically be attributed to that specific author. This feature is particularly useful for multi-author blogs or websites where different users contribute content regularly.

= 🔧 Feature Toggles =

* **Enable Default Author** - Turn the default author feature on/off without deactivating the plugin
* **Only force for users who can edit others’ posts** - Recommended. Prevents the permission error that Authors encounter when the plugin tries to re-assign their new posts. Admins and Editors still receive the forced default author.
* **Sync Revision Authors** - Keep revision authors matching the parent post author

= 📦 Bulk Update Existing Posts =

Need to change the author of existing posts? The plugin includes a powerful bulk update feature that:

* Processes posts in real AJAX batches to prevent timeouts
* Shows accurate real-time progress
* Supports dry-run mode so you can preview changes
* Lets you filter by post status
* Provides detailed completion messages
* Works with any number of posts

= 🖥️ WP-CLI Commands =

```
wp dpap set-author --author=5
wp dpap set-author --author=5 --post-types=post,page
wp dpap bulk-update
wp dpap bulk-update --dry-run
wp dpap bulk-update --author=5 --post-types=post,page --status=publish
```

= 🔒 Security =

* Proper WordPress nonces for all operations
* Capability checks for admin functions
* Sanitized inputs and escaped outputs
* Follows WordPress coding standards

= 🌐 Compatibility =

* WordPress 6.1 and higher
* PHP 8.0 and higher
* Compatible with multisite installations
* Works with popular themes and plugins

= Contributing & Bug Report =
Bug reports and pull requests are welcome on [GitHub](https://github.com/WordPress-Satkhira-Community/default-post-author).

== Installation ==

= From within WordPress =
1. Visit **Plugins > Add New**
2. Search for **'Default Post Author'**
3. Click **Install Now** and then **Activate**
4. Go to **Settings > Default Post Author**
5. Select your default author, choose post types and configure the toggle options
6. Click **Save Settings** - that’s all!

= Manual Installation =
1. Download the plugin zip file
2. Upload the `default-post-author` folder to the `/wp-content/plugins/` directory
3. Activate the **Default Post Author** plugin through the **Plugins** menu in WordPress
4. Navigate to **Settings > Default Post Author** to configure

= After Activation =
The plugin will automatically redirect you to the settings page after activation, making it easy to get started right away.

== Frequently Asked Questions ==

= Where is the settings page? =

Navigate to **Settings > Default Post Author** in your WordPress admin dashboard.

= Is it compatible with multisite? =

Yes! The plugin is fully compatible with both single sites and WordPress multisite installations.

= Can I disable the default author feature temporarily? =

Yes! There is a toggle switch to enable/disable the default author feature without deactivating the plugin.

= Authors get a permission error when creating posts. How do I fix it? =

In version 2.3.0 a new setting was added: **“Only force for users who can edit others’ posts”**.  
Enable this option (it is enabled by default). Authors will keep their own authorship while Admins and Editors still receive the forced default author. This completely solves the permission error.

= How does the bulk update feature work? =

The bulk update feature processes posts in real AJAX batches of 50 to prevent server timeouts. It shows an accurate progress bar and notifies you when complete. Only posts with different authors are updated. You can also run a dry-run first.

= Will this affect my existing posts? =

No, by default the plugin only affects new posts. To update existing posts, you must explicitly use the **Update Posts** feature on the settings page.

= What happens to post revisions? =

If the **Sync Revision Authors** toggle is enabled, revision authors will automatically match their parent post’s author.

= Can I apply the default author to Pages or Custom Post Types? =

Yes! In the settings you can select any public post type that supports the author feature.

= Is there WP-CLI support? =

Yes. See the Description section for available commands.

= How frequently is the plugin updated? =

The plugin is actively maintained and updated regularly to ensure compatibility with the latest versions of WordPress and to address any potential issues.

= Is there customer support available? =

Yes! If you encounter any difficulties or have questions, please visit the [support forum](https://wordpress.org/support/plugin/default-post-author/) or contact us through our website.

= Can I contribute to the plugin? =

Absolutely! We welcome contributions on our [GitHub repository](https://github.com/WordPress-Satkhira-Community/default-post-author).

== Screenshots ==

1. Modern settings page with card-based layout and gradient header
2. Default author selection with styled dropdown
3. Feature toggles with modern switch controls (including the new capability-aware option)
4. Post type selection and bulk update section with progress indicator
5. Success notification toast message
6. Mobile responsive design

== Upgrade Notice ==

= 2.3.0 =
Important update! Fixes the permission error for Author-role users, adds multi post-type support, real batch processing, dry-run mode, WP-CLI and more. Recommended for all users.

= 2.2 =
Major UI update! New modern admin interface with toggle switches, AJAX saving, progress indicators, and improved user experience.

= 2.1 =
Bug fixes and stability improvements.

= 2.0 =
Security and performance updates. New bulk update feature to change all existing posts to the default author.

== Changelog ==

= 2.3.0 - (October 13, 2026) =
* **New:** Capability-aware default author assignment – solves the permission error when users with the Author role create posts
* **New:** Setting “Only force for users who can edit others’ posts” (enabled by default)
* **New:** Multi post-type support – apply the default author to Posts, Pages and any public CPT that supports authors
* **New:** Real AJAX batch processing for bulk updates with accurate progress bar
* **New:** Dry-run mode for bulk updates
* **New:** Post status filter for bulk updates
* **New:** WP-CLI commands (`wp dpap set-author` and `wp dpap bulk-update`)
* **New:** Developer filters & actions (`dpap_enabled_post_types`, `dpap_before_set_author`, `dpap_after_set_author`, `dpap_eligible_users`, `dpap_bulk_update_args`)
* **Improved:** Soft migration of options for existing installations
* **Improved:** Better error handling and user feedback
* **Improved:** Updated readme and documentation

= 2.2 - (December 04, 2025) =
* **New:** Completely redesigned modern admin interface
* **New:** Toggle switches replace checkboxes for better UX
* **New:** AJAX-powered settings saving (no page reload)
* **New:** Toast notifications for instant feedback
* **New:** Progress bar for bulk update operations
* **New:** Enable/disable default author feature toggle
* **New:** Sync revision authors toggle option
* **New:** Animated UI elements and smooth transitions
* **New:** Card-based settings layout
* **New:** Responsive design improvements
* **Improved:** Separated code structure (PHP, CSS, JS)
* **Improved:** Better code organization with admin class
* **Improved:** Enhanced security measures
* **Improved:** Optimized asset loading
* **Improved:** Better error handling

= 2.1 - (August 12, 2025) =
* Bug Fix

= 2.0 - (August 12, 2025) =
* Security Update
* Performance Update
* New: Ability to Update All Existing Posts to Default Author

= 1.1.5 - (January 28, 2025) =
* Security Update
* Performance Update
* TasteWP Live Demo Added

= 1.1.4 - (November 17, 2024) =
* Security Update
* Performance Update
* WordPress 6.7 compatibility

= 1.1.3 - (July 17, 2024) =
* Security Update
* Performance Update
* WordPress 6.6 compatibility

= 1.1.2 - (April 15, 2024) =
* Security Update

= 1.1.1 - (April 15, 2024) =
* Bug Fix
* Performance Update
* Security Update

= 1.1 - (March 23, 2024) =
* Performance Update

= 1.0.2 - (February 17, 2024) =
* Performance Update

= 1.0.1 - (February 17, 2024) =
* Simple Bug Fix

= 1.0 =
* Initial release
