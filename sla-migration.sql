-- ═══════════════════════════════════════════════════════════
-- ATP Services SLA / Engagement Letter System
-- Migration SQL for SmartDash / NexCore Client Manager
-- ═══════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `nexcore_client_sla` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `client_id` BIGINT UNSIGNED NOT NULL,
    `sla_reference` VARCHAR(30) NOT NULL,

    -- Client signatory details (snapshot at signing time)
    `signatory_name` VARCHAR(255) NULL,
    `signatory_id_number` VARCHAR(20) NULL,
    `signatory_email` VARCHAR(255) NULL,
    `signatory_cellphone` VARCHAR(20) NULL,
    `signatory_designation` VARCHAR(100) NULL,
    `province` VARCHAR(50) NULL,

    -- Emergency contact
    `emergency_name` VARCHAR(255) NULL,
    `emergency_relationship` VARCHAR(100) NULL,
    `emergency_phone` VARCHAR(20) NULL,
    `emergency_email` VARCHAR(255) NULL,
    `emergency_consent` TINYINT(1) NOT NULL DEFAULT 0,

    -- Addendum A: Client information sheet
    `tax_reference_number` VARCHAR(30) NULL,
    `coida_rma_number` VARCHAR(30) NULL,
    `vat_number` VARCHAR(30) NULL,
    `paye_number` VARCHAR(30) NULL,
    `uif_number` VARCHAR(30) NULL,
    `applying_for` ENUM('individual','company') NOT NULL DEFAULT 'company',
    `company_reg_number` VARCHAR(50) NULL,
    `business_name` VARCHAR(255) NULL,
    `nature_of_business` VARCHAR(255) NULL,
    `physical_address` TEXT NULL,
    `postal_address` TEXT NULL,
    `work_telephone` VARCHAR(20) NULL,
    `marital_status` VARCHAR(50) NULL,

    -- Addendum B: Service selection
    `selected_package` VARCHAR(50) NULL COMMENT 'starter,growth,professional,enterprise,premium',
    `service_consent` TINYINT(1) NOT NULL DEFAULT 0,

    -- Addendum C: Debit order mandate
    `bank_account_holder` VARCHAR(255) NULL,
    `bank_name` VARCHAR(100) NULL,
    `bank_branch_code` VARCHAR(20) NULL,
    `bank_account_number` VARCHAR(30) NULL,
    `bank_account_type` VARCHAR(30) NULL,
    `debit_order_date` VARCHAR(10) NULL COMMENT '7th or 25th',
    `debit_order_consent` TINYINT(1) NOT NULL DEFAULT 0,

    -- Signature
    `signature_data` LONGTEXT NULL COMMENT 'Base64 signature or typed name',
    `signature_type` ENUM('drawn','typed') NULL,
    `signed_at_location` VARCHAR(255) NULL,
    `signed_date` DATE NULL,

    -- Status tracking
    `status` ENUM('draft','sent','viewed','signed','active','terminated','expired') NOT NULL DEFAULT 'draft',
    `sent_date` DATE NULL,
    `effective_date` DATE NULL,
    `termination_date` DATE NULL,
    `termination_reason` TEXT NULL,

    -- Metadata
    `notes` TEXT NULL,
    `created_by` BIGINT UNSIGNED NULL,
    `updated_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL,

    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_sla_reference` (`sla_reference`),
    KEY `idx_sla_client` (`client_id`),
    KEY `idx_sla_status` (`status`),
    KEY `idx_sla_signed_date` (`signed_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
