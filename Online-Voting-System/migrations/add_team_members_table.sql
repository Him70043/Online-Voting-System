-- Migration to add team_members table and update voters table structure
-- This migration supports the Vote Integrity and Audit Trail implementation

USE polltest;

-- Create team_members table if it doesn't exist
CREATE TABLE IF NOT EXISTS `team_members` (
  `member_id` int(100) NOT NULL AUTO_INCREMENT,
  `fullname` varchar(50) NOT NULL,
  `about` varchar(255) NOT NULL,
  `votecount` int(255) NOT NULL DEFAULT 0,
  PRIMARY KEY (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample team members if table is empty
INSERT IGNORE INTO `team_members` (`member_id`, `fullname`, `about`, `votecount`) VALUES
(1, 'Himanshu Kumar', 'Project Lead and Developer', 0),
(2, 'Alice Johnson', 'Frontend Developer', 0),
(3, 'Bob Smith', 'Backend Developer', 0),
(4, 'Carol Davis', 'UI/UX Designer', 0),
(5, 'David Wilson', 'Quality Assurance', 0);

-- Add team_voted column to voters table if it doesn't exist
ALTER TABLE `voters` 
ADD COLUMN IF NOT EXISTS `team_voted` varchar(255) DEFAULT NULL AFTER `voted`;

-- Update voters table status values to be consistent
UPDATE `voters` SET `status` = 'VOTED' WHERE `status` = 'NOTVOTED' AND `voted` IS NOT NULL;
UPDATE `voters` SET `status` = 'NOT_VOTED' WHERE `status` = 'NOTVOTED' AND `voted` IS NULL;

-- Add indexes for better performance
ALTER TABLE `voters` ADD INDEX IF NOT EXISTS `idx_status` (`status`);
ALTER TABLE `voters` ADD INDEX IF NOT EXISTS `idx_voted` (`voted`);
ALTER TABLE `voters` ADD INDEX IF NOT EXISTS `idx_team_voted` (`team_voted`);

-- Add indexes to languages table for better performance
ALTER TABLE `languages` ADD INDEX IF NOT EXISTS `idx_fullname` (`fullname`);
ALTER TABLE `languages` ADD INDEX IF NOT EXISTS `idx_votecount` (`votecount`);

-- Add indexes to team_members table for better performance
ALTER TABLE `team_members` ADD INDEX IF NOT EXISTS `idx_fullname` (`fullname`);
ALTER TABLE `team_members` ADD INDEX IF NOT EXISTS `idx_votecount` (`votecount`);