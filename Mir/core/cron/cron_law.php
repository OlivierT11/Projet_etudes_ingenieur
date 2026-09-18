<?php

//require 
// 1) cron.yaml sends a http request to this file
// 2) handle the request by checking the existence of the header X-AppEngine-Cron. If non existent send back an error
// 3) then execute the cron functions inside the http request handler

// How to handle cron job request  ? $_POST ?
//??? https://stackoverflow.com/questions/13023959/doing-an-http-post-from-php-to-an-http-handler

// cron.yaml sends a HTTP request. catch it w/ $_REQUEST[]
// TODO : test on app engine and display the REQUEST array here
//var_dump($_REQUEST[]);

// DOCS

//Description of an HTTP request
// https://gist.github.com/nicolas-grekas/1028251
// Before sending a request, the server has access to basic information about the client: IP address and possibly SSL context. This phase takes place before the launch of PHP and is passed to it via environment variables ($_SERVER['REMOTE_ADDR'] and $_SERVER['SSL_*'] eg.).
// The request itself, in its primary structure, contains three subsections:

//     The first line contains the HTTP method (GET, POST, etc..), the URL and the used protocol version (HTTP/1.1). This line is the only one the web server may need to decide on handing over to PHP. This information is available via $_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'] and $_SERVER['SERVER_PROTOCOL'] respectively.
//     The following lines, as they are not empty, make a list of keys and values: the headers.
//     The blank line that ends the headers is followed by the body of the request, whose content should be interpreted according to the Content-Type header. The body of the request is typically empty for GET requests and containing the values of form fields for POST requests.

// Safety
// Validating cron requests
// https://cloud.google.com/appengine/docs/flexible/nodejs/scheduling-jobs-with-cron-yaml 
// You might want to validate that requests to your cron URLs are coming from App Engine and not from another source. You can do so by validating an HTTP header and the source IP address for the request:
//     Requests from the Cron Service will contain the following HTTP header:
// X-Appengine-Cron: true

// safety 2
// https://partner-security.withgoogle.com/docs/appengine_tips.html 
// Cron handlers run certain tasks regularly. They are defined in cron.yaml (Python) or cron.xml (Java) files and must be restricted to authenticated admins. Otherwise, arbitrary users could execute the cron handlers. For examples on how to restrict cron handlers, refer to the Authorization section.

// Restrict cron handlers to admin users.

// Requests from the App Engine environment to cron handlers contain this HTTP header: X-AppEngine-Cron: true

// App Engine strips this header from user requests unless the user is an administrator of the application. All cron handlers should check for the presence of this header to mitigate against cross-site request forgery attacks against the application administrators, which would allow an attacker to execute cron tasks.

// Cron handlers must verify the presence of the X-AppEngine-Cron HTTP header.

// The following code snippet shows how to do this in Java:

//   // code snippet inside the get/post handler
//   if (request.getHeader("X-AppEngine-Cron") == null) 123
//     throw new IllegalStateException("attempt to access cron handler directly, " +
//                                     "missing custom App Engine header");
//   }

// The following code snippet shows how to do this in Python (assumes a framework like WebOb or webapp2):

//   # code snippet inside the get/post handler
//   header = request.headers.get('X-AppEngine-Cron', None)
//   if not header:
//     raise ValueError('attempt to access cron handler directly, '
//                      'missing custom App Engine header')


// safety 3
// Securing URLs for cron
// https://cloud.google.com/appengine/docs/standard/php/config/cron

// A cron handler is just a normal handler defined in app.yaml. You can prevent users from accessing URLs used by scheduled tasks by restricting access to administrator accounts. Scheduled tasks can access admin-only URLs. You can restrict a URL by adding login: admin to the handler configuration in app.yaml.

// An example might look like this in app.yaml:

// runtime: php55
// api_version: 1

// handlers:
// - url: /report/weekly
//   script: weekly.php
//   login: admin

// Note: While cron jobs can use URL paths restricted with login: admin, they cannot use URL paths restricted with login: required because cron scheduled tasks are not run as any user. The admin restriction is satisfied by the inclusion of the X-Appengine-Cron header described below.
// For more information see how to require login or admin status in the app.yaml reference.

// To test a cron job, sign in as an administrator and visit the URL of the handler in your browser.

// Requests from the Cron Service will also contain a HTTP header:

// X-Appengine-Cron: true

// The X-Appengine-Cron header is set internally by Google App Engine. If your request handler finds this header it can trust that the request is a cron request. If the header is present in an external user request to your app, it is stripped, except for requests from logged in administrators of the application, who are allowed to set the header for testing purposes.

// Google App Engine issues Cron requests from the IP address 0.1.0.1.

// safety 4
// https://stackoverflow.com/questions/14193816/google-app-engine-security-of-cron-jobs 
// In addition to what Paul C said you could create a decorator that checks the X-Appengine-Cron header as illustrated below. Btw, the header can't be spoofed, meaning that if a request that hasn't originated from a cron job has this header, App Engine will change the header's name. You could also write a similar method for tasks, checking X-AppEngine-TaskName in this case.
// """
// Decorator to indicate that this is a cron method and applies request.headers check
// """
// def cron_method(handler):
//     def check_if_cron(self, *args, **kwargs):
//         if self.request.headers.get('X-AppEngine-Cron') is None:
//             self.error(403)
//         else:
//             return handler(self, *args, **kwargs)
//     return check_if_cron

