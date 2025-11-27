# Halo Stat Tracker
simple stat tracker. lets admins CRUD stuff. lets user read stuff. WAMP

## Setup guide for grading:
1. download all of the provided files.
2. install XAMPP if needed. if you are comfortable running on something else go ahead. this is how i did it though, if your method fails do this.
3. put php.ini in xampp\php\php.ini.
4. put httpd in xampp\apache\conf.
5. start apache and mysql in xampp
6. in phpmyadmin, create a database named wampville. if you change the name youll need to change some files i provided. it's not worth it.
7.  open phpmyadmin. use the datadump provided to make wampville.
8.  put the folder halo-stat-tracker in htdocs.
9.  go to localhost. navigate to htdocs, open halo-stat-tracker.
10. all passwords are the same as the username. for simplicity use admin for admin and user for user

once you have all that done the CRUD operations are all pretty obvious
- create a post as an admin
- read posts as user or admin
- read stat screens as user or admin
- update your existing posts as an admin
- delete your existing posts as an admin


the one to many relationship is that one user has many associated matches. each match is owned by one user.
you can click on the username next to each match to see a stats screen for them specifically.
