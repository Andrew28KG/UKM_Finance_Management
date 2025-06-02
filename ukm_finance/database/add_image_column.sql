-- Add image column to transaksi table
ALTER TABLE `transaksi` ADD COLUMN `image` TEXT DEFAULT NULL COMMENT 'Image URL or file path for transaction receipt/proof';
