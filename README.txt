=== Plugin Name ===
Contributors: bhoot
Donate link: http://bengal-studio.com/
Tags: firebase, firebase-auth, json-web-tokens
Requires at least: 4.7.0
Tested up to: 5.1.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A plugin to work with Firebase tokens.

== Description ==

If your Firebase client app communicates with a custom backend server, you might need to identify the currently signed-in user on that server.

This plugin work with Google Firebase tokens. You can use it to verify ID Tokens.

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload `verify-id-tokens` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Namespace and Endpoints ==

When the plugin is activated, a new namespace is added

`
/verify-id-tokens/v1/
`

Also, a new endpoint is added to this namespace

*/verify-id-tokens/v1/token/validate* | POST

== PHP HTTP Authorization Header enable ==

Most of the shared hosting has disabled the **HTTP Authorization Header** by default.

To enable this option you'll need to edit your **.htaccess** file adding the follow

`
RewriteEngine on
RewriteCond %{HTTP:Authorization} ^(.*)
RewriteRule ^(.*) - [E=HTTP_AUTHORIZATION:%1]
`

== WPENGINE ==

To enable this option you'll need to edit your **.htaccess** file adding the follow

`
SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1
`

== Configurate the Firebase projectId ==

To add the **projectId** edit your wp-config.php file and add a new constant called **BENGAL_STUDIO_VERIFY_ID_TOKENS_FIREBASE_PROJECT_ID**

`
define('BENGAL_STUDIO_VERIFY_ID_TOKENS_FIREBASE_PROJECT_ID', 'projectId');
`

== Configurate CORs ==

The **Verify ID Tokens | Firebase** plugin has the option to activate [CORs](https://en.wikipedia.org/wiki/Cross-origin_resource_sharing) response headers.

To enable the CORs edit your wp-config.php file and add a new constant called **BENGAL_STUDIO_VERIFY_ID_TOKENS_ENABLE_CORS**

`
define('BENGAL_STUDIO_VERIFY_ID_TOKENS_ENABLE_CORS', true);
`

== Retrieve ID tokens on clients ==

To retrieve the ID token from the client, make sure the user is signed in and then get the ID token from the signed-in user:

`
firebase.auth().currentUser.getIdToken(/* forceRefresh */ true).then(function(idToken) {
  // Send token to your backend via HTTPS
  // ...
}).catch(function(error) {
  // Handle error
});
`

== Verify ID Tokens ==

#### verify-id-tokens/v1/token/validate

This is a simple helper endpoint to validate a token; you only will need to make a POST request sending the Authorization header.

== Changelog ==

= 1.0.0 =
* Initial release.
