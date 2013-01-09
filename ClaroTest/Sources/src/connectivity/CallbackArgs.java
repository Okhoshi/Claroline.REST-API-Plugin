package connectivity;

import java.io.UnsupportedEncodingException;
import java.util.ArrayList;
import java.util.List;

import model.Cours;

import org.apache.http.NameValuePair;
import org.apache.http.client.entity.UrlEncodedFormEntity;
import org.apache.http.message.BasicNameValuePair;

public class CallbackArgs {
	
	public String login, password;
	public Cours cidReq;
	public int resId;
	public AllowedOperations operation;
	public UrlEncodedFormEntity entity;
	public String resourceURL;
	
	public CallbackArgs(String login,String password,Cours cidReq, int resId, AllowedOperations operation){
		this.login = login;
		this.password = password;
		this.cidReq = cidReq;
		this.operation = operation;
		this.resId = resId;
		List<NameValuePair> args = new ArrayList<NameValuePair>();
		resourceURL = "/";
		
		switch (operation) {
		case authenticate:
			args.add(new BasicNameValuePair("login", login));
			args.add(new BasicNameValuePair("password", password));
			break;
		case getSingleAnnounce:
			resourceURL = "CLANN/getSingleResource/" + cidReq.getSysCode() + "/" + resId + "/";
			break;
		case getAnnounceList:
			resourceURL = "CLANN/getResourcesList/" + cidReq.getSysCode() + "/";
			break;
		case getDocList:
			resourceURL = "CLDOC/getResourcesList/" + cidReq.getSysCode() + resourceURL;
			break;
		case getCourseToolList:
			resourceURL = cidReq.getSysCode() + resourceURL;
		case getCourseList:
		case getUpdates:
		case getUserData:
			resourceURL = "User/" + operation.name() + "/" + resourceURL;
			break;
		default:
			break;
		}
		
		try {
			entity = new UrlEncodedFormEntity(args);
		} catch (UnsupportedEncodingException e) {
			e.printStackTrace();
		}
	}
	
	public CallbackArgs(String login, String password, AllowedOperations operation){
		this(login, password, null, -1, operation);
	}
	
	public CallbackArgs(Cours cidReq, int resId, AllowedOperations operation){
		this("","",cidReq, resId, operation);
	}

	public CallbackArgs(Cours cidReq, AllowedOperations operation){
		this("","",cidReq, -1, operation);
	}

	public CallbackArgs(AllowedOperations operation){
		this("","",null, -1, operation);
	}
}
