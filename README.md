# Proxy Plugin

**This README.md file should be modified to describe the features, installation, configuration, and general usage of the plugin.**

The **Proxy** Plugin is an extension for [Grav CMS](http://github.com/getgrav/grav). Proxies other webpages, circumventing CORS restrictions.

## Installation

Installing the Proxy plugin can be done in one of three ways: The GPM (Grav Package Manager) installation method lets you quickly install the plugin with a simple terminal command, the manual method lets you do so via a zip file, and the admin method lets you do so via the Admin Plugin.

### GPM Installation (Preferred)

To install the plugin via the [GPM](http://learn.getgrav.org/advanced/grav-gpm), through your system's terminal (also called the command line), navigate to the root of your Grav-installation, and enter:

    bin/gpm install proxy

This will install the Proxy plugin into your `/user/plugins`-directory within Grav. Its files can be found under `/your/site/grav/user/plugins/proxy`.

### Manual Installation

To install the plugin manually, download the zip-version of this repository and unzip it under `/your/site/grav/user/plugins`. Then rename the folder to `proxy`. You can find these files on [GitHub](https://github.com/nd-net/grav-plugin-proxy) or via [GetGrav.org](http://getgrav.org/downloads/plugins#extras).

You should now have all the plugin files under

    /your/site/grav/user/plugins/proxy
	
### Admin Plugin

If you use the Admin Plugin, you can install the plugin directly by browsing the `Plugins`-menu and clicking on the `Add` button.

## Configuration

Before configuring this plugin, you should copy the `user/plugins/proxy/proxy.yaml` to `user/config/plugins/proxy.yaml` and only edit that copy.

Here is the default configuration and an explanation of available options:

```yaml
enabled: true
```

Note that if you use the Admin Plugin, a file with your configuration named proxy.yaml will be saved in the `user/config/plugins/`-folder once the configuration is saved in the Admin.

## Usage

Add a page configuration `proxy` and specify the URL to proxy. The plugin makes this work similar to the `redirect` configuration, but it takes into account the HTTP method being used.

Here is an example configuration:

```yaml
proxy: http://google.com
```

## Credits

This plugin is based on the AJAX Cross Domain (PHP) Proxy by Iacovos Constantinou (https://github.com/softius). Thanks!

## To Do

- [ ] Make the CURL configurable, if necessary

