CREATE TABLE `pos_order_drafts` (
  `id` VARCHAR(32) NOT NULL PRIMARY KEY,
  `user_id` INT NOT NULL,
  `data` JSON NOT NULL,
  `updated_at` DATETIME NOT NULL,
  INDEX (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;