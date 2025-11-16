-- Migration: Add photo_url and suggested_by columns to dropoff_points table
-- This allows tracking who added each drop-off point and storing photos

-- Add photo_url column to store uploaded images
ALTER TABLE dropoff_points
ADD COLUMN photo_url VARCHAR(255) NULL AFTER materials;

-- Add suggested_by column to track which collector suggested the drop-off
ALTER TABLE dropoff_points
ADD COLUMN suggested_by INT NULL AFTER photo_url;

-- Add foreign key constraint to link to collectors table
ALTER TABLE dropoff_points
ADD CONSTRAINT fk_dropoff_suggested_by 
FOREIGN KEY (suggested_by) REFERENCES collectors(id) 
ON DELETE SET NULL;

-- Add index for faster lookups
CREATE INDEX idx_dropoff_suggested_by ON dropoff_points(suggested_by);

-- Verification query to check columns were added
SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'kiambu_recycling' 
  AND TABLE_NAME = 'dropoff_points' 
  AND COLUMN_NAME IN ('photo_url', 'suggested_by');
