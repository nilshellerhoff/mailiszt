ALTER TABLE mailbox
ADD s_templatesubject TEXT NOT NULL DEFAULT '{{subject}}';

ALTER TABLE mailbox
ADD s_templatefrom TEXT NOT NULL DEFAULT '{{fromname}} via {{listname}}';

-- x'0a' is SQLITE for newline character
-- ALTER TABLE mailbox
-- ADD s_templatebody TEXT NOT NULL DEFAULT '{{body}}' || x'0a' || x'0a' || '----------------' || x'0a' || 'This mail was originally sent by {{frommail}} to {{listaddress}}. Contact the moderator at {{moderatoraddress}} regarding any questions.';

-- temporary workaround without footer because the concatenate operator || doesn't work in DEFAULT clause
-- (see https://stackoverflow.com/questions/72622175/add-column-with-default-including-newlines)
ALTER TABLE mailbox
ADD s_templatebody TEXT NOT NULL DEFAULT '{{body}}'