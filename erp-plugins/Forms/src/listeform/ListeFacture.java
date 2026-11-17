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

public class ListeFacture extends ADForm {

    private static final long serialVersionUID = 1L;
    private static final CLogger log = CLogger.getCLogger(ListeFacture.class);

    private Listbox invoiceList = new Listbox();
    private Listbox lineList = new Listbox();

    @Override
    protected void initForm() {
        System.out.println("DEBUG ===> initForm STARTED");
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
        refreshBtn.addEventListener(Events.ON_CLICK, e -> loadInvoices());
        northContainer.appendChild(refreshBtn);

        // Remove the northDiv and append Listbox directly
        invoiceList.setVflex("1");
        invoiceList.setHflex("1");
        northContainer.appendChild(invoiceList);

        // Define columns
        Listhead ih = new Listhead();
        Listheader docNoHeader = new Listheader("Document No");
        docNoHeader.setStyle("font-weight: bold; text-align: center;");
        ih.appendChild(docNoHeader);

        Listheader bpHeader = new Listheader("Tiers");
        bpHeader.setAlign("left");
        bpHeader.setStyle("font-weight: bold; text-align: center;");
        ih.appendChild(bpHeader);

        Listheader amountHeader = new Listheader("Total HT");
        amountHeader.setAlign("right");
        amountHeader.setStyle("font-weight: bold; text-align: center;");
        ih.appendChild(amountHeader);

        Listheader totalTTCHeader = new Listheader("Total TTC");
        totalTTCHeader.setAlign("right");
        totalTTCHeader.setStyle("font-weight: bold; text-align: center;");
        ih.appendChild(totalTTCHeader);

        Listheader dateHeader = new Listheader("Date");
        dateHeader.setAlign("right");
        dateHeader.setStyle("font-weight: bold; text-align: center;");
        ih.appendChild(dateHeader);

        Listheader magasinHeader = new Listheader("Magasin");
        magasinHeader.setAlign("center");
        magasinHeader.setStyle("font-weight: bold; text-align: center;");
        ih.appendChild(magasinHeader);

        Listheader printHeader = new Listheader("Imprimer");
        printHeader.setStyle("font-weight: bold; text-align: center;");
        ih.appendChild(printHeader);
        invoiceList.appendChild(ih);

        // Detail (Bottom Part)
        Center center = new Center();
        center.setAutoscroll(true);
        layout.appendChild(center);

        lineList.setVflex("1");
        lineList.setHflex("1");
        center.appendChild(lineList);

        Listhead lh = new Listhead();
        Listheader lineNoHeader = new Listheader("Ligne n°");
        lineNoHeader.setStyle("font-weight: bold; text-align: center;");
        lh.appendChild(lineNoHeader);

        Listheader articleHeader = new Listheader("Article");
        articleHeader.setStyle("font-weight: bold; text-align: center;");
        lh.appendChild(articleHeader);

        Listheader qtyHeader = new Listheader("Quantité");
        qtyHeader.setStyle("font-weight: bold; text-align: center;");
        lh.appendChild(qtyHeader);

        Listheader amtHeader = new Listheader("Montant");
        amtHeader.setStyle("font-weight: bold; text-align: center;");
        lh.appendChild(amtHeader);
        lineList.appendChild(lh);

        loadInvoices();

        // Select Event (Keep your existing listener)
        invoiceList.addEventListener(Events.ON_SELECT, e -> {
            ListItem item = invoiceList.getSelectedItem();
            if (item != null) {
                loadLines((Integer) item.getValue());
            }
        });
    }

    private void loadInvoices() {
        invoiceList.getItems().clear();
        int adClientID = Env.getAD_Client_ID(Env.getCtx());
        int adUserID = Env.getAD_User_ID(Env.getCtx());
        // log.warning("Querying invoices for AD_Client_ID: " + adClientID + ",
        // CreatedBy: " + adUserID);
        log.info("Loading invoices...");
        String sql = "SELECT i.C_Invoice_ID, i.DocumentNo, bp.C_BPartner_ID, bp.Name, i.TotalLines, i.GrandTotal, i.DateAcct, i.Ctrmag,bp.value "
                +
                "FROM C_Invoice i " +
                "LEFT JOIN C_BPartner bp ON (i.C_BPartner_ID = bp.C_BPartner_ID) " +
                "WHERE i.AD_Client_ID = ? AND i.CreatedBy = ? " +
                "ORDER BY i.DateAcct DESC";

        PreparedStatement pstmt = null;
        ResultSet rs = null;
        try {
            pstmt = DB.prepareStatement(sql, null);
            pstmt.setInt(1, adClientID);
            pstmt.setInt(2, adUserID);
            rs = pstmt.executeQuery();
            int clientID = Env.getAD_Client_ID(Env.getCtx());
            System.out.println("DEBUG: Querying invoices for Client ID: " + clientID);
            while (rs.next()) {
                int C_Invoice_ID = rs.getInt(1);
                String docNo = rs.getString(2);
                String codebp = rs.getString("VALUE");
                String bpName = codebp + "_" + rs.getString(4);
                String totalHT = rs.getBigDecimal(5).toPlainString();
                String totalTTC = rs.getBigDecimal(6).toPlainString();
                String dateAcct = new SimpleDateFormat("dd/MM/yyyy").format(rs.getTimestamp(7));

                ListItem item = new ListItem();
                item.setValue(C_Invoice_ID);
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
                // String magasinStr = rs.getString(6);
                // boolean isMagasin = "Y".equalsIgnoreCase(magasinStr);
                String magasinStr = rs.getString(8);
                String val = magasinStr != null ? magasinStr.trim() : "N";
                boolean isMagasin = "Y".equalsIgnoreCase(val) || "true".equalsIgnoreCase(val)
                        || "t".equalsIgnoreCase(val) || "yes".equalsIgnoreCase(val);

                Checkbox magasinChk = new Checkbox();
                magasinChk.setChecked(isMagasin);
                magasinChk.setDisabled(true);
                ListCell magasinCell = new ListCell();
                magasinCell.appendChild(magasinChk);
                item.appendChild(magasinCell);

                Button printBtn = new Button("Imprimer");
                printBtn.addEventListener(Events.ON_CLICK, e -> {
                    System.out.println("Printing Invoice: " + docNo);
                });
                ListCell printCell = new ListCell();
                printCell.appendChild(printBtn);
                item.appendChild(printCell);

                invoiceList.appendChild(item);
                System.out.println("DEBUG: Found Invoice: " + docNo);
            }
        } catch (SQLException e) {
            log.severe(e.getMessage());
        } finally {
            DB.close(rs, pstmt);
        }
    }

    private void loadLines(int C_Invoice_ID) {
        lineList.getItems().clear();

        String sql = "SELECT il.Line, p.Name, il.QtyInvoiced, il.LineNetAmt " +
                "FROM C_InvoiceLine il " +
                "JOIN M_Product p ON (il.M_Product_ID = p.M_Product_ID) " +
                "WHERE il.C_Invoice_ID = ? " +
                "ORDER BY il.Line";

        PreparedStatement pstmt = null;
        ResultSet rs = null;
        try {
            pstmt = DB.prepareStatement(sql, null);
            pstmt.setInt(1, C_Invoice_ID);
            rs = pstmt.executeQuery();
            while (rs.next()) {
                int lineNo = rs.getInt(1);
                String prodName = rs.getString(2);
                String qty = rs.getBigDecimal(3).toPlainString();
                String lineAmt = rs.getBigDecimal(4).toPlainString();

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
