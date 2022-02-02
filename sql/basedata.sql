-- insert the default user (username: admin, pw: admin)
INSERT INTO user (s_username, s_password, s_role)
VALUES ('admin', '$2y$10$LDb/dYOuzQNRXJfxtN2H9egWjXRN2tC4Mg/NB6rx0ZSoyeMjOYjI2', 'ADMIN');

-- version number
INSERT INTO setting (s_name, s_type, v_value)
VALUES ('version_number', 'string', '0.1');

-- dbadmin access disabled
INSERT INTO setting (s_name, s_type, v_value)
VALUES ('enable_dbadmin', 'bool', 0);