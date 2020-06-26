### PZZ API Client
-  Tags: RESTful API
-  Requires at least: 4.2
-  Tested up to: 5.4.2
-  Requires PHP: 5.6

This plugin provides simple RESTful API, developed specifically for Mobile clients that want to connect to your WordPress website.

#### Description
This plugin provides simple RESTful API, developed specifically for Mobile clients that want to connect to your WordPress website. 

For now, we support some specific APIs such as posts, taxonomies, comments. We plan to support  More APIs. Also, this plugin integrated with your Auth method and we can get authenticated user and work with.

#### Installation
Download the latest stable version from [releases](https://github.com/mjavadhpour/pzz-api-client/releases) and upload it into your WordPress site.

#### Frequently Asked Questions
Feel free to open an [Issue](https://github.com/mjavadhpour/pzz-api-client/issues), Also you can track the developing process from [milestones](https://github.com/mjavadhpour/pzz-api-client/milestones).

#### Changelog
-  1.1.5
    * Resolve conflicts with reactor-core.

-  1.1.4
    * Fix WordPress trademarked name with plugin name.

-  1.1.3
    * Update readme and plugin description.

-  1.1.2
    * Use WordPress filters to replace links and fix the functionality of replaced links.

-  1.1.1
    * Fix get all posts API error.

-  1.1.0
    * Replace all links int the post description by  in post API.

-  1.0.0
    * Basic functionality.
    * Post API.
    * Taxonomies.
    * Post comments.

#### Upgrade Notice
If you install plugin manually, please remove the previous version and install the new one.

#### Contribution guide
We always working on `develop` branch on GitHub. The `master` branch was updated with [WordPress SVN](http://plugins.svn.wordpress.org/pzz-api-client/trunk/).

We use [Git Flow](https://nvie.com/posts/a-successful-git-branching-model/) for branching startegy:
-  If you want to [fix a bug](https://github.com/mjavadhpour/pzz-api-client/labels/bug), please create a pull request from/to `master` branch.
-  If you want to [add new feature or anything else](https://github.com/mjavadhpour/pzz-api-client/labels/enhancement) please create a pull request from/to `develop` branch.