ALTER TABLE mailbox
ADD b_sendrejectionnotices INT NOT NULL DEFAULT 0;
 
ALTER TABLE mailbox
ADD s_templaterejectionnotice TEXT NOT NULL DEFAULT 'Hello {{fromname}}, Unfortunately you are not allowed to send mails to {{listaddress}}. If you have questions, contact the moderator at {{moderatoraddress}}.Best regards, Mailiszt';