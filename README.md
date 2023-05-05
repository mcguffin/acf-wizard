ACF Wizard
==========

Build a First-Run-Wizard with ACF.

The **'Wizard Step** field organizes Your fields in Steps to pace through – Just like Tabs, just as a Wizard. 

Use conditional logic to prevent a step from being navigatable.

Use the **Wizard Proceed** field to navigate through the steps.

The **User Dashboard** location replaces the default WordPress Welcome screen.  
By default the Welcome screen is not dismissible any more. Making make it dismissible again is a one-liner:

```php
add_filter( 'acf_wizard/welcome_dismissable', '__return_true' );
```

Plugin API
----------

### Filter hooks

#### `apply_filters( 'acf_wizard/welcome_panel_form_post_id', int|string $post_id)`

##### Parameters
`$post_id` *(string|int)* ACF Post ID to be passed to `get_field()`. Default: `welcome_panel`

#### `apply_filters( 'acf_wizard/welcome_panel_capability', string $capability )`

Filters the capability required to show the form on the welcome screen.
Additionally WordPress requires the `edit_theme_options` capability
to show the welcome screen, regardless of what your filter hook returns.

##### Parameters
`$capability` *(string)* Capability. Default `edit_theme_options`

#### `apply_filters( 'acf_wizard/welcome_dismissable', boolean $dismissible )`

Filters whether the user is allowed to dismiss the ACF welcome screen.

##### Parameters
`$dismissible` *(booleaen)* Default `false`

#### `apply_filters( 'acf_wizard/print_welcome_panel_submit', boolean $print )`

Filters whether to print the submit section in the welcome panel

##### Parameters
`$promt` *(booleaen)* Default `true`

### Action hooks

#### `do_action( 'acf_wizard/welcome_panel_before_fields' )`

Fired before acf fields are being rendered

#### `do_action( 'acf_wizard/welcome_panel_after_fields' )`

Fired after acf fields have been rendered

Installation
------------

### Production (using Github Updater – recommended for Multisite)
 - Head over to [releases](../../releases)
 - Download 'acf-wizard.zip'
 - Upload and activate it like any other WordPress plugin
 - AutoUpdate will run as long as the plugin is active

### Development
 - cd into your plugin directory
 - $ `git clone git@github.com:mcguffin/acf-wizard.git`
 - $ `cd acf-wizard`
 - $ `npm install && npm run dev`
 - Have fun coding
