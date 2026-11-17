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
import org.adempiere.webui.component.Listbox;
import org.adempiere.webui.component.ListItem;
import org.adempiere.webui.component.ListCell;
import org.adempiere.webui.component.Checkbox;
import org.zkoss.zul.North;
import org.zkoss.zul.Center;
import org.zkoss.zul.Hlayout;
import org.zkoss.zul.Vlayout;

import org.compiere.util.CLogger;
import org.zkoss.zk.ui.event.Events;

public class ListeBT extends ADForm {

    private static final long serialVersionUID = 1L;
    private static final CLogger log = CLogger.getCLogger(ListeBT.class);

    private Listbox btList = new Listbox();
    private Listbox lineList = new Listbox();

    // Tracks the currently selected BT for the export button
    private int selectedBtID = -1;
    private String selectedNumero = null;
    private Button exportBtn = new Button("Exporter CSV");

    @Override
    protected void initForm() {
        System.out.println("DEBUG ===> initForm ListeBT STARTED");
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

        // Toolbar for Buttons
        Hlayout toolbar = new Hlayout();
        toolbar.setSpacing("8px");
        toolbar.setStyle("margin-bottom: 4px;");
        northContainer.appendChild(toolbar);

        // Refresh Button
        Button refreshBtn = new Button("Actualiser");
        refreshBtn.addEventListener(Events.ON_CLICK, e -> loadBTs());
        toolbar.appendChild(refreshBtn);

        // Export CSV Button — disabled until a row is selected
        exportBtn.setDisabled(true);
        exportBtn.addEventListener(Events.ON_CLICK, e -> {
            if (selectedBtID < 0) {
                org.zkoss.zk.ui.util.Clients.showNotification(
                    "Veuillez sélectionner un BT", "warning", null, "top_right", 3000);
                return;
            }
            String safeNum = (selectedNumero != null && !selectedNumero.trim().isEmpty())
                ? selectedNumero.trim() : ("BT_" + selectedBtID);
            String fileName = "BT_" + safeNum + "_"
                + new java.text.SimpleDateFormat("yyyyMMdd_HHmmss").format(new java.util.Date()) + ".csv";
            byte[] csvBytes = BonTransfertLogic.generateCSVExport(selectedBtID, selectedNumero);
            if (csvBytes != null) {
                log.info("Exporting CSV on demand: " + fileName);
                org.zkoss.zul.Filedownload.save(csvBytes, "text/csv;charset=UTF-8", fileName);
            } else {
                log.severe("CSV export failed for BT_ID=" + selectedBtID);
                org.zkoss.zk.ui.util.Clients.showNotification(
                    "Erreur lors de l'export CSV", "error", null, "top_right", 4000);
            }
        });
        toolbar.appendChild(exportBtn);

        // BT header list
        btList.setVflex("1");
        btList.setHflex("1");
        northContainer.appendChild(btList);

        // Define columns for btList
        Listhead ih = new Listhead();

        Listheader numBTHeader = new Listheader("N° BT");
        numBTHeader.setStyle("font-weight: bold; text-align: center; background-color: #C8E6C9;");
        ih.appendChild(numBTHeader);

        Listheader dateHeader = new Listheader("Date");
        dateHeader.setStyle("font-weight: bold; text-align: center; background-color: #C8E6C9;");
        ih.appendChild(dateHeader);

        Listheader heureHeader = new Listheader("Heure");
        heureHeader.setStyle("font-weight: bold; text-align: center; background-color: #C8E6C9;");
        ih.appendChild(heureHeader);

        Listheader traiteeHeader = new Listheader("Traitée");
        traiteeHeader.setStyle("font-weight: bold; text-align: center; background-color: #C8E6C9;");
        ih.appendChild(traiteeHeader);

        btList.appendChild(ih);

        // Detail (Bottom Part)
        Center center = new Center();
        center.setAutoscroll(true);
        layout.appendChild(center);

        lineList.setVflex("1");
        lineList.setHflex("1");
        center.appendChild(lineList);

        Listhead lh = new Listhead();

        Listheader typeHeader = new Listheader("Type");
        typeHeader.setStyle("font-weight: bold; text-align: center; background-color: #BBDEFB;");
        lh.appendChild(typeHeader);

        Listheader numDocHeader = new Listheader("N° Document");
        numDocHeader.setStyle("font-weight: bold; text-align: center; background-color: #BBDEFB;");
        lh.appendChild(numDocHeader);

        Listheader detailDateHeader = new Listheader("Date");
        detailDateHeader.setStyle("font-weight: bold; text-align: center; background-color: #BBDEFB;");
        lh.appendChild(detailDateHeader);

        Listheader clientHeader = new Listheader("Client");
        clientHeader.setStyle("font-weight: bold; text-align: center; background-color: #BBDEFB;");
        lh.appendChild(clientHeader);

        Listheader articleHeader = new Listheader("Article");
        articleHeader.setStyle("font-weight: bold; text-align: center; background-color: #BBDEFB;");
        lh.appendChild(articleHeader);

        lineList.appendChild(lh);

        loadBTs();

        // Select Event
        btList.addEventListener(Events.ON_SELECT, e -> {
            ListItem item = btList.getSelectedItem();
            if (item != null) {
                selectedBtID = (Integer) item.getValue();
                selectedNumero = (String) item.getAttribute("numero");
                exportBtn.setDisabled(false);
                loadDetails(selectedBtID);
            }
        });
    }

    private void loadBTs() {
        btList.getItems().clear();
        lineList.getItems().clear();
        selectedBtID = -1;
        selectedNumero = null;
        if (exportBtn != null) {
            exportBtn.setDisabled(true);
        }
        int adClientID = Env.getAD_Client_ID(Env.getCtx());
        int adUserID = Env.getAD_User_ID(Env.getCtx());
        log.info("Loading XX_BT for AD_Client_ID=" + adClientID + "...");

        String sql = "SELECT XX_BT_ID, DocumentNo, DateDoc, Processed FROM XX_BT WHERE AD_Client_ID = ? ORDER BY XX_BT_ID DESC";

        PreparedStatement pstmt = null;
        ResultSet rs = null;
        try {
            pstmt = DB.prepareStatement(sql, null);
            pstmt.setInt(1, adClientID);
            rs = pstmt.executeQuery();
            while (rs.next()) {
                int btID = rs.getInt(1);
                String numero = rs.getString(2);
                java.sql.Timestamp dateDoc = rs.getTimestamp(3);
                String dateStr = dateDoc != null ? new SimpleDateFormat("dd/MM/yyyy").format(dateDoc) : "";
                String heure = dateDoc != null ? new SimpleDateFormat("HH:mm:ss").format(dateDoc) : "";
                String traiteeStr = rs.getString(4);
                String traiteeVal = traiteeStr != null ? traiteeStr.trim() : "N";
                boolean isTraitee = "Y".equalsIgnoreCase(traiteeVal);

                ListItem item = new ListItem();
                item.setValue(btID);
                item.setAttribute("numero", numero);
                item.appendChild(new ListCell(numero != null ? numero : ""));
                item.appendChild(new ListCell(dateStr));
                item.appendChild(new ListCell(heure));

                // Traitée Checkbox — locked if already Y, lockable N→Y
                Checkbox traiteeChk = new Checkbox();
                traiteeChk.setChecked(isTraitee);
                traiteeChk.setDisabled(isTraitee);
                final int finalBtID = btID;
                final String finalNumero = numero;
                if (!isTraitee) {
                    traiteeChk.addEventListener(Events.ON_CHECK, ev -> {
                        String upd = "UPDATE XX_BT SET Processed = 'Y', DocStatus = 'CO', Updated = clock_timestamp(), UpdatedBy = ? WHERE XX_BT_ID = ?";
                        try {
                            PreparedStatement ps = DB.prepareStatement(upd, null);
                            ps.setInt(1, adUserID > 0 ? adUserID : 100);
                            ps.setInt(2, finalBtID);
                            ps.executeUpdate();
                            DB.close(ps);
                            log.info("Updated Processed to Y for XX_BT_ID=" + finalBtID);
                            traiteeChk.setDisabled(true);
                            // Generate CSV and send to browser for download
                            String safeNum = (finalNumero != null && !finalNumero.trim().isEmpty())
                                ? finalNumero.trim() : ("BT_" + finalBtID);
                            String fileName = "BT_" + safeNum + "_"
                                + new java.text.SimpleDateFormat("yyyyMMdd_HHmmss").format(new java.util.Date()) + ".csv";
                            byte[] csvBytes = BonTransfertLogic.generateCSVExport(finalBtID, finalNumero);
                            if (csvBytes != null) {
                                log.info("Sending CSV download to browser: " + fileName);
                                org.zkoss.zul.Filedownload.save(csvBytes, "text/csv;charset=UTF-8", fileName);
                            } else {
                                log.severe("CSV generation failed for XX_BT_ID=" + finalBtID);
                                org.zkoss.zk.ui.util.Clients.showNotification(
                                    "Erreur lors de la génération du CSV", "error", null, "top_right", 4000);
                            }
                        } catch (SQLException ex) {
                            log.severe("Error updating XX_BT Processed: " + ex.getMessage());
                            traiteeChk.setChecked(false);
                        }
                    });
                }
                ListCell traiteeCell = new ListCell();
                traiteeCell.appendChild(traiteeChk);
                item.appendChild(traiteeCell);

                btList.appendChild(item);
                System.out.println("DEBUG: Found XX_BT: " + numero);
            }
        } catch (SQLException e) {
            log.severe(e.getMessage());
        } finally {
            DB.close(rs, pstmt);
        }
    }

    private void loadDetails(int BT_ID) {
        lineList.getItems().clear();

        String sql =
            "SELECT 'BL' AS Type, " +
            "       COALESCE(io.DocumentNo, ''), " +
            "       io.MovementDate, " +
            "       COALESCE(bp.Name, ''), " +
            "       COALESCE(p.Name, '') " +
            "FROM XX_BTLine btl " +
            "LEFT JOIN M_InOut io ON (btl.M_InOut_ID = io.M_InOut_ID) " +
            "LEFT JOIN C_BPartner bp ON (btl.C_BPartner_ID = bp.C_BPartner_ID) " +
            "LEFT JOIN M_Product p ON (btl.M_Product_ID = p.M_Product_ID) " +
            "WHERE btl.XX_BT_ID = ? " +
            "ORDER BY btl.Line, btl.XX_BTLine_ID";

        PreparedStatement pstmt = null;
        ResultSet rs = null;
        try {
            pstmt = DB.prepareStatement(sql, null);
            pstmt.setInt(1, BT_ID);
            rs = pstmt.executeQuery();
            while (rs.next()) {
                String type = rs.getString(1);
                String numero = rs.getString(2);
                java.sql.Date btDate = rs.getDate(3);
                String dateStr = btDate != null ? new SimpleDateFormat("dd/MM/yyyy").format(btDate) : "";
                String client = rs.getString(4);
                String article = rs.getString(5);

                ListItem item = new ListItem();
                item.appendChild(new ListCell(type != null ? type : ""));
                item.appendChild(new ListCell(numero != null ? numero : ""));
                item.appendChild(new ListCell(dateStr));
                item.appendChild(new ListCell(client != null ? client : ""));
                item.appendChild(new ListCell(article != null ? article : ""));
                lineList.appendChild(item);
            }
        } catch (SQLException e) {
            log.severe(e.getMessage());
        } finally {
            DB.close(rs, pstmt);
        }
    }
}
