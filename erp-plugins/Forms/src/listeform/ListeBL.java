package listeform;

import org.adempiere.webui.component.Button;

import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.text.SimpleDateFormat;

import org.compiere.util.DB;
import org.compiere.util.Env;

import org.adempiere.webui.panel.ADForm;
import org.zkoss.zul.Borderlayout;
import org.zkoss.zul.Div;
//import org.zkoss.zul.Label;
//import org.zkoss.zul.Listbox;
//import org.zkoss.zul.Listcell;
import org.zkoss.zul.Listhead;
import org.zkoss.zul.Listheader;
//import org.zkoss.zul.Listitem;
import org.adempiere.webui.component.Label;
import org.adempiere.webui.component.Listbox;
import org.adempiere.webui.component.ListItem;
import org.adempiere.webui.component.ListCell;
import org.adempiere.webui.component.Checkbox;
import org.zkoss.zul.North;
import org.zkoss.zul.Center;
import org.zkoss.zul.Vlayout;

import org.compiere.util.CLogger;
import org.zkoss.zk.ui.event.Event;
import org.zkoss.zk.ui.event.EventListener;
import org.zkoss.zk.ui.event.Events;

public class ListeBL extends ADForm {

    private static final long serialVersionUID = 1L;
    private static final CLogger log = CLogger.getCLogger(ListeBL.class);

    private Listbox blList = new Listbox();
    private Listbox lineList = new Listbox();

    @Override
    protected void initForm() {
        System.out.println("DEBUG ===> initForm ListeBL STARTED");
        Borderlayout layout = new Borderlayout();
        layout.setStyle("width: 100%; height: 100%;");
        this.appendChild(layout);

        // Header (Top Part)
        North north = new North();
        north.setHeight("50%"); // 50/50 split is safer for testing
        north.setAutoscroll(true);
        layout.appendChild(north);

        // Container for Button and List
        Vlayout northContainer = new Vlayout();
        northContainer.setVflex("1");
        northContainer.setHflex("1");
        north.appendChild(northContainer);

        // Refresh Button
        Button refreshBtn = new Button("Actualiser");
        refreshBtn.addEventListener(Events.ON_CLICK, e -> loadBLs());
        northContainer.appendChild(refreshBtn);

        // Remove the northDiv and append Listbox directly
        blList.setVflex("1");
        blList.setHflex("1");
        northContainer.appendChild(blList);

        // Define columns
        Listhead ih = new Listhead();
        Listheader docNoHeader = new Listheader("Document No");
        docNoHeader.setStyle("font-weight: bold; text-align: center; background-color: #C8E6C9;");
        ih.appendChild(docNoHeader);

        Listheader bpHeader = new Listheader("Tiers");
        bpHeader.setAlign("left");
        bpHeader.setStyle("font-weight: bold; text-align: center; background-color: #C8E6C9;");
        ih.appendChild(bpHeader);

        Listheader amountHeader = new Listheader("Total HT");
        amountHeader.setAlign("right");
        amountHeader.setStyle("font-weight: bold; text-align: center; background-color: #C8E6C9;");
        ih.appendChild(amountHeader);

        Listheader totalTTCHeader = new Listheader("Total TTC");
        totalTTCHeader.setAlign("right");
        totalTTCHeader.setStyle("font-weight: bold; text-align: center; background-color: #C8E6C9;");
        ih.appendChild(totalTTCHeader);

        Listheader dateHeader = new Listheader("Date");
        dateHeader.setAlign("right");
        dateHeader.setStyle("font-weight: bold; text-align: center; background-color: #C8E6C9;");
        ih.appendChild(dateHeader);

        Listheader magasinHeader = new Listheader("Magasin");
        magasinHeader.setAlign("center");
        magasinHeader.setStyle("font-weight: bold; text-align: center; background-color: #C8E6C9;");
        ih.appendChild(magasinHeader);

        Listheader printHeader = new Listheader("Imprimer");
        printHeader.setStyle("font-weight: bold; text-align: center; background-color: #C8E6C9;");
        ih.appendChild(printHeader);
        blList.appendChild(ih);

        // Detail (Bottom Part)
        Center center = new Center();
        center.setAutoscroll(true);
        layout.appendChild(center);

        lineList.setVflex("1");
        lineList.setHflex("1");
        center.appendChild(lineList);

        Listhead lh = new Listhead();
        Listheader lineNoHeader = new Listheader("Ligne n°");
        lineNoHeader.setStyle("font-weight: bold; text-align: center; background-color: #C8E6C9;");
        lh.appendChild(lineNoHeader);

        Listheader articleHeader = new Listheader("Article");
        articleHeader.setStyle("font-weight: bold; text-align: center; background-color: #C8E6C9;");
        lh.appendChild(articleHeader);

        Listheader qtyHeader = new Listheader("Quantité");
        qtyHeader.setStyle("font-weight: bold; text-align: center; background-color: #C8E6C9;");
        lh.appendChild(qtyHeader);

        Listheader amtHeader = new Listheader("Montant");
        amtHeader.setStyle("font-weight: bold; text-align: center; background-color: #C8E6C9;");
        lh.appendChild(amtHeader);

        lineList.appendChild(lh);

        loadBLs();

        // Select Event (Keep your existing listener)
        blList.addEventListener(Events.ON_SELECT, e -> {
            ListItem item = blList.getSelectedItem();
            if (item != null) {
                loadLines((Integer) item.getValue());
            }
        });
    }

    private void loadBLs() {
        blList.getItems().clear();
        int adClientID = Env.getAD_Client_ID(Env.getCtx());
        int adUserID = Env.getAD_User_ID(Env.getCtx());
        // log.warning("Querying BLs for AD_Client_ID: " + adClientID + ",
        // CreatedBy: " + adUserID);
        log.info("Loading BLs...");
        String sql = "SELECT io.M_InOut_ID, io.DocumentNo, bp.C_BPartner_ID, bp.Name, io.MovementDate, bp.value, o.TotalLines, o.GrandTotal, io.ctrmag "
                +
                "FROM M_InOut io " +
                "LEFT JOIN C_BPartner bp ON (io.C_BPartner_ID = bp.C_BPartner_ID) " +
                "LEFT JOIN C_Order o ON (io.C_Order_ID = o.C_Order_ID) " +
                "WHERE io.AD_Client_ID = ? AND io.CreatedBy = ? " +
                "ORDER BY io.MovementDate DESC";

        PreparedStatement pstmt = null;
        ResultSet rs = null;
        try {
            pstmt = DB.prepareStatement(sql, null);
            pstmt.setInt(1, adClientID);
            pstmt.setInt(2, adUserID);
            rs = pstmt.executeQuery();
            int clientID = Env.getAD_Client_ID(Env.getCtx());
            System.out.println("DEBUG: Querying BLs for Client ID: " + clientID);
            while (rs.next()) {
                int M_InOut_ID = rs.getInt(1);
                String docNo = rs.getString(2);
                String codebp = rs.getString("VALUE");
                String bpName = codebp + "_" + rs.getString(4);
                String dateAcct = new SimpleDateFormat("dd/MM/yyyy").format(rs.getTimestamp(5));
                String totalHT = rs.getBigDecimal(7) != null ? rs.getBigDecimal(7).toPlainString() : "0";
                String totalTTC = rs.getBigDecimal(8) != null ? rs.getBigDecimal(8).toPlainString() : "0";

                ListItem item = new ListItem();
                item.setValue(M_InOut_ID);
                item.appendChild(new ListCell(docNo));

                // Tiers Cell (Name Left, ID Right)
                ListCell tiersCell = new ListCell();
                try {
                    // Name Label (Left)
                    Label lblName = new Label(bpName != null ? bpName : "");
                    if (bpName != null && !bpName.isEmpty()) {
                        lblName.setTooltiptext(bpName);
                    }
                    tiersCell.appendChild(lblName);
                } catch (Exception e) {
                    log.severe("Error rendering Tiers cell: " + e.toString());
                    tiersCell.setLabel(bpName + " ");
                }
                item.appendChild(tiersCell);

                item.appendChild(new ListCell(totalHT));
                item.appendChild(new ListCell(totalTTC));
                item.appendChild(new ListCell(dateAcct));

                // Magasin Checkbox
                String magasinStr = rs.getString(9);
                String val = magasinStr != null ? magasinStr.trim() : "N";
                boolean isMagasin = "Y".equalsIgnoreCase(val) || "true".equalsIgnoreCase(val)
                        || "t".equalsIgnoreCase(val) || "yes".equalsIgnoreCase(val);

                Checkbox magasinChk = new Checkbox();
                magasinChk.setChecked(isMagasin);
                // If already checked (Y), lock it — value can only go N→Y, never Y→N
                magasinChk.setDisabled(isMagasin);
                final int inOutID = M_InOut_ID;
                if (!isMagasin) {
                    magasinChk.addEventListener(Events.ON_CHECK, ev -> {
                        String upd = "UPDATE M_InOut SET ctrmag = 'Y' WHERE M_InOut_ID = ?";
                        try {
                            PreparedStatement ps = DB.prepareStatement(upd, null);
                            ps.setInt(1, inOutID);
                            ps.executeUpdate();
                            DB.close(ps);
                            log.info("Updated ctrmag to Y for M_InOut_ID=" + inOutID);

                            // Traiter l'ajout des articles '#' au bon de transfert (BT)
                            try {
                                BonTransfertLogic.processBLValidation(inOutID);
                            } catch (Exception exBT) {
                                log.severe("Error processing BT lines for M_InOut_ID=" + inOutID + ": " + exBT.getMessage());
                            }

                            // Lock the checkbox immediately after confirming
                            magasinChk.setDisabled(true);
                        } catch (SQLException ex) {
                            log.severe("Error updating ctrmag: " + ex.getMessage());
                            // Revert the visual state if DB update failed
                            magasinChk.setChecked(false);
                        }
                    });
                }
                ListCell magasinCell = new ListCell();
                magasinCell.appendChild(magasinChk);
                item.appendChild(magasinCell);

                Button printBtn = new Button("Imprimer");
                printBtn.addEventListener(Events.ON_CLICK, e -> {
                    System.out.println("Printing BL: " + docNo);
                });
                ListCell printCell = new ListCell();
                printCell.appendChild(printBtn);
                item.appendChild(printCell);

                blList.appendChild(item);
                System.out.println("DEBUG: Found BL: " + docNo);
            }
        } catch (SQLException e) {
            log.severe(e.getMessage());
        } finally {
            DB.close(rs, pstmt);
        }
    }

    private void loadLines(int M_InOut_ID) {
        lineList.getItems().clear();

        String sql = "SELECT iol.Line, p.Name, iol.MovementQty, ol.LineNetAmt " +
                "FROM M_InOutLine iol " +
                "JOIN M_Product p ON (iol.M_Product_ID = p.M_Product_ID) " +
                "LEFT JOIN C_OrderLine ol ON (iol.C_OrderLine_ID = ol.C_OrderLine_ID) " +
                "WHERE iol.M_InOut_ID = ? " +
                "ORDER BY iol.Line";

        PreparedStatement pstmt = null;
        ResultSet rs = null;
        try {
            pstmt = DB.prepareStatement(sql, null);
            pstmt.setInt(1, M_InOut_ID);
            rs = pstmt.executeQuery();
            while (rs.next()) {
                int lineNo = rs.getInt(1);
                String prodName = rs.getString(2);
                String qty = rs.getBigDecimal(3).toPlainString();
                String lineAmt = rs.getBigDecimal(4) != null ? rs.getBigDecimal(4).toPlainString() : "0";

                ListItem item = new ListItem();
                item.appendChild(new ListCell(String.valueOf(lineNo)));
                item.appendChild(new ListCell(prodName));
                item.appendChild(new ListCell(qty));
                item.appendChild(new ListCell(lineAmt));
                lineList.appendChild(item);
            }
        } catch (SQLException e) {
            log.severe(e.getMessage());
        } finally {
            DB.close(rs, pstmt);
        }
    }
}
