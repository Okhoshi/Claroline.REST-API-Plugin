/**
 * 
 */
package connectivity;

import java.util.Date;
import model.Cours;
import org.json.JSONObject;

/**
 * @author Quentin
 *
 */
public class JSONCours extends Cours {

	/**
	 * @param isLoaded
	 * @param officialEmail
	 * @param sysCode
	 * @param title
	 * @param titular
	 */
	public JSONCours(Date isLoaded, String officialEmail, String sysCode, String officialCode, String title, String titular) {
		super(isLoaded, officialEmail, sysCode, officialCode, title, titular);
	}
	
	public static JSONCours fromJSONObject(JSONObject object){
		JSONCours cours = new JSONCours(new Date(),
							 object.optString("officialEmail"),
							 object.optString("sysCode"),
							 object.optString("officialCode"), 
							 object.optString("title"), 
							 object.optString("titular"));
		cours.setId(object.optInt("cours_id"));
		cours.setNotified(object.optBoolean("notified"));
		cours.setUpdated(true);
		cours.setIsLoaded(new Date());
		return cours;
	}

}
