-- insert the version number
INSERT INTO setting (s_name, s_type, v_value)
VALUES ('version_number', 'string', '0.1')

-- insert the default user (username: admin, pw: admin)
INSERT INTO user (s_username, s_password, s_role)
VALUES ('admin', '$2y$10$zqpp/tiCrXc1bEN3tCklxuLJchle1JSzU26jtusLlJqsoVugr2IK.', 'ADMIN')