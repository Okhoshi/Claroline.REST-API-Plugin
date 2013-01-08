/**
 * 
 */
package connectivity;

import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.Date;

import org.json.JSONObject;

import model.Annonce;

/**
 * @author Quentin
 *
 */
public class JSONAnnonce extends Annonce {

	/**
	 * @param Cours
	 * @param date
	 * @param title
	 * @param content
	 */
	public JSONAnnonce(model.Cours Cours, Date date, String title,
			String content) {
		super(Cours, date, title, content);
	}
	
	public static JSONAnnonce fromJSONObject(JSONObject object, model.Cours cours) throws ParseException{
		JSONAnnonce annonce = new JSONAnnonce(
				cours,
				(new SimpleDateFormat("yyyy-MM-dd")).parse(object.optString("date")),
				object.optString("title"),
				object.optString("content")
				);
		
		annonce.setRessourceId(object.optInt("ressourceId"));
		annonce.setNotified(object.optBoolean("notified"));
		annonce.setVisible(object.optBoolean("visibility"));
		annonce.setUpdated(true);
		
		return annonce;
	}

}
