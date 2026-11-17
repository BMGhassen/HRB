-- ============================================================================
-- iDempiere Standard Schema: Bons de Transfert (XX_BT & XX_BTLine)
-- ============================================================================

-- Sequences
CREATE SEQUENCE IF NOT EXISTS xx_bt_sq START 1000000;
CREATE SEQUENCE IF NOT EXISTS xx_btline_sq START 1000000;
CREATE SEQUENCE IF NOT EXISTS bt_numero_seq START 1 INCREMENT 1;

-- 1. Table En-tête : XX_BT
CREATE TABLE IF NOT EXISTS XX_BT (
    XX_BT_ID            NUMERIC(10) NOT NULL PRIMARY KEY,
    AD_Client_ID        NUMERIC(10) NOT NULL,
    AD_Org_ID           NUMERIC(10) NOT NULL DEFAULT 0,
    IsActive            CHAR(1) NOT NULL DEFAULT 'Y' CHECK (IsActive IN ('Y','N')),
    Created             TIMESTAMP NOT NULL DEFAULT clock_timestamp(),
    CreatedBy           NUMERIC(10) NOT NULL DEFAULT 100,
    Updated             TIMESTAMP NOT NULL DEFAULT clock_timestamp(),
    UpdatedBy           NUMERIC(10) NOT NULL DEFAULT 100,
    DocumentNo          VARCHAR(30) NOT NULL,
    DateDoc             TIMESTAMP NOT NULL DEFAULT clock_timestamp(),
    Description         VARCHAR(255),
    Processed           CHAR(1) NOT NULL DEFAULT 'N' CHECK (Processed IN ('Y','N')),
    DocStatus           CHAR(2) NOT NULL DEFAULT 'DR' CHECK (DocStatus IN ('DR','CO','CL','VO'))
);

-- 2. Table Lignes : XX_BTLine
CREATE TABLE IF NOT EXISTS XX_BTLine (
    XX_BTLine_ID        NUMERIC(10) NOT NULL PRIMARY KEY,
    AD_Client_ID        NUMERIC(10) NOT NULL,
    AD_Org_ID           NUMERIC(10) NOT NULL DEFAULT 0,
    IsActive            CHAR(1) NOT NULL DEFAULT 'Y' CHECK (IsActive IN ('Y','N')),
    Created             TIMESTAMP NOT NULL DEFAULT clock_timestamp(),
    CreatedBy           NUMERIC(10) NOT NULL DEFAULT 100,
    Updated             TIMESTAMP NOT NULL DEFAULT clock_timestamp(),
    UpdatedBy           NUMERIC(10) NOT NULL DEFAULT 100,
    XX_BT_ID            NUMERIC(10) NOT NULL REFERENCES XX_BT(XX_BT_ID) ON DELETE CASCADE,
    Line                NUMERIC(10) NOT NULL DEFAULT 10,
    M_InOut_ID          NUMERIC(10) REFERENCES M_InOut(M_InOut_ID),
    M_InOutLine_ID      NUMERIC(10) REFERENCES M_InOutLine(M_InOutLine_ID),
    C_BPartner_ID       NUMERIC(10) REFERENCES C_BPartner(C_BPartner_ID),
    M_Product_ID        NUMERIC(10) REFERENCES M_Product(M_Product_ID),
    MovementQty         NUMERIC NOT NULL DEFAULT 1,
    Chauffeur           VARCHAR(120),
    Description         VARCHAR(255)
);

-- 3. Register in AD_Sequence (Table IDs)
INSERT INTO AD_Sequence (AD_Sequence_ID, AD_Client_ID, AD_Org_ID, IsActive, Created, CreatedBy, Updated, UpdatedBy, Name, Description, IsAutoSequence, IncrementNo, StartNo, CurrentNext, CurrentNextSys, IsAudited, IsTableID, StartNewYear, StartNewMonth, IsOrgLevelSequence, AD_Sequence_UU)
VALUES 
(1000026, 0, 0, 'Y', clock_timestamp(), 100, clock_timestamp(), 100, 'XX_BT', 'Table XX_BT', 'Y', 1, 1000000, 1000000, 200000, 'N', 'Y', 'N', 'N', 'N', generate_uuid()),
(1000027, 0, 0, 'Y', clock_timestamp(), 100, clock_timestamp(), 100, 'XX_BTLine', 'Table XX_BTLine', 'Y', 1, 1000000, 1000000, 200000, 'N', 'Y', 'N', 'N', 'N', generate_uuid())
ON CONFLICT (AD_Sequence_ID) DO NOTHING;

-- 4. Register in AD_Table
INSERT INTO AD_Table (AD_Table_ID, AD_Client_ID, AD_Org_ID, IsActive, Created, CreatedBy, Updated, UpdatedBy, Name, TableName, IsView, AccessLevel, EntityType, IsSecurityEnabled, IsDeleteable, IsHighVolume, ImportTable, IsChangeLog, ReplicationType, IsCentrallyMaintained, AD_Table_UU)
VALUES
(1000011, 0, 0, 'Y', clock_timestamp(), 100, clock_timestamp(), 100, 'Bon de Transfert', 'XX_BT', 'N', '3', 'U', 'N', 'Y', 'N', 'N', 'Y', 'L', 'Y', generate_uuid()),
(1000012, 0, 0, 'Y', clock_timestamp(), 100, clock_timestamp(), 100, 'Ligne Bon de Transfert', 'XX_BTLine', 'N', '3', 'U', 'N', 'Y', 'N', 'N', 'Y', 'L', 'Y', generate_uuid())
ON CONFLICT (AD_Table_ID) DO NOTHING;
