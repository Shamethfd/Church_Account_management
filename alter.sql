-- Run this after importing db.sql to add the missing approved_date column
ALTER TABLE monthly_accounts
  ADD COLUMN approved_date DATE AFTER approved_name;
