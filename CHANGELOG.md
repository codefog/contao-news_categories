news_categories Changelog
=========================

Version 2.8.1 stable (2017-03-06)
---------------------------------

### Fixed
- Fixed news feed being created in wrong folder for Contao 4 (#99)


Version 2.8.0 stable (2017-01-27)
---------------------------------

### Improved
- Allow to hide category in the news list module
- Allow to hide category in the news reader module
- Allow to exclude the news of category in the related list module


Version 2.7.5 stable (2016-10-20)
---------------------------------

### Fixed
- Extension is now compatible with Contao 4.2 (#95)


Version 2.7.4 stable (2016-08-19)
---------------------------------

### Fixed
- Do not set reset link as active if any active categories are selected (#93)


Version 2.7.3 stable (2016-08-09)
---------------------------------

### Fixed
- Made the news menu module compatible with Contao 3.5.3 and above (#92)


Version 2.7.2 stable (2016-04-30)
---------------------------------

### Fixed
- Fixed various news feed issues (#86)


Version 2.7.1 stable (2016-04-27)
---------------------------------

### Fixed
- Restored the PHP 5.3 compatibility (#85)


Version 2.7.0 stable (2016-01-11)
---------------------------------

### Improved
- Updated the composer.json file (see #63)
- Updated the French language (thanks to Lionel Maccaud, see #70)
- Added the "news_trail" class to category list items which the active news item belongs to (see #64)
- Added the "news_categories" insert tag that displays information about active news category (see #66)
- Allowed to choose the custom template for news categories module (see #77)

### Fixed
- Cleaned up the $GLOBALS array in newslist module (see #57)
- Removed the undefined variables usage in the NewsModel class (see #76)


Version 2.6.1 stable (2015-06-24)
---------------------------------

### Fixed
- Fixed the composer.json file


Version 2.6.0 stable (2015-06-10)
---------------------------------

### Improved
- The extension is now compatible with Contao 3.5

### Fixed
- Fixed the German labels (see #58) 


Version 2.5.0 stable (2015-04-10)
---------------------------------

### Improved
- Added the related categories mode to the newslist module


Version 2.4.0 stable (2015-04-03)
---------------------------------

### Improved
- The category URL param is now translatable


Version 2.3.0 stable (2015-03-20)
---------------------------------

### Improved
- The categories in news template are now links (see #52)
- Added the CSS class field for each category
- Added the reference category field to the categories list front end module
- Added new category data in the news templates. The category jumpTo page is now inherited from parent records
- The categories in news template are now sorted alphabetically


Version 2.2.2 stable (2015-02-20)
---------------------------------

### Fixed
- Fixed the class collision error in news menu module


Version 2.2.1 stable (2015-01-23)
---------------------------------

### Fixed
- Updated the readme file


Version 2.2.0 stable (2015-01-23)
---------------------------------

### Improved
- Made the news menu filterable by categories (see #46)
- Added the composer.json file (see #34)
- Added the categories to news feed (see #47)
- Added the news filter content element (see #51)
- Added the dc_multlingual to composer.json file (see #50)


Version 2.1.2 stable (2014-11-03)
---------------------------------

### Fixed
- Fixed a wrong fallback template name


Version 2.1.1 stable (2014-09-01)
---------------------------------

### Fixed
- Drop the reference to old CSS file (see #33)


Version 2.1.0 stable (2014-08-20)
---------------------------------

### Improved
- Limit categories to news archives (see #30)

### Fixed
- Add the "active" class to reset link (see #31)


Version 2.0.2 stable (2014-07-19)
---------------------------------

### Fixed
- Fixed the tl_news palette replacement (see #27)


Version 2.0.1 stable (2014-07-03)
---------------------------------

### Fixed
- Fixed the indentation (see #26)


Version 2.0.0 stable (2014-06-26)
---------------------------------

### Improved
- Updated the French translation (see #15)
- Dropped the backend.css file
- Added support for subcategories (see #19)
- Added feature to display the number of news items per each category (see #22)
- Added the multilingual titles (see #16)
- Format the code according to PSR-2
- Allow to set the default news categories for user and user group (see #25)

### Fixed
- Fixed preserving the custom news order (see #24)


Version 1.2.3 stable (2014-04-10)
---------------------------------

### Fixed
- Fixed the news archive menu module (see #21)


Version 1.2.2 stable (2014-04-10)
---------------------------------

### Fixed
- Fixed the news archive menu module (see #21)


Version 1.2.1 stable (2013-11-30)
---------------------------------

### Improved
- Updated the .htaccess file (see #12)
- Improved the language labels a little
- Updated the readme file and copyrights
- Updated the autoload.ini file


Version 1.2.0 stable (2013-10-21)
---------------------------------

### Improved
- Added the possiblity to preserve the default filters (see #6)
- Added a unique class per each category in front end
- Added a separate category title for front end (see #8)
- Added the categories to news archive and news menu modules (see #5)
- Added the permissions to edit news categories (see #10)
- Added the "news" module to the autoload.ini
- Added the custom categories feature to the navigation module (see #9)


Version 1.1.0 stable (2013-09-16)
---------------------------------

### Improved
- Added the Italian translation (thanks to Marco Damian)
- Added the categories to RSS feeds (see #4)
- Added the categories to news_ templates
- Added the autoload.ini file
- Updated the readme file
- Updated the copyright information


Version 1.0.9 stable (2013-08-10)
---------------------------------

### Improved
- Now whole data of each category is passed to the template


Version 1.0.8 stable (2013-06-15)
---------------------------------

### Improved
- Added a categories cache


Version 1.0.7 stable (2013-05-23)
---------------------------------

### Improved
- It is now possilbe to filter news by category in the back end


Version 1.0.6 stable (2013-03-26)
---------------------------------

### Improved
- Changed the default filter to checkbox list


Version 1.0.5 stable (2013-03-26)
---------------------------------

### Improved
- Added the default filter feature


Version 1.0.4 stable (2013-02-20)
---------------------------------

### Improved
- Added the XHTML template


Version 1.0.3 stable (2013-02-19)
---------------------------------

### Fixed
- Added the missing icon


Version 1.0.2 stable (2013-02-18)
---------------------------------

### Improved
- Added the French translation (thanks to Lionel Maccaud)

### Fixed
- Added the missing labels
- Fixed the incompatibility with Contao 3.0.3 and above (see #2)


Version 1.0.1 stable (2013-02-12)
---------------------------------

### Fixed
- Added the missing templates


Version 1.0.0 stable (2013-02-12)
---------------------------------

Initial release.
