package listeform;

import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.List;

import org.compiere.util.CLogger;
import org.compiere.util.DB;

public class BonTransfertLogic {

    private static final CLogger log = CLogger.getCLogger(BonTransfertLogic.class);

    /**
     * Process a validated BL (M_InOut) and record its '#'-prefixed product lines
     * into a Bon de Transfert (XX_BT / XX_BTLine).
     *
     * @param M_InOut_ID the ID of the shipment (BL) that was just validated
     */
    public static void processBLValidation(int M_InOut_ID) {

        // ------------------------------------------------------------------
        // Step 1 – Collect all '#' product lines for this BL
        // ------------------------------------------------------------------
        String sqlLines =
            "SELECT p.Name, io.DocumentNo, io.MovementDate, bp.Name as ClientName, p.Value, " +
            "       bp.Value as ClientCode, iol.MovementQty, " +
            "       COALESCE(o.Description, io.Description, '') as Chauffeur, " +
            "       io.AD_Client_ID, io.AD_Org_ID, io.UpdatedBy, " +
            "       iol.M_InOut_ID, iol.M_InOutLine_ID, io.C_BPartner_ID, iol.M_Product_ID " +
            "FROM M_InOutLine iol " +
            "JOIN M_Product p ON (iol.M_Product_ID = p.M_Product_ID) " +
            "JOIN M_InOut io ON (iol.M_InOut_ID = io.M_InOut_ID) " +
            "LEFT JOIN C_Order o ON (io.C_Order_ID = o.C_Order_ID) " +
            "LEFT JOIN C_BPartner bp ON (io.C_BPartner_ID = bp.C_BPartner_ID) " +
            "WHERE iol.M_InOut_ID = ? AND p.Name LIKE '#%' " +
            "ORDER BY iol.Line";

        // Simple inner class to hold one detail row
        class BLRow {
            String productName;
            String documentNo;
            java.sql.Date movementDate;
            String clientName;
            String productCode;
            String clientCode;
            java.math.BigDecimal qty;
            String chauffeur;
            int adClientID;
            int adOrgID;
            int userID;
            int mInOutID;
            int mInOutLineID;
            int cBPartnerID;
            int mProductID;
        }

        List<BLRow> rows = new ArrayList<BLRow>();

        PreparedStatement pstmt = null;
        ResultSet rs = null;
        try {
            pstmt = DB.prepareStatement(sqlLines, null);
            pstmt.setInt(1, M_InOut_ID);
            rs = pstmt.executeQuery();
            while (rs.next()) {
                BLRow row = new BLRow();
                row.productName  = rs.getString(1);
                row.documentNo   = rs.getString(2);
                row.movementDate = rs.getDate(3);
                row.clientName   = rs.getString(4);
                row.productCode  = rs.getString(5);
                row.clientCode   = rs.getString(6);
                row.qty          = rs.getBigDecimal(7);
                row.chauffeur    = rs.getString(8);
                row.adClientID   = rs.getInt(9);
                row.adOrgID      = rs.getInt(10);
                row.userID       = rs.getInt(11);
                row.mInOutID     = rs.getInt(12);
                row.mInOutLineID = rs.getInt(13);
                row.cBPartnerID  = rs.getInt(14);
                row.mProductID   = rs.getInt(15);
                rows.add(row);
            }
        } catch (SQLException e) {
            log.severe("Error reading BL lines for M_InOut_ID=" + M_InOut_ID + ": " + e.getMessage());
            return;
        } finally {
            DB.close(rs, pstmt);
            pstmt = null;
            rs = null;
        }

        // No '#' products → nothing to do
        if (rows.isEmpty()) {
            log.info("No '#' products found for M_InOut_ID=" + M_InOut_ID + " — no BT created.");
            return;
        }

        BLRow firstRow = rows.get(0);
        int adClientID = firstRow.adClientID;
        int adOrgID = firstRow.adOrgID;
        int userID = firstRow.userID > 0 ? firstRow.userID : 100;

        // ------------------------------------------------------------------
        // Step 2 – Find or create the active (Processed='N') XX_BT
        // ------------------------------------------------------------------
        int btID = -1;

        String sqlFindBT = "SELECT XX_BT_ID FROM XX_BT WHERE Processed = 'N' AND AD_Client_ID = ? ORDER BY XX_BT_ID DESC LIMIT 1";
        try {
            pstmt = DB.prepareStatement(sqlFindBT, null);
            pstmt.setInt(1, adClientID);
            rs = pstmt.executeQuery();
            if (rs.next()) {
                btID = rs.getInt(1);
                log.info("Reusing existing XX_BT XX_BT_ID=" + btID);
            }
        } catch (SQLException e) {
            log.severe("Error finding open XX_BT: " + e.getMessage());
            return;
        } finally {
            DB.close(rs, pstmt);
            pstmt = null;
            rs = null;
        }

        if (btID < 0) {
            // No open BT — create a new one with sequence-generated ID & DocumentNo
            String sqlInsertBT =
                "INSERT INTO XX_BT (XX_BT_ID, AD_Client_ID, AD_Org_ID, CreatedBy, UpdatedBy, DocumentNo, DateDoc, Processed, DocStatus) " +
                "VALUES (NEXTVAL('xx_bt_sq'), ?, ?, ?, ?, 'BT-' || TO_CHAR(CURRENT_DATE, 'YYYY') || '-' || LPAD(NEXTVAL('bt_numero_seq')::text, 4, '0'), " +
                "        CURRENT_DATE, 'N', 'DR') " +
                "RETURNING XX_BT_ID, DocumentNo";
            try {
                pstmt = DB.prepareStatement(sqlInsertBT, null);
                pstmt.setInt(1, adClientID);
                pstmt.setInt(2, adOrgID);
                pstmt.setInt(3, userID);
                pstmt.setInt(4, userID);
                rs = pstmt.executeQuery();
                if (rs.next()) {
                    btID = rs.getInt(1);
                    String generatedNumero = rs.getString(2);
                    log.info("Created new XX_BT XX_BT_ID=" + btID + " DocumentNo=" + generatedNumero);
                }
            } catch (SQLException e) {
                log.severe("Error creating XX_BT: " + e.getMessage());
                return;
            } finally {
                DB.close(rs, pstmt);
                pstmt = null;
                rs = null;
            }
        }

        if (btID < 0) {
            log.severe("Could not obtain a valid XX_BT_ID — aborting.");
            return;
        }

        // Determine current max line number for this BT
        int currentMaxLine = 0;
        String sqlMaxLine = "SELECT COALESCE(MAX(Line), 0) FROM XX_BTLine WHERE XX_BT_ID = ?";
        try {
            pstmt = DB.prepareStatement(sqlMaxLine, null);
            pstmt.setInt(1, btID);
            rs = pstmt.executeQuery();
            if (rs.next()) {
                currentMaxLine = rs.getInt(1);
            }
        } catch (SQLException e) {
            log.severe("Error fetching max line for XX_BT_ID=" + btID + ": " + e.getMessage());
        } finally {
            DB.close(rs, pstmt);
            pstmt = null;
            rs = null;
        }

        // ------------------------------------------------------------------
        // Step 3 – Insert one XX_BTLine row per collected '#' line
        // ------------------------------------------------------------------
        String sqlInsertDetail =
            "INSERT INTO XX_BTLine (XX_BTLine_ID, AD_Client_ID, AD_Org_ID, CreatedBy, UpdatedBy, XX_BT_ID, Line, " +
            "                       M_InOut_ID, M_InOutLine_ID, C_BPartner_ID, M_Product_ID, MovementQty, Chauffeur) " +
            "VALUES (NEXTVAL('xx_btline_sq'), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        for (int i = 0; i < rows.size(); i++) {
            BLRow row = rows.get(i);
            int nextLineNo = currentMaxLine + ((i + 1) * 10);
            try {
                pstmt = DB.prepareStatement(sqlInsertDetail, null);
                pstmt.setInt(1, row.adClientID);
                pstmt.setInt(2, row.adOrgID);
                pstmt.setInt(3, row.userID);
                pstmt.setInt(4, row.userID);
                pstmt.setInt(5, btID);
                pstmt.setInt(6, nextLineNo);
                if (row.mInOutID > 0) {
                    pstmt.setInt(7, row.mInOutID);
                } else {
                    pstmt.setNull(7, java.sql.Types.NUMERIC);
                }
                if (row.mInOutLineID > 0) {
                    pstmt.setInt(8, row.mInOutLineID);
                } else {
                    pstmt.setNull(8, java.sql.Types.NUMERIC);
                }
                if (row.cBPartnerID > 0) {
                    pstmt.setInt(9, row.cBPartnerID);
                } else {
                    pstmt.setNull(9, java.sql.Types.NUMERIC);
                }
                if (row.mProductID > 0) {
                    pstmt.setInt(10, row.mProductID);
                } else {
                    pstmt.setNull(10, java.sql.Types.NUMERIC);
                }
                if (row.qty != null) {
                    pstmt.setBigDecimal(11, row.qty);
                } else {
                    pstmt.setBigDecimal(11, java.math.BigDecimal.ONE);
                }
                pstmt.setString(12, row.chauffeur);
                pstmt.executeUpdate();
                log.info("Inserted XX_BTLine for XX_BT_ID=" + btID + " Line=" + nextLineNo + " Article=" + row.productName);
            } catch (SQLException e) {
                log.severe("Error inserting XX_BTLine for XX_BT_ID=" + btID + ": " + e.getMessage());
            } finally {
                DB.close(pstmt);
                pstmt = null;
            }
        }
    }

    /**
     * Generate the CSV export for a validated / Traitee BT.
     * Returns the CSV content as a byte array for browser download via Filedownload.save().
     *
     * @param btID the ID of the XX_BT
     * @param btNumero the DocumentNo of the BT
     * @return CSV content as UTF-8 bytes, or null on error
     */
    public static byte[] generateCSVExport(int btID, String btNumero) {
        String sql =
            "SELECT COALESCE(bp.Value, ''), " +
            "       COALESCE(p.Value, ''), " +
            "       COALESCE(p.Name, ''), " +
            "       COALESCE(btl.MovementQty, 1), " +
            "       COALESCE(btl.Chauffeur, '') " +
            "FROM XX_BTLine btl " +
            "LEFT JOIN C_BPartner bp ON (btl.C_BPartner_ID = bp.C_BPartner_ID) " +
            "LEFT JOIN M_Product p ON (btl.M_Product_ID = p.M_Product_ID) " +
            "WHERE btl.XX_BT_ID = ? " +
            "ORDER BY btl.Line, btl.XX_BTLine_ID";

        PreparedStatement pstmt = null;
        ResultSet rs = null;

        try {
            java.io.ByteArrayOutputStream baos = new java.io.ByteArrayOutputStream();
            java.io.BufferedWriter writer = new java.io.BufferedWriter(
                new java.io.OutputStreamWriter(baos, java.nio.charset.StandardCharsets.UTF_8));

            // Header line: Client;Code;Des;QTE;Chauffeur;
            writer.write("Client;Code;Des;QTE;Chauffeur;");
            writer.newLine();

            pstmt = DB.prepareStatement(sql, null);
            pstmt.setInt(1, btID);
            rs = pstmt.executeQuery();

            while (rs.next()) {
                String clientCode = rs.getString(1);
                String prodCode   = rs.getString(2);
                String des        = rs.getString(3);
                java.math.BigDecimal qte = rs.getBigDecimal(4);
                String qteStr = (qte != null) ? qte.stripTrailingZeros().toPlainString() : "1";
                String chauffeur  = rs.getString(5);

                StringBuilder sb = new StringBuilder();
                sb.append(clientCode != null ? clientCode.trim() : "").append(";");
                sb.append(prodCode   != null ? prodCode.trim()   : "").append(";");
                sb.append(des        != null ? des.trim()        : "").append(";");
                sb.append(qteStr).append(";");
                sb.append(chauffeur  != null ? chauffeur.trim()  : "").append(";");

                writer.write(sb.toString());
                writer.newLine();
            }

            writer.flush();
            log.info("CSV bytes built for XX_BT_ID=" + btID + ", size=" + baos.size());
            return baos.toByteArray();

        } catch (Exception e) {
            log.severe("Error generating CSV for XX_BT_ID=" + btID + ": " + e.getMessage());
            return null;
        } finally {
            DB.close(rs, pstmt);
        }
    }
}
