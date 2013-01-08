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
	
	public CallbackArgs(String login,String password,Cours cidReq, int resId, AllowedOperations operation){
		this.login = login;
		this.password = password;
		this.cidReq = cidReq;
		this.operation = operation;
		this.resId = resId;
		List<NameValuePair> args = new ArrayList<NameValuePair>();
		
		switch (operation) {
		case authenticate:
			args.add(new BasicNameValuePair("login", login));
			args.add(new BasicNameValuePair("password", password));
			break;
		case getSingleAnnounce:
			args.add(new BasicNameValuePair("resId",resId + ""));
		case getAnnounceList:
		case getCourseToolList:
		case getDocList:
			args.add(new BasicNameValuePair("cidReq",cidReq.getSysCode()));
		case getCourseList:
		case getUpdates:
		case getUserData:
			args.add(new BasicNameValuePair("Method",operation.name()));
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
