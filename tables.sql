CREATE TABLE mailbox (
    i_mailbox INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    s_name TEXT,
    s_address TEXT,
    s_imapserver TEXT,
    n_imapport TEXT,
    s_smtpserver TEXT,
    n_smtpport TEXT,
    s_username TEXT,
    s_password TEXT,
    s_groupsjson TEXT,
    s_groupssql TEXT,
    d_inserted TEXT,
    d_updated TEXT
);

CREATE TABLE mail (
    i_mail INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    i_mailbox INTEGER,
    s_internalid TEXT,
    s_frommail TEXT,
    s_fromname TEXT,
    s_replytomail TEXT,
    s_replytoname TEXT,
    s_tomail TEXT,
    s_toname TEXT,
    s_messageid TEXT,
    d_sent TEXT,
    s_subject TEXT,
    s_bodytext TEXT,
    s_bodyhtml TEXT,
    n_attachments INTEGER,
    d_forwarded TEXT,
    d_inserted TEXT,
    d_updated TEXT
);

CREATE TABLE attachment (
    i_attachment INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    i_mail INTEGER,
    s_filename TEXT,
    s_name TEXT,
    s_contenttype TEXT,
    s_cid TEXT,
    n_size INTEGER,
    n_index INTEGER,
    d_inserted TEXT,
    d_updated TEXT
);

CREATE TABLE mail2member (
    i_mail2member INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    i_mail INTEGER,
    i_member INTEGER,
    d_sent TEXT,
    s_email TEXT,
    d_inserted TEXT,
    d_updated TEXT
);

CREATE TABLE user (
    i_user INTEGER PRIMARY KEY,
    s_username TEXT,
    s_password TEXT,
    s_role TEXT,
    d_inserted TEXT,
    d_updated TEXT
);

CREATE TABLE authtoken (
    i_authtoken INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    i_user INTEGER,
    s_username TEXT,
    s_token TEXT,
    d_inserted TEXT
);

CREATE TABLE member (
    i_member INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    s_name1 TEXT,
    s_name2 TEXT,
    s_email TEXT,
    s_phone TEXT,
    d_birthdate TEXT,
    b_active INTEGER,
    d_inserted TEXT,
    d_updated TEXT
);

CREATE TABLE _group (
    i_group INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    s_name TEXT,
    d_inserted TEXT,
    d_updated TEXT
);

CREATE TABLE member2group (
    i_member2group INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    i_member INTEGER,
    i_group INTEGER,
    d_inserted TEXT,
    d_updated TEXT
);

CREATE TABLE setting (
    i_setting INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    s_name TEXT,
    s_type TEXT,
    v_value
);