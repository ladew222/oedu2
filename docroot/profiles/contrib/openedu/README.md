This is a Composer-based installer for the [OpenEDU](https://www.drupal.org/project/openedu) Drupal distribution. 

## Get Started
```
composer create-project imagex/openedu-project MY_PROJECT
```
Composer will create a new directory called MY_PROJECT containing a ```docroot``` 
directory with a full OpenEDU code base. Once your webserver is pointing to the ```docroot``` folder, you can then 
install it using either ```drush si``` or 
via your web browser like any other drupal installation.

## Post Installation

#### Google Maps
You must provide a valid maps api key to utilize the google mapping features used within OpenEDU. You can find 
the configuration at `/admin/config/services/gmap-field-settings`

#### IXM Dashboard
The IXM Dashboard is enabled on install, but has a few requirements to be functional after installation of OpenEDU.

- **Analytics**
  - You will need to supply your Google Analytics tracking code at `/admin/config/system/google-analytics`
  - To get on-page reporting, you will need to allow the reporting API access, follow the directions at `/admin/config/services/google-analytics-reports-api`

- **SEO**
  - SEO Data will populate once the "Focus Keyword" has been entered on content.

## PHP Performance
The sample content contained in the OpenEDU distribution (and enabled by default) is quite large, you may 
need to raise your PHP ```memory_limit``` setting to >= 192MB and potentially your ```max_execution_time``` to >= 60. 
Once installed, you are safe to restore these to their initial values.

## Helpful Tips
- The ```docroot``` folder represents the web root for your site (the folder your web server points to).
- Some helpful tools can be found in the ```bin``` folder.
- Composer commands are always run from the site root.
- Downloading additional modules: ```composer require "drupal/devel:1.x-dev"```
- Updating an existing module: ```composer update drupal/devel -–with-dependencies```

## Version Control
The provided ```.gitignore``` in the root contains all directories expected to be installed using composer.

When you first install your project, Composer will create a file called ```composer.lock``` that keeps track 
of your dependencies and which version is installed. 

**You want to Commit ```composer.lock``` !** This will ensure that anyone collaborating on the project will also 
install the same versions when running ```composer install```
