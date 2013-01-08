package connectivity;

import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.Locale;

import org.json.JSONObject;

import model.Documents;

public class JSONDocument extends Documents {

	public JSONDocument(model.Cours Cours, Date date, String Description,
			String Extension, String name, String path, String url) {
		super(Cours, date, Description, Extension, name, path, url);
	}
	
	public static JSONDocument fromJSONObject(JSONObject object, model.Cours cours) throws ParseException{
		JSONDocument doc = new JSONDocument(cours,
											(new SimpleDateFormat("yyyy-MM-dd", Locale.US)).parse(object.optString("date")), 
											object.optString("description"), 
											object.optString("extension"), 
											object.optString("title"), 
											object.optString("path").replace(object.optBoolean("isFolder")?
																			object.optString("title") :
																			object.optString("title") + "." + object.optString("extension")
																			, ""), 
											object.optString("url"));
		
		doc.setFolder(object.optBoolean("isFolder"));
		doc.setNotified(object.optBoolean("notified"));
		doc.setVisible(object.optBoolean("visibility"));
		
		if(!doc.isFolder()){
			doc.setSize(object.optDouble("size"));
		}
		doc.setUpdated(true);
		
		return doc;
	}

}
