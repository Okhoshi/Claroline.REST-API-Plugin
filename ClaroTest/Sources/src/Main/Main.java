package Main;
import java.io.BufferedReader;
import java.io.Console;
import java.io.IOException;
import java.io.InputStreamReader;
import java.util.ArrayList;
import java.util.List;

import model.Annonce;
import model.Cours;
import model.Documents;

import connectivity.AllowedOperations;
import connectivity.CallbackArgs;
import connectivity.ClaroClient;

/**
 * 
 */

/**
 * @author Quentin
 *
 */
public class Main {

	private static Console mConsole = System.console();
	private static ClaroClient mClient;
	
	public static List<Cours> mCoursList;
	public static List<Documents> mDocList;
	public static List<Annonce> mAnnList;

	/**
	 * @param args
	 */
	public static void main(String[] args) {
		
		mCoursList = new ArrayList<Cours>();
		mAnnList = new ArrayList<Annonce>();
		mDocList = new ArrayList<Documents>();
		
		String login;
		String password;
		do {
		String platformURL = (args.length > 0 && args[0] != null && args[0] != "")?args[0]:readInput("Enter the platform URL : ", false);
		login = (args.length > 1 && args[1] != null && args[1] != "")?args[1]:readInput("Enter your login : ", false);
		password = (args.length > 2 && args[2] != null && args[2] != "")?args[2]:readInput("Enter your password : ", false);
		
		mClient = new ClaroClient(platformURL);
		} while(!mClient.getSessionCookie(new CallbackArgs(login, password, null, -1, AllowedOperations.authenticate)));
		mClient.Execute(new CallbackArgs(login, password, null, -1, AllowedOperations.getUserData));
		mClient.Execute(new CallbackArgs(AllowedOperations.getCourseList));
		
		boolean mContinued = true;
		String methods = "";
		for (AllowedOperations meth : AllowedOperations.values()) {
			methods += meth.name() + "\n";
		}
		
		while(mContinued){
			String meth = readInput("\nChoose the method to call ('Q' for exit) : \n" + methods + "\n", false);
			
			if(meth.equalsIgnoreCase("Q")){
				mContinued = false;
				break;
			}
			AllowedOperations op;
			try {
				op = AllowedOperations.valueOf(meth);
			} catch (IllegalArgumentException e) {
				System.err.println(meth + " is not a valid method!\n");
				continue;
			}
			CallbackArgs ca = null;
			String req = "";
			String coursTag = "";
			switch (op) {
			case getSingleAnnounce:
				for (Cours cours : mCoursList) {
					req += mCoursList.indexOf(cours) + ") " + cours.getSysCode() + "-" + cours.getTitle() + "\n";
				}
				coursTag = readInput("Choose the cours to resquest : " + req, false);
				 Cours courss;
				if((courss = mCoursList.get(Integer.valueOf(coursTag))) == null){
					System.err.println("The requested cours is not found");
					continue;
				}
				int resId = Integer.valueOf(readInput("The id of requested resource :", false));
				ca = new CallbackArgs(courss, resId, op);
				break;
			case getAnnounceList:
			case getCourseToolList:
			case getDocList:
			case updateCompleteCourse:
				for (Cours _cours : mCoursList) {
					req += mCoursList.indexOf(_cours) + ") " + _cours.getSysCode() + "-" + _cours.getTitle() + "\n";
				}
				coursTag = readInput("Choose the cours to resquest : " + req, false);
				 Cours cours;
				if((cours = mCoursList.get(Integer.valueOf(coursTag))) == null){
					System.err.println("The requested cours is not found");
					continue;
				}
				ca = new CallbackArgs(cours, op);
				break;
			case getUpdates:
			case getUserData:
			case getCourseList:
				ca = new CallbackArgs(op);
				break;
			default:
				break;
			}
			System.out.println("");
			mClient.Execute(ca);
		}
	}

	private static String readInput(String inputRequest, boolean password) {
		if(mConsole != null){
			mConsole.printf(inputRequest);
			if(password){
				return String.valueOf(mConsole.readPassword());
			} else {
				return mConsole.readLine();
			}
		} else {
			try {
				System.out.println(inputRequest);
				BufferedReader bufferedReader = new BufferedReader(new InputStreamReader(System.in));
				return bufferedReader.readLine();
			} catch (IOException e) {
				e.printStackTrace();
				return "";
			}
		}
	}

}
