/**
 * @author Dim
 * @version 1
 */
package model;

import java.util.Calendar;
import java.util.Date;
import java.util.GregorianCalendar;
import java.util.List;


//import java.util.Date;

public class Annonce 
{
	// Variables globales : propriétés
	
	private Cours Cours;
	private Date date;
	private Date loaded;
	
	private int Id;
	private int ressourceId;
	
	private boolean notified;
	private boolean Updated;
	private boolean visibility;
	
	private String title;
	private String content;
	
	
	// Construteur // Sans boolean et sans Id et RessourceId 
	public  Annonce(Cours Cours, Date date, String title, String content)
	{
		this.Cours=Cours;
		this.date=date;
		this.notified=true;
		this.Updated=true;
		this.visibility=true;
		this.title=title;
		this.content=content;
		
	}
	
	// Methodes get
	public Cours getCours()
	{
		return this.Cours;	
	}
	public Date getDate()
	{
		return this.date;
	}
	public int getId()
	{
		return this.Id;
	}
	public int getRessourceId()
	{
		return this.ressourceId;
	}
	public String getTitle()
	{
		return this.title;
	}
	public String getContent()
	{
		return this.content;
	}
	
	
	/**
	 * @return the loaded
	 */
	public Date getLoaded() {
		return loaded;
	}

	// Methodes Booleennes
	public boolean isNotified()
	{
		return this.notified;
	}
	public boolean isUpdated()
	{
		return this.Updated;
	}
	public boolean isVisible()
	{
		return this.visibility;
	}
	
	
	// Méthodes set
	
	public void setCours(Cours Cours)
	{
		this.Cours=Cours;
	}
	public void setDate(Date date)
	{
		this.date=date;
	}
	public void setId(int Id)
	{
		this.Id=Id;
	}
	public void setRessourceId(int ressourceId)
	{
		this.ressourceId=ressourceId;
	}
	public void setTitle(String title)
	{
		this.title=title;
	}
	public void setContent(String content)
	{
		this.content=content;
	}
	public void setNotified(boolean notified)
	{
		this.notified=notified;
	}
	public void setUpdated(boolean Updated)
	{
		this.Updated=Updated;
	}
	public void setVisible(boolean visibility)
	{
		this.visibility=visibility;
	}
	
	/**
	 * @param loaded the loaded to set
	 */
	public void setLoaded(Date loaded) {
		this.loaded = loaded;
	}

	@Override
	public boolean equals(Object o){
		if(o instanceof Annonce){
			return ((Annonce) o).getCours().equals(Cours) && ((Annonce) o).getRessourceId() == ressourceId;
		}
		return false;
	}
	
	public boolean isExpired(){
		GregorianCalendar temp = new GregorianCalendar();
		temp.setTime(getLoaded());
		temp.add(Calendar.DAY_OF_YEAR, 7);
		return (new GregorianCalendar()).after(temp);
	}

	public int saveInDB(List<Annonce> fullList){
		if(fullList.contains(this)){
			fullList.remove(this);
		}
		fullList.add(this);
		
		return this.getId();
	}
}
