# SeeCMSv3
SeeCMS Content Management System

## Release notes

### v3.474

ADF:

- Update to allow ADFs to use jQuery's UI sortable functionality

- Fix permissions appearance on folder tree within posts

- Fixed Website User ADF's - broken on website user edit page

- Fixed bug with in page ADFs not displaying when the first ADF in the
ADF table isn't on the page you're editing

- Fixed problem with DOMdocument incorrectly assuming ISO-8859-1 content
<br/>
- Fixed problem with save button not appearing on media, downloads and posts


Download:

- Fix to replacing download not working
<br />
- Added modified field to downloads to keep track of when downloads are replaced


HTML Forms:

- Added all new HTML5 Inputs -<br />Including: <br/> Color<br/> Date<br/> dateTimeLocal<br/> Time<br/> Telephone<br/>  reset<br/>
- Added option group compatability with SeeCMS Forms select method

- Added support for reloading form fields for mult-part/form-data


Improvement:

- Speed improvement - change admin auth session from runnning multiple times per page

- Added date support for negative timestamps

- Added ability to disable CORS ( Cross Origin Resource Sharing ) and turned off by default

- Added styling to plugin navigation


Permissions:

- Allow Permissions to be set on protected pages


Styling:

- Added general styling button within the CMS

- Tweaked in page Routes display


Website Users:

- Added Logindate showing when users have logged in

- Added creationdate and modifieddate to self update method



### v3.463

Posts:

- Restored missing icon in posts

RTE (Rich Text Editor):

- Fixed iPad issues with editing text and adding images



### v3.462

Downloads:

- Restored missing icon to move downloads



### v3.461

Pages:

- Fixed bug stopping creation/moving of pages at top level

RTE Content:

- Fixed bug caused by old version of libxml on some servers stopping content saving



### v3.46

Admin users:

- Allow admin permission setting to be json encoded instead of serialising
- Added beta version of advanced editing permissions

Images:

- Added option to allow a colour overlay to be automatically added to images

Misc/CMS Styling:

- Updated CMS theme to use font icons
- Added AI and PSD icons to the CMS for download files
- Added new edit page sidebar option to allow edit points to be hidden on perview

Sessions:

- Added session manager

Template manager:

- Fixed functionality regression

Website users:

- Fixed bug in user activation link
- Added option to allow setting a default order of users in CMS



### v3.451

Update tool:

- Updated to allow additional directory creation
This update requires the update script to run again to continue after this update. Please check for further updates after installing this one.


### v3.45

ADFs:

- Fixed problem with ADF link selector for in page ADFs


Posts:

- Added option to duplicate a post
- Fixed problem with & in RSS feed



### v3.44

ADFs:

- Fixed problem with ADF content loading from a page when the viewpart is in a post
- Allowed ADF data on websiteUsers
- Allowed ADFs to be loaded with a post field
- Various improvements to in page ADFs
- Added option to loadADFContent to load from an ascendant at a specific level, setting ascendant = 1 in config with load the ADF content from the top level page the current page belongs to
- Fixed bug with speech marks in text content causing field to show blank

CSV:

- Added new core CSV controller, to create and read CSV files

Downloads:

- Added the ability to add categories in the database for downloads
- Fixed bug with inline files which affected files which couldn’t open inline
- Added the download URL to the download edit screen
- Allow download folder list to open and close folders/sub folders
- Updated downloads screen in the CMS to show folder name in heading bar
- Improved speed of loading the list in the CMS

Forms:

- Added new option to built-in sendByEmail method to allow styling of the table:
$formSettings['controller']['settings']['style']['th'] = "text-align: left; background: #905500; color: #ffffff; padding: 4px;";
$formSettings['controller']['settings']['style']['td'] = "text-align: left; background: #F4A640; color: #000000; padding: 4px;";
- Added new option to exclude fields from the email: 
$formSettings['controller']['settings']['excludeField']['address3'] = true;

Helpers: 

- Updated to include post titles in breadcrumb

Media:

- Added extra options to image size settings for classes and duping
- Added options to allow manual cropping of images from within the CMS
- Added option to allow replacement of a media file with a new one, this replaces all occurrences throughout the website
- Updated media screen in the CMS to show folder name in heading bar
- Allowed download folder list to open and close folders/sub folders
- Added ‘friendlyImageURLs’ setting, on by default in new installations. This changes the standard images paths: ie /img-4-4.jpg, to /4/4/imageNameHere.jpg

Misc/CMS Styling:

- Fixed the Core/Cache/Save method so it stops the system trying to cache files with php GET parameters
- Added AI and PSD icons to the CMS for download files
- Tweaked padding on folder list
- Added a new button to the header that allows the user to go straight to their live website
- Fixed the issue with posts and media folder names going behind the icons and also fixed the height styling problems
- Added an update alert to the header of the CMS to alert users that there is a new version ready to download
- Changed layout and style of the CMS login screen
- Removed the hardcoded ‘Folder name’ text
- Added styling to image names on hover so that if it is too long it adds ‘...’ to the end 
- Fixed secure icon not fading properly when moving folders
- Allow download folder list to open and close folders/sub folders
- Updates to allow future API expansion
- Improvements to site slideout toolbar
- Added option for new editbar in preview/edit mode

Multisite:

- Added support for multiple sites within one CMS

Pages:

- Added a new ‘Open live page’ button to the edit page in the CMS which allows users to view individual pages as if they were live on the site
- Added support for cloning pages (this clones the content of one page to another, updating the master page then also updates the clone)
- Added support for ‘onlyShowIfUserHasAccess’ setting on navigation, this will stop links to pages showing if the user doesn’t have access, ie not logged in or is logged in but with permission to view that page
- Added support for plugins to extend the edit page screen
- Improved the move page function to increase speed
- Fixed bug with speech marks in page title causing field to show blank

--

Posts:

- List post categories in alphabetic order in the CMS post edit screen
- Updated to allow setting of a parent page for post types, so you don’t need to have a category if you don’t want
- Updated the archiveList method so it takes into account the post category, and allows setting of showing months for all years or just the current selected year
- Added a new ‘Open live post button to the edit page in the CMS which allows users to view individual posts as if they were live on the site
- Allowed ADFs to be loaded with a post field
- Allowed file uploads in custom post types
- Updated posts screen in the CMS to show folder name in heading bar
- Allowed creation of redirects from posts
- Fix bug with news archive method breaking if there are no posts
- Added option to posts feed method to allow only future events to be shown: ‘futureEventsOnly’
- Updated posts feed to allow a feed where a post has to be in multiple categories
- Fixed bug with speech marks in post title causing field to show blank

--

Routes:

- Allowed routing to plugin methods

--

RTE (Rich Text Editor):

- Updated implementation to allow custom styles, these are defined in the DB setting table: additionalRTEStyles' in format {title: 'Test', block: 'p', attributes :
{'class': 'yaay'}},{title: 'PRE', block: 'pre' }
- Implemented first release of a pre-save html parser, at the moment it just removes unwanted script tags, more updates to follow.

--

Search:

- Completely rewritten the search method to give better search results and include additional data in results
- Fixed search error caused by ADF content which is on a deleted page
- Added an option to set specific inclusion of pages and post categories for site section searches
- Allowed exclusion list for search results
- Updated to also search meta description and keywords

--

Security:

- Fixed bug which caused AES functionality to break in PHP5.6+ - sites installed prior to SeeCMS 3.44 will require a manual update to work on PHP5.6+

--

Site Users:

- Added a new function to load the current user
- Updated to allow the ‘Activation required’ setting to be passed through the form settings in addition to the database setting, and if both are set the form setting takes precedence
- Added missing option to toggle website user status, and fixed auto add option on user groups to off by default
- Added the option to be able to import/export website users from/to a CSV file 
- Updated selfUpdate method to add the functionality
- Automatically notify an admin via email when someone registers
- Allow setting of activation details
- Allowed ADF data on websiteUsers
- Added option to allow configuration of which fields display on the site users list in the CMS

--

Statistics:

- Fixed minor display issue on the browser visits

--

Theme: 

- Fixed problem with SeeCMS 2015 theme installed missing templates
- Fixed minor bug in SeeCMS 2015 theme where the contactform viewpart caused a validation error
