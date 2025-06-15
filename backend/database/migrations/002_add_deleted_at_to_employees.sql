ALTER TABLE employees
ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL;

-- Add index for soft delete queries
CREATE INDEX idx_deleted_at ON employees(deleted_at); 