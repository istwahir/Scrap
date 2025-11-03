-- Migration: Add collector tracking columns
-- Date: 2025-11-02
-- Description: Add columns for tracking collector online status and current location

-- Add active_status column for online/offline/on_job tracking
ALTER TABLE collectors 
ADD COLUMN IF NOT EXISTS active_status ENUM('online', 'offline', 'on_job') DEFAULT 'offline' 
AFTER status;

-- Add current location columns
ALTER TABLE collectors 
ADD COLUMN IF NOT EXISTS current_latitude DECIMAL(10, 8) NULL,
ADD COLUMN IF NOT EXISTS current_longitude DECIMAL(11, 8) NULL;

-- Add last_active timestamp
ALTER TABLE collectors 
ADD COLUMN IF NOT EXISTS last_active TIMESTAMP NULL;

-- Add index for active status queries
ALTER TABLE collectors 
ADD INDEX IF NOT EXISTS idx_collectors_active_status (active_status);

-- Add index for location-based queries
ALTER TABLE collectors 
ADD INDEX IF NOT EXISTS idx_collectors_location (current_latitude, current_longitude);
