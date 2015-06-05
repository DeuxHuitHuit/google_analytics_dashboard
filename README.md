# Google Analytics Dashboard

Version: 1.0.x

> Google Analytics dashboard for Symphony CMS.

### REQUIREMENTS ###

- Symphony CMS version 2.5.1 and up (as of the day of the last release of this extension)
- [Dashboard extension](https://github.com/symphonists/dashboard) 2.0.x

### INSTALLATION ###

- `git clone` / download and unpack the tarball file
- Put into the extension directory
- Enable/install just like any other extension

You can also install it using the [extension downloader](http://symphonyextensions.com/extensions/extension_downloader/).
Just search for `google_analytics_dashboard`.

For more information, see <http://getsymphony.com/learn/tasks/view/install-an-extension/>

### HOW TO USE ###

- Be sure to have the [dashboard extension](https://github.com/symphonists/dashboard) installed.
- Go on the [Google API Console](https://console.developers.google.com/) and create a [Oauth Service account](https://developers.google.com/api-client-library/php/guide/aaa_oauth2_web).
- Generate and download a p12 key file.
- Go on Google Analytics and add the read right to the Service Account email. (xxx@developer.gserviceaccount.com)
- Upload the p12 file in a secure place on your server. Make sure nobody can download this file.
Symphony's `manifest` folder is a good place for this kind of file.
- Go to the dashboard page and add some Google Analytics Panels.
- Protip: Save the configuration as default the first time to save you some time.

### LICENSE ###

[MIT](http://deuxhuithuit.mit-license.org)

Made with love in Montréal by [Deux Huit Huit](https://deuxhuithuit.com)

Copyright (c) 2015
