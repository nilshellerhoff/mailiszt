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

## Usage

- Members (<domainname>/members): add members with their email-address 
- Groups (<domainname>/groups): create groups and add your members to them
- Mailboxes (<domainname/mailboxes): add at least one mailbox in order to build a list. You only need the IMAP and SMTP credentials, so virtually any email-service can be used!
  - customize who is allowed to send mails to the list: 
    - "Everybody": every mail is accepted and forwarded to the list
    - "Registered members": only mails coming from email addresses of members registered under <domainname>/members are accepted, regardless of which groups they belong to
    - "Members of the list": only mails coming from email addresses belonging to members who are recipients of the list emails are accepted
    - "Specific people": you can customize which members are allowed to send emails to the group
  - customize reply to address:
    - "Sender": the Reply-To header is set to the original sender of the email.
    - "Malinglist": no Reply-To header is set. This means that when recipients click "reply", they will reply to the entire list
    - "Sender + Malinglist": the Reply-To header is set to the original sender and the email-address of the list. If the original sender is member of the list already, only the email-address of the list is added, so that the original senders does not get replies twice
  - Server: pretty self explanatory, enter your IMAP and SMTP details and your credentials here. 
    - :warning: Only SSL is supported for IMAP and only STARTTLS is supported for SMTP right now. This will be changed soon
  - Groups
    - simple selector: select which groups receive emails sent to the list address
    - logic selector: build a logic condition tree where you can include or exclude members based on their group memberships
    - use the "Show recipients" button to check whether you got the group selection correctly!
- Mails (<domainname>/mails): a list of all the mails processed by Mailiszt. You can view the mails (albeit without attachments for now) and see in detail, to which mail addresses they were forwarded.  
   

## What is missing
Lots of stuff, including:
- proper validation of inputs
- multi user functionality with customizable access levels
- allow member self-registration
