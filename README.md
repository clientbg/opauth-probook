Opauth-Probook
=============
Opauth strategy for Probook.bg authentication.

Based on Opauth's Facebook Oauth2 Strategy

Getting started
----------------
0. Make sure your cake installation supports UTF8

1. Install Opauth-Probook:
   ```bash
   cd path_to_opauth/Strategy
   git clone git://github.com/clientbg/opauth-probook.git Probook
   ```
2. Require credentials by sending a request to admin@probook.bg

3. Configure Opauth-Probook strategy with `client_id` and `client_secret`.

4. Direct user to `http://path_to_opauth/probook` to authenticate

Strategy configuration
----------------------

Required parameters:

```php
<?php
'Probook' => array(
	'client_id' => 'YOUR APP KEY',
	'client_secret' => 'YOUR APP SECRET'
)
```

License
---------
Opauth-Probook is MIT Licensed  
