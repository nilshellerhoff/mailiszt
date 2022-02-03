ALTER TABLE mailbox
ADD s_welcometext TEXT;

ALTER TABLE mailbox
ADD b_sendwelcometext INT DEFAULT 0;