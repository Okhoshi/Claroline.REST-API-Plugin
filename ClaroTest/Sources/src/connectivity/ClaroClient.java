/**
 * author : Quentin
 */
package connectivity;

import java.io.IOException;
import java.io.UnsupportedEncodingException;
import java.text.ParseException;
import java.util.Calendar;
import java.util.Date;
import java.util.GregorianCalendar;

import model.Cours;

import org.apache.http.HttpResponse;
import org.apache.http.HttpStatus;
import org.apache.http.client.ClientProtocolException;
import org.apache.http.client.CookieStore;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.client.params.HttpClientParams;
import org.apache.http.client.protocol.ClientContext;
import org.apache.http.impl.client.BasicCookieStore;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.protocol.BasicHttpContext;
import org.apache.http.util.EntityUtils;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;


/**
 * @author Quentin
 *
 */
public class ClaroClient {

	private static CookieStore cookies = new BasicCookieStore();
	private static Date cookieCreation = new Date(0);

	private String platformURL;
	
	public static boolean isValidAccount = false;
	
	public ClaroClient(String platformURL){
		this.platformURL = platformURL;
	}

	private String getResponse(boolean forAuth, CallbackArgs args) throws UnsupportedEncodingException, IOException{
		DefaultHttpClient client = new DefaultHttpClient();
		BasicHttpContext httpContext = new BasicHttpContext();
		httpContext.setAttribute(ClientContext.COOKIE_STORE, cookies);

		HttpPost postMessage;
		if(forAuth){
			postMessage = new HttpPost(platformURL + "/claroline/auth/login.php");
		} else {
			postMessage = new HttpPost(platformURL + "/module/MOBILE/" + args.resourceURL);
		}
		postMessage.addHeader("Content-Type", "application/x-www-form-urlencoded");
		postMessage.setEntity(args.entity);
		HttpClientParams.setRedirecting(postMessage.getParams(), false);

		System.out.println("Host:" + postMessage.getURI().getHost() + "\n"
				+ "Path:" + postMessage.getURI().getPath() + "\n"
				+ "Post:" + EntityUtils.toString(postMessage.getEntity()));

		HttpResponse response = client.execute(postMessage, httpContext);

		System.out.println("Status: " + response.getStatusLine().getStatusCode() + " - " + response.getStatusLine().getReasonPhrase());
		String result = null;
		
		switch(response.getStatusLine().getStatusCode()){
		case HttpStatus.SC_ACCEPTED:
		case HttpStatus.SC_OK:
		case HttpStatus.SC_MOVED_TEMPORARILY:
			result =  EntityUtils.toString(response.getEntity());
			break;
		case HttpStatus.SC_FORBIDDEN:
			invalidateClient();
		default:
			throw new NotOKResponseCode(response.getStatusLine().getStatusCode() + " - " + response.getStatusLine().getReasonPhrase());
		}
		System.out.println("Response:" + result);
		return result;

	}

	public void Execute(CallbackArgs args){
		if(isExpired()){
			if(!getSessionCookie(new CallbackArgs(args.login,
					args.password,
					AllowedOperations.authenticate))){
				System.err.println("Authentication Failed!");
				return;
			}
		}

		if(args.operation == AllowedOperations.updateCompleteCourse){
			Execute(new CallbackArgs(args.cidReq, AllowedOperations.getCourseToolList));
			Execute(new CallbackArgs(args.cidReq, AllowedOperations.getDocList));
			Execute(new CallbackArgs(args.cidReq, AllowedOperations.getAnnounceList));
		} else {
			try {
				String _res = getResponse(false, args);

				JSONArray JSONresponse;

				switch(args.operation){
				case getCourseList:
					JSONresponse = new JSONArray(_res);
					for (int i = 0; i < JSONresponse.length(); i++) {
						JSONObject object = JSONresponse.getJSONObject(i);
						JSONCours.fromJSONObject(object).saveInDB(Main.Main.mCoursList);
					}
					break;
				case getCourseToolList:
					JSONObject jsonRes = new JSONObject(_res);
					Cours cours = args.cidReq;
					cours.setAnn(jsonRes.optBoolean("isAnn"));
					cours.setAnnNotif(jsonRes.optBoolean("annNotif"));
					cours.setDnL(jsonRes.optBoolean("isDnL"));
					cours.setDnlNotif(jsonRes.optBoolean("dnlNotif"));
					cours.setNotified(true);
					cours.saveInDB(Main.Main.mCoursList);
					break;
				case getAnnounceList:
					JSONresponse = new JSONArray(_res);
					for(int i = 0; i < JSONresponse.length(); i++){
						JSONObject object = JSONresponse.getJSONObject(i);
						JSONAnnonce.fromJSONObject(object, args.cidReq).saveInDB(Main.Main.mAnnList);
					}
					break;
				case getDocList:
					JSONresponse = new JSONArray(_res);
					for(int i = 0; i < JSONresponse.length(); i++){
						JSONObject object = JSONresponse.getJSONObject(i);
						JSONDocument.fromJSONObject(object, args.cidReq).saveInDB(Main.Main.mDocList);
					}
					break;
				case getSingleAnnounce:
					JSONObject object = new JSONObject(_res);
					JSONAnnonce.fromJSONObject(object, args.cidReq).saveInDB(Main.Main.mAnnList);
					break;
				case getUpdates:
					/*if(!_res.equals("[]")){
						JSONObject JSONResp = new JSONObject(_res);
						Iterator<?> iterOnCours = JSONResp.keys();
						while(iterOnCours.hasNext()){
							Cours upCours;
							String syscode = (String) iterOnCours.next();
							if((upCours = CoursRepository.GetBySysCode(syscode)) == null){
								Execute(new CallbackArgs(AllowedOperations.getCourseList));
								if((upCours = CoursRepository.GetBySysCode(syscode)) != null){
									Execute(new CallbackArgs(upCours, AllowedOperations.updateCompleteCourse));
								}
								continue;
							} else {
								JSONObject jsonCours = JSONResp.getJSONObject(syscode);
								Iterator<?> iterOnMod = jsonCours.keys();
								while(iterOnMod.hasNext()){
									String modKey = (String) iterOnMod.next();
									if(modKey == "CLANN"){
										if(!upCours.isAnn()){
											Execute(new CallbackArgs(upCours, AllowedOperations.getCourseToolList));
											if(upCours.isAnn()){
												Execute(new CallbackArgs(upCours, AllowedOperations.getAnnounceList));
											}
											continue;
										} else {
											JSONObject jsonAnn = jsonCours.getJSONObject(modKey);
											Iterator<?> iterOnAnn = jsonAnn.keys();
											while(iterOnAnn.hasNext()){
												int resID = Integer.parseInt((String) iterOnAnn.next());
												Annonce upAnn;
												if((upAnn = AnnonceRepository.GetByRessourceId(resID, upCours.getId())) == null){
													Execute(new CallbackArgs(upCours, AllowedOperations.getAnnounceList));
												} else {
													upAnn.setDate(new SimpleDateFormat("yyyy-MM-dd HH:mm:ss",Locale.US).parse(jsonAnn.optString(String.valueOf(resID))));
													upAnn.setNotified(true);
													AnnonceRepository.Update(upAnn);
													Execute(new CallbackArgs(upCours, resID, AllowedOperations.getSingleAnnounce));
												}
											}
										}
									}
									else if(modKey == "CLDOC"){
										if(!upCours.isDnL()){
											Execute(new CallbackArgs(upCours, AllowedOperations.getCourseToolList));
											if(upCours.isDnL()){
												Execute(new CallbackArgs(upCours, AllowedOperations.getDocList));
											}
											continue;
										} else {
											JSONObject jsonDoc = jsonCours.getJSONObject(modKey);
											Iterator<?> iterOnDoc = jsonDoc.keys();
											while(iterOnDoc.hasNext()){
												String path = (String) iterOnDoc.next();
												Documents upDoc;
												if((upDoc = DocumentsRepository.GetAllByPath(path, upCours.getId()).get(0)) == null){
													Execute(new CallbackArgs(upCours, AllowedOperations.getDocList));
												} else {
													upDoc.setDate(new SimpleDateFormat("yyyy-MM-dd HH:mm:ss", Locale.US).parse(jsonDoc.optString(path)));
													upDoc.setNotified(true);
													DocumentsRepository.Update(upDoc);
												}
											}
										}
									}
								}
							}
						}
					}*/
					break;
				case getUserData:
					break;
				default:
					break;
				}

			} catch (ClientProtocolException e) {
				e.printStackTrace();
			} catch (UnsupportedEncodingException e) {
				e.printStackTrace();
			} catch (IOException e) {
				e.printStackTrace();
			} catch (IllegalStateException e) {
				e.printStackTrace();
			} catch (JSONException e) {
				e.printStackTrace();
			} catch (ParseException e) {
				e.printStackTrace();
			}
		}
	}

	public static void invalidateClient(){
		cookieCreation = new Date(0);
		cookies.clear();
		isValidAccount = false;
	}

	public static boolean isExpired(){
		GregorianCalendar temp = new GregorianCalendar();
		temp.setTime(cookieCreation);
		temp.add(Calendar.HOUR_OF_DAY, 2);
		return (new GregorianCalendar()).getTime().after(temp.getTime());
	}

	public boolean getSessionCookie(CallbackArgs args){
		try {
			boolean empty =  getResponse(true, args).isEmpty();
			if(empty){
				cookieCreation = new Date();
			}
			isValidAccount = empty;
			return empty;
		} catch (ClientProtocolException e) {
			e.printStackTrace();
		} catch (UnsupportedEncodingException e) {
			e.printStackTrace();
		} catch (IOException e) {
			e.printStackTrace();
		} catch (Exception e) {
			e.printStackTrace();
		}
		isValidAccount = false;
		return false;
	}

	class NotOKResponseCode extends IOException{

		public NotOKResponseCode(String string) {
			super(string);
		}

		/**
		 * 
		 */
		private static final long serialVersionUID = 4367959832676790410L;}
}