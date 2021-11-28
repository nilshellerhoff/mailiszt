# Mailiszt
Simple Mailing List Manager written in PHP and Vue designed to be as lightweight as possible (only requirements are PHP 7.4+ with SQLite module). You can organize members into groups and organize these groups into mailing lists based on logical conditions. 

!!! very early development stage, bugs and missing features are present!

## Building

Clone the repository
```
git clone https://github.com/nilshellerhoff/mailiszt
```

Install composer and php dependencies
```
cd mailiszt
php -r "readfile('https://getcomposer.org/installer');" | php
php composer.phar update
```

Run the backend developer server (port can be adjusted in `frontend/.env.development`)
```
php -S 0.0.0.0:8081 index.php
```

Install node dependencies
```
cd frontend
npm i
```

Run the frontend development server
```
npm run serve
```

## Installing

Build the frontend
```
cd frontend 
npm run build
```

Copy the base directory to your server (everything in `frontend` except the `dist`-folder can be ignored)

Point a cron job to the following URL in order to forward emails for all registered mailboxes:
```
<url of server>/api/mailbox/forward
```
or if you only want to forward certain mailboxes
```
<url of server>/api/mailbox/<id of mailbox>/forward
```

## What is missing
- proper validation of inputs
- multi user functionality with customizable access levels
- allow member self-registration