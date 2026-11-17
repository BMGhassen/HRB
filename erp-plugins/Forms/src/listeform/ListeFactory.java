package listeform;

import org.adempiere.webui.factory.IFormFactory;
import org.adempiere.webui.panel.ADForm;

public class ListeFactory implements IFormFactory {

	@Override
	public ADForm newFormInstance(String formName) {
		System.out.println("FormFactory called with************* " + formName);
		if (formName.contains("ListeFacture")) {
			return new ListeFacture();
		} else if (formName.contains("ListeBL")) {
			return new ListeBL();
		} else if (formName.contains("ListeBC")) {
			return new ListeBC();
		} else if (formName.contains("ListeBT")) {
			return new ListeBT();
		}
		return null;
	}

}
