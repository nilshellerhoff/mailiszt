# Mailiszt
Simple Mailing List Manager written in PHP and Vue designed to be as lightweight as possible (only requirements are PHP 8.0+ with SQLite module). You can organize members into groups and organize these groups into mailing lists based on logical conditions. 

# Building and installing

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

The default user is `admin` with password `admin` (change this in the Settings!).

## Deploying

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

# Usage

## Members

Members (`/members`): add members with their email-address. Members can be set to `inactive`, which will mean that they won't receive any mails.

## Groups
Groups (`/groups`): create groups and add your members to them

## Mailboxes

Mailboxes (`/mailboxes`): add at least one mailbox in order to build a list. You only need the IMAP and SMTP credentials, so virtually any email-service can be used!

Settings:

**General**:
  - **Name**: The display name used for the list, both in the interface and when sending mails
  - **Email Address**: The mail address used for the list.
  - **Allowed senders**: decide, who is allowed to send mails to the mailing list
    - "Everybody": every mail is accepted and forwarded to the list
    - "Registered members": only mails coming from email addresses of members registered in the members page are accepted, regardless of which groups they belong to
    - "Members of the list": only mails coming from email addresses belonging to members who are recipients of the list emails are accepted
    - "Specific people": you can customize which members are allowed to send emails to the group in the field to the right
  - **Reply to**: customize, what the reply-to header is set to
    - "Sender": the Reply-To header is set to the original sender of the email. When recipients click "reply", they will only reply to the original sender of the mail
    - "Malinglist": no Reply-To header is set. This means that when recipients click "reply", they will reply to the entire list
    - "Sender + Malinglist": the Reply-To header is set to the original sender and the email-address of the list. If the original sender is member of the list already, only the email-address of the list is added, so that the original senders does not get replies twice
    - if a custom reply-to header is set
      - TODO

**Templates**: templates used when sending mails to the list. Templates are in text format and can use placeholders
  - **Subject header**: The value to set the subject to. Defaults to the original subject
  - **From name header**: The value which is shown as the from name. Defaults to the original sender name plus "via listname"
  - **Email body**: The body of the message. Defaults to adding a footer.
  - **rejection notices**: Whether to send a rejection notice, if the list receives a mail from somebody not allowed to address the list.
  - **rejection notice template**: The template to use for the rejection notice

**Server**: Details on the server connection
  - **receiving mail**:
    - **IMAP server**: URL of the IMAP server to be used
    - **Encryption**: encryption scheme (None, SSL or TLS)
    - **IMAP port**: port to be used
  - **sending mail**:
    - **SMTP server**: URL of the SMTP server
    - **Encryption scheme (None, SSL or TLS)
    - **SMTP port**: port to be used
  - **Username**: username used for logging on to IMAP and SMTP servers. Usually identical to the email address
  - **Password**: password for username
  - **Only consider messages sent to this address**: If this is enabled, Mailiszt will only handle those emails, which have the tomail set to the email-address of the list. Enable this option if e.g. you're using a catch-all mailbox which receives mails for multiple addresses, which you e.g. want to use for different mailing lists.

**Groups**: configure recipients of the list
  - simple selector: select which groups receive emails sent to the list address. Everybody, who is in at least one of the selected groups, receives mails from the mailinglist
  - logic selector: build a logic condition tree where you can include or exclude members based on their group memberships. Different conditions can be combined with `AND` or `OR` statements
  - include inactive members: if enabled, the group selection will include members, even if they are inactive. If not enabled, only active members will receive mails.
  - the "Show recipients" button will show, who would receive mails according to the settings above. Use it to check whether you got the group selection correctly!

### Mails 
Mails (`/mails`): a list of all the mails processed by Mailiszt. You can view the mails and attachments, and see in detail, to which mail addresses they were forwarded.   

### Settings
Configure some settings, including password change, automatic email fetching and direct access to the DB via interface.

### Logs
View the server logs.

## ToDos

- [ ] proper input validation
  - [ ] define validation rules serverside
  - [ ] serve validationrules through api
  - [ ] use validationrules clientside
- [ ] login for members (not only users)
- [ ] allow to compose mails from the interface
  - [ ] allow to define a mail which is to be sent to every new member of a list/group
- [ ] member registration confirmation
  - [ ] when registering members, send them email with confirmation link
- [x] handle bounces correctly 
  - [x] parse bounce messages, and dont make them show up in the interface
  - [x] connect bounce messages to original mail and show that the mail could not be delivered to certain members
- [ ] logging
  - [ ] frontend access to logs
  - [ ] delete logs after a certain time (maybe based on loglevel?)
- [ ] multi user functionality with customizable access levels
- [ ] allow member self-registration
- [ ] for every api action, add a human readable response and show this response in the frontend
- [ ] not only send mail once
  - [ ] but check if it has been sent and no mailer-daemon came back
    - [ ] parsing for Mailer-Daemon and Postmaster mails
  - [ ] if not, resend it x times
  - [ ] keep track of supposed and actual receivers of the mail (in the `mail` table?)
- [x] change X-Mailer header to Mailiszt (maybe reduced SPAM detection)
  - [ ] change other headers if necessary
- [x] introduce Utilities class for functions that are used in multiple places
- [x] send a rejection email if somebody is not allowed to write to mailinglist
  - [x] define moderator email/member for list
- [x] modify subject and body of email
  - [x] additional tab in mailbox
  - [x] use placeholders for variables
- [ ] when adding member, mailbox... use the default values stored in DB
  - [ ] method for reading default values from DB (maybe through sqlite_master)
  - [ ] add API endpoint for object defaults
