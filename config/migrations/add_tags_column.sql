USE notes_app;

-- Add tags column if it doesn't exist
ALTER TABLE notes
ADD COLUMN IF NOT EXISTS tags JSON DEFAULT '[]' AFTER content; 