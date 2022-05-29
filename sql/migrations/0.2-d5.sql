ALTER TABLE mailbox
ADD s_imapencryption TEXT DEFAULT 'ssl'
CHECK (s_imapencryption IN ('none', 'ssl', 'tls'));

ALTER TABLE mailbox
ADD s_smtpencryption TEXT DEFAULT 'tls'
CHECK (s_smtpencryption IN ('none', 'ssl', 'tls'));