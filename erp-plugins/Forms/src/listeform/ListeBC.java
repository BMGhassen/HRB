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
import org.zkoss.zul.Listhead;
import org.zkoss.zul.Listheader;
import org.adempiere.webui.component.Label;
import org.adempiere.webui.component.Listbox;
import org.adempiere.webui.component.ListItem;
import org.adempiere.webui.component.ListCell;
import org.adempiere.webui.component.Checkbox;
import org.zkoss.zul.North;
import org.zkoss.zul.Center;
import org.zkoss.zul.Vlayout;

import org.compiere.util.CLogger;
import org.zkoss.zk.ui.event.Events;

public class ListeBC extends ADForm {

    private static final long serialVersionUID = 1L;
    private static final CLogger log = CLogger.getCLogger(ListeBC.class);

    private Listbox bcList = new Listbox();
    private Listbox lineList = new Listbox();

    @Override
    protected void initForm() {
        System.out.println("DEBUG ===> initForm ListeBC STARTED");
        Borderlayout layout = new Borderlayout();
        layout.setStyle("width: 100%; height: 100%;");
        this.appendChild(layout);

        // Header (Top Part)
        North north = new North();
        north.setHeight("50%");
        north.setAutoscroll(true);
        layout.appendChild(north);

        // Container for Button and List
        Vlayout northContainer = new Vlayout();
        northContainer.setVflex("1");
        northContainer.setHflex("1");
        north.appendChild(northContainer);

        // Refresh Button
        Button refreshBtn = new Button("Actualiser");
        refreshBtn.addEventListener(Events.ON_CLICK, e -> loadBCs());
        northContainer.appendChild(refreshBtn);

        // BC List
        bcList.setVflex("1");
        bcList.setHflex("1");
        northContainer.appendChild(bcList);

        // Define columns for BC list
        Listhead ih = new Listhead();

        Listheader docNoHeader = new Listheader("Document No");
        docNoHeader.setStyle("font-weight: bold; text-align: center; background-color: #FFCDD2;");
        ih.appendChild(docNoHeader);

        Listheader bpHeader = new Listheader("Tiers");
        bpHeader.setAlign("left");
        bpHeader.setStyle("font-weight: bold; text-align: center; background-color: #FFCDD2;");
        ih.appendChild(bpHeader);

        Listheader amountHeader = new Listheader("Total HT");
        amountHeader.setAlign("right");
        amountHeader.setStyle("font-weight: bold; text-align: center; background-color: #FFCDD2;");
        ih.appendChild(amountHeader);

        Listheader totalTTCHeader = new Listheader("Total TTC");
        totalTTCHeader.setAlign("right");
        totalTTCHeader.setStyle("font-weight: bold; text-align: center; background-color: #FFCDD2;");
        ih.appendChild(totalTTCHeader);

        Listheader dateHeader = new Listheader("Date");
        dateHeader.setAlign("right");
        dateHeader.setStyle("font-weight: bold; text-align: center; background-color: #FFCDD2;");
        ih.appendChild(dateHeader);

        Listheader printHeader = new Listheader("Imprimer");
        printHeader.setStyle("font-weight: bold; text-align: center; background-color: #FFCDD2;");
        ih.appendChild(printHeader);

        bcList.appendChild(ih);

        // Detail (Bottom Part)
        Center center = new Center();
        center.setAutoscroll(true);
        layout.appendChild(center);

        lineList.setVflex("1");
        lineList.setHflex("1");
        center.appendChild(lineList);

        Listhead lh = new Listhead();

        Listheader lineNoHeader = new Listheader("Ligne n°");
        lineNoHeader.setStyle("font-weight: bold; text-align: center; background-color: #FFCDD2;");
        lh.appendChild(lineNoHeader);

        Listheader articleHeader = new Listheader("Article");
        articleHeader.setStyle("font-weight: bold; text-align: center; background-color: #FFCDD2;");
        lh.appendChild(articleHeader);

        Listheader qtyHeader = new Listheader("Quantité");
        qtyHeader.setStyle("font-weight: bold; text-align: center; background-color: #FFCDD2;");
        lh.appendChild(qtyHeader);

        Listheader amtHeader = new Listheader("Montant");
        amtHeader.setStyle("font-weight: bold; text-align: center; background-color: #FFCDD2;");
        lh.appendChild(amtHeader);

        Listheader magasinHeader = new Listheader("Magasin");
        magasinHeader.setAlign("center");
        magasinHeader.setStyle("font-weight: bold; text-align: center; background-color: #FFCDD2;");
        lh.appendChild(magasinHeader);

        lineList.appendChild(lh);

        loadBCs();

        // Select Event — load lines for the selected BC
        bcList.addEventListener(Events.ON_SELECT, e -> {
            ListItem item = bcList.getSelectedItem();
            if (item != null) {
                loadLines((Integer) item.getValue());
            }
        });
    }

    private void loadBCs() {
        bcList.getItems().clear();
        int adClientID = Env.getAD_Client_ID(Env.getCtx());
        int adUserID = Env.getAD_User_ID(Env.getCtx());
        log.info("Loading BCs...");

        String sql = "SELECT o.C_Order_ID, o.DocumentNo, bp.C_BPartner_ID, bp.Name, o.DateOrdered, bp.Value, o.TotalLines, o.GrandTotal "
                + "FROM C_Order o "
                + "LEFT JOIN C_BPartner bp ON (o.C_BPartner_ID = bp.C_BPartner_ID) "
                + "WHERE o.AD_Client_ID = ? AND o.CreatedBy = ? "
                + "ORDER BY o.DateOrdered DESC";

        PreparedStatement pstmt = null;
        ResultSet rs = null;
        try {
            pstmt = DB.prepareStatement(sql, null);
            pstmt.setInt(1, adClientID);
            pstmt.setInt(2, adUserID);
            rs = pstmt.executeQuery();
            System.out.println("DEBUG: Querying BCs for Client ID: " + adClientID);

            while (rs.next()) {
                int C_Order_ID = rs.getInt(1);
                String docNo = rs.getString(2);
                String codebp = rs.getString("Value");
                String bpName = codebp + "_" + rs.getString(4);
                String dateOrdered = new SimpleDateFormat("dd/MM/yyyy").format(rs.getTimestamp(5));
                String totalHT = rs.getBigDecimal(7) != null ? rs.getBigDecimal(7).toPlainString() : "0";
                String totalTTC = rs.getBigDecimal(8) != null ? rs.getBigDecimal(8).toPlainString() : "0";

                ListItem item = new ListItem();
                item.setValue(C_Order_ID);
                item.appendChild(new ListCell(docNo));

                // Tiers Cell
                ListCell tiersCell = new ListCell();
                try {
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
                item.appendChild(new ListCell(dateOrdered));

                Button printBtn = new Button("Imprimer");
                printBtn.addEventListener(Events.ON_CLICK, e -> {
                    System.out.println("Printing BC: " + docNo);
                });
                ListCell printCell = new ListCell();
                printCell.appendChild(printBtn);
                item.appendChild(printCell);

                bcList.appendChild(item);
                System.out.println("DEBUG: Found BC: " + docNo);
            }
        } catch (SQLException e) {
            log.severe(e.getMessage());
        } finally {
            DB.close(rs, pstmt);
        }
    }

    private void loadLines(int C_Order_ID) {
        lineList.getItems().clear();

        String sql = "SELECT ol.Line, p.Name, ol.QtyOrdered, ol.LineNetAmt, ol.ctrmag "
                + "FROM C_OrderLine ol "
                + "JOIN M_Product p ON (ol.M_Product_ID = p.M_Product_ID) "
                + "WHERE ol.C_Order_ID = ? "
                + "ORDER BY ol.Line";

        PreparedStatement pstmt = null;
        ResultSet rs = null;
        try {
            pstmt = DB.prepareStatement(sql, null);
            pstmt.setInt(1, C_Order_ID);
            rs = pstmt.executeQuery();
            while (rs.next()) {
                int lineNo = rs.getInt(1);
                String prodName = rs.getString(2);
                String qty = rs.getBigDecimal(3).toPlainString();
                String lineAmt = rs.getBigDecimal(4) != null ? rs.getBigDecimal(4).toPlainString() : "0";
                String magasinStr = rs.getString(5);
                String magVal = magasinStr != null ? magasinStr.trim() : "N";
                boolean isMagasin = "Y".equalsIgnoreCase(magVal) || "true".equalsIgnoreCase(magVal)
                        || "t".equalsIgnoreCase(magVal) || "yes".equalsIgnoreCase(magVal);

                ListItem item = new ListItem();
                item.appendChild(new ListCell(String.valueOf(lineNo)));
                item.appendChild(new ListCell(prodName));
                item.appendChild(new ListCell(qty));
                item.appendChild(new ListCell(lineAmt));

                // Magasin Checkbox
                Checkbox magasinChk = new Checkbox();
                magasinChk.setChecked(isMagasin);
                magasinChk.setDisabled(isMagasin);
                final int orderLineID_unused = lineNo; // placeholder
                if (!isMagasin) {
                    magasinChk.addEventListener(Events.ON_CHECK, ev -> {
                        String upd = "UPDATE C_OrderLine SET ctrmag = 'Y' WHERE C_Order_ID = ? AND Line = ?";
                        try {
                            PreparedStatement ps = DB.prepareStatement(upd, null);
                            ps.setInt(1, C_Order_ID);
                            ps.setInt(2, lineNo);
                            ps.executeUpdate();
                            DB.close(ps);
                            log.info("Updated ctrmag to Y for C_Order_ID=" + C_Order_ID + " Line=" + lineNo);
                            magasinChk.setDisabled(true);
                        } catch (SQLException ex) {
                            log.severe("Error updating ctrmag: " + ex.getMessage());
                            magasinChk.setChecked(false);
                        }
                    });
                }
                ListCell magasinCell = new ListCell();
                magasinCell.appendChild(magasinChk);
                item.appendChild(magasinCell);

                lineList.appendChild(item);
            }
        } catch (SQLException e) {
            log.severe(e.getMessage());
        } finally {
            DB.close(rs, pstmt);
        }
    }
}
