ACF Wizard
==========

Build a First-Run-Wizard with ACF.

The **'Wizard Step** field organizes Your fields in Steps to pace through – Just like Tabs, just as a Wizard. 

Use conditional logic to prevent a step from being navigatable.

Use the **Wizard Proceed** field to navigate through the steps.

The **User Dashboard** location replaces the default WordPress Welcome screen.  
By default the Welcome screen is not dismissible any more. Making make it dismissible again is a one-liner:

```php
add_filter( 'acf_wizard_welcome_dismissable', '__return_true' );
```

Installation
------------

### Development
 - cd into your plugin directory
 - $ `git clone git@github.com:mcguffin/acf-wizard.git`
 - $ `cd acf-wizard`
 - $ `npm install && npm run dev`
 - Have fun coding

Current Status
--------------
This plugin is currently pre-alpha. There are still a few things to do, so please refrain from raising issues at the moment.
