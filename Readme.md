TwoBus Phone
============
TwoBus Phone is a clone of the 
[original One Bus Away phone interface](https://web.archive.org/web/20100722212516/http://www.onebusaway.org/p/Tools_Phone.action)
written in PHP for use with [Twilio](http://www.twillio.com/).  This project 
uses Twilio to create an 
[IVR](http://en.wikipedia.org/wiki/Interactive voice response) for announcing 
arrival times at a transit stop.  Users can access arrival times by inputting 
a stop ID or deriving the ID by selecting from a list of stops for a given 
route. Data comes from the 
[One Bus Away API](http://www.onebusaway.org/p/OneBusAwayApiService.action) 
Each region requires its own phone number.  A fundamental difference between 
this and the original implementation is that it does not use proximity-based 
searching; instead the user is prompted to select an agency when more than one 
exists for the given route or stop number.

Main Phone Tree
----------
 # | Action
---|-------------------------------------------
1  | Search by stop number
2  | Static text for help finding a stop number
3  | Access bookmarked stops
4  | Manage bookmarks (not yet implemented)
5  | Arrivals for most recently requested stop
6  | Search arrivals by route
7  | Placeholder for manage preferences
8  | Vehicle activity

Demo
----
A live demo is running at `(312) 250-2BUS` until my server goes down or I run 
out of credit in my Twilio Account.

Requirements and Compatibility
------------------------------
PHP 5.4 or greater is required due to the use of 
([http://php.net/manual/en/migration54.new-features.php](short array syntax and array deferenencing)).
This project was developed on Windows with Apache but was also tested on Ubuntu
with Apache.  There are no specific dependencies for Apache, and since PHP 5.4
is required you could probably use 
[http://www.php.net/manual/en/features.commandline.webserver.php](PHP's built-in webserver).

Setup
-----
1. Clone or wget the project
2. Copy `configuration-sample.php` to `configuration.php` in the `includes` 
   folder and make the necessary changes.
3. Set the DB_IMPL constant to `database-mysqli` or copy the 
   `database-sample.php` file in the `includes` folder to interface with 
   another DBMS.  If you leave it as `database-sample` everything will work 
   except bookmarks and most recent stop.
4. Optional: Make edits to config files (see next section)
5. Make sure the `cache` directory is writable by your webserver
6. Make sure your project is world-accessible.  [http://ngrok.com/](ngrok) is
   great if you're working on your local machine or behind a firewall
7. Sign up for a Twilio account and point your phone number to `main.php` in 
   the root folder, for example: http://www.example.com/twobus-phone/main.php

Config Files
------------
There are three files in the `config` directory.  Each is optional but will 
provide some modifications to help cope with the irregularities of the One Bus
Away API and Twilio's Text-to-Speech engine.  Each of these files are 
well-documented but are briefly explained here:
###agency_modifications.xml
This file is read if the `agencies.xml` file does not exist in the `cache`
directory.  The One Bus Away API will likely return agencies in an order that
is different from their popularity.  In this file, you set their sort order.
Additionally, you can set a different name for each agency than what is provided
in the agency's GTFS so the phone system can simply say "MARTA" instead of 
"Metropolitan Atlanta Rapid Transit Authority".  If certain agencies (such as 
those that are private) should not be used when searching by stop, route, or
vehicle search, options exist to exclude them on an agency-by-agency and 
case-by-case basis, or ignore the agency alltogether.
###headsign_pronunciations.xml
Replacement strings for headsigns returned by the One Bus Away API for trip 
headsigns, such as changing the text "SR-520" to "S R 5 20" so the Twilio TTS
engine says "S R Five Twenty" (as locals know it) instead of "Senior Five 
Hundred Twenty"
###stop_pronunciations.xml
Same as `headsign_pronunciations.xml` but for stop names.

Other Notes
-----------
It is not possible to search by non-numeric stop or route numbers due to the 
limitations of touch tone phones and the complexity (to the end user) of doing 
so.  While it would be possible to allow text input, it would be necessary to 
prompt each user which entry mode they would want to use (text or numeric) 
before prompting for input.  Should this be deployed in a region that has a 
significant amount of non-numeric stop or route numbers, it is suggested that 
a web interface be developed for users to create bookmarks prior to calling in.

ToDo
----
* Implement an object cache for DB calls and file reads
* Sort all agency prompts by the order defined in `agency_modifications.xml`
* Interface for users to manage their bookmarks


Copyright and License
---------------------
Copyright (c) 2014 [http://github.com/cookieguru](Cookie Guru)

This project is licensed under the The MIT License.

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is 
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.