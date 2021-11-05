# Mailiszt
Simple Mailing List Manager written in PHP and Vue. Includes group management

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