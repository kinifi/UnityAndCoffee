using UnityEngine;
using UnityEditor;
using System;
using System.IO;
using System.Text;
using System.Net;
using System.Collections;

public class UnityAndCoffee : EditorWindow {

	private bool m_isFirstTime = false;
	private string m_UserName = "UserName";
	private int m_Level = 0;

	//daily Puzzle
	private bool m_dailyLoadedPuzzle = false;
	private string m_dailyPuzzle;

	//Options
	private bool m_showOptions = false;

	[MenuItem("Window/Unity AND Coffee")]
	public static void ShowWindow()
	{
		//Show existing window instance. If one doesn't exist, make one.
		EditorWindow.GetWindow(typeof(UnityAndCoffee));
		//Debug.Log("Starting Unity and Coffee");

	}

	private void OnEnable()
	{
		//load the values needed when the window opens
		loadSavedValues();
	}

	/// <summary>
	/// Loads the saved values from PlayerPrefs
	/// </summary>
	private void loadSavedValues()
	{
		//Check to see if this the first time Unity And Coffee has been opened
		if(EditorPrefs.HasKey("firstTime"))
		{
			//set the value so we can reference if this is their first time or not
			m_isFirstTime = EditorPrefs.GetBool("firstTime");
			//Debug.Log("Not First Time");
			m_isFirstTime = true;
		}
		else
		{
			Debug.Log("First Time Playing");
		}

		if(EditorPrefs.HasKey("level"))
		{
			m_Level = EditorPrefs.GetInt("level");
		}

		if(EditorPrefs.HasKey("userName"))
		{
			m_UserName = EditorPrefs.GetString("userName");
		}

	}

	/// <summary>
	/// //is this the first time the user is opening this?
	/// </summary>
	private void firstTimeGUI()
	{
		//is this the first time the user is opening this?
		if(!m_isFirstTime)
		{
			//display the username with a text field
			GUILayout.BeginHorizontal();
				GUILayout.Label("Username:");
				m_UserName = GUILayout.TextField(m_UserName, 25);
			GUILayout.EndHorizontal();


			GUILayout.BeginHorizontal();

			//setup button when the user is done entering information
			//saves all the values for future use
			if(GUILayout.Button("Setup"))
			{
				//save the values so we can not show them later
				m_isFirstTime = true;
				EditorPrefs.SetInt("level", m_Level);
				EditorPrefs.SetString("userName", m_UserName);
				EditorPrefs.SetBool("firstTime", m_isFirstTime);
			}

			//
			if(GUILayout.Button("Load Previous Values"))
			{
				loadSavedValues();
			}

			GUILayout.EndHorizontal();
		}
	}

	/// <summary>
	/// Loads the daily challenge from the github server
	/// </summary>
	private void loadDailyChallenge() {

		//tell the user to wait in case their internet connection isn't the best
		Debug.Log("Please Wait... Loading Daily Challenge");
		//make a request for the data
		HttpWebRequest myReq = (HttpWebRequest)WebRequest.Create("https://raw.githubusercontent.com/kinifi/UnityAndCoffee/master/Test.txt");
		//get the data
		WebResponse _myResponse = myReq.GetResponse ();
		//stream from the top down and put all the data into a string
		using (Stream stream = _myResponse.GetResponseStream())
		{
			StreamReader reader = new StreamReader(stream, Encoding.UTF8);
			//close the stream at the end of the data
			m_dailyPuzzle = reader.ReadToEnd();
		}
		//close the webrequest
		_myResponse.Close();

		//double check that the data isn't null or blank
		if(m_dailyPuzzle != null)
		{
			//mark the daily puzzle is true. so we can display all the data
			m_dailyLoadedPuzzle = true;

		}
		else
		{
			Debug.LogError("puzzle is null " + m_dailyPuzzle + "  ");
		}

	}

	private void makeFolderAndScript(string folderName, string fileName, string fileExtension, string challengeText)
	{
		//create the folder
		Directory.CreateDirectory(Application.dataPath + "/" + folderName);
		//create the file with the extension and put it into the folder
		File.WriteAllText(Application.dataPath + "/" + folderName + "/" + fileName + fileExtension, challengeText);

		//refresh the project folder
		AssetDatabase.Refresh();

		//tell the user to open the file
		Debug.Log("Daily Challenge Puzzle Created! Open the " + shortDate() + fileExtension + " file and have fun! ");
	}

	private void OnGUI()
	{

		GUILayout.Space(10);
			GUILayout.Label("Unity & Coffee");
			GUILayout.Label("Twitter: #unity3d @UnityAndCoffee");
		GUILayout.Space(10);

		//Display the first TimeGUI
		firstTimeGUI();

		if(m_isFirstTime)
		{
			//Display the user data
			GUILayout.BeginHorizontal();
				//display the user name
				GUILayout.Label(m_UserName);
				//display the level for the player
				GUILayout.Label("Level: " + m_Level);
			GUILayout.EndVertical();

			GUILayout.Space(10);

			if(GUILayout.Button("Daily Puzzle"))
			{
				loadDailyChallenge();
			}


			///////////////////////////////////
			// Display Daily Puzzle
			///////////////////////////////////

			GUILayout.Space(10);

			if(m_dailyLoadedPuzzle)
			{
				GUILayout.Label("Daily Challenge Preview");
				GUILayout.TextArea(m_dailyPuzzle, 10000);

				if(GUILayout.Button("Play Daily Puzzle"))
				{
					makeFolderAndScript("DailyChallenge", shortDate(), ".cs", m_dailyPuzzle);
				}

				if(GUILayout.Button("Hide Daily Puzzle"))
				{
					m_dailyLoadedPuzzle = false;
				}
			}

			////////////////////////////////
			///////////////////////////////////////////////////////


			GUILayout.Space(10);

			GUILayout.Label("Help / Resources");

			if(GUILayout.Button("Scripting Reference"))
			{
				Debug.Log("Opening Scripting Reference");
				Application.OpenURL ("http://docs.unity3d.com/ScriptReference/index.html");
			}

			if(GUILayout.Button("Unity Manual"))
			{
				Debug.Log("Opening Unity Manual");
				Application.OpenURL ("http://docs.unity3d.com/Manual/index.html");
			}

			GUILayout.Space(10);

			////////////////////////////////////////////////////
			/// Options Display
			////////////////////////////////////////////////////

			if(!m_showOptions)
			{
				if(GUILayout.Button("Options"))
				{
					m_showOptions = true;
				}
			}
			else
			{
				if(GUILayout.Button("Hide Options"))
				{
					m_showOptions = false;
				}
			}

			if (m_showOptions)
			{
				if(GUILayout.Button("Clear User Data"))
				{
					EditorPrefs.DeleteKey("firstTime");
					EditorPrefs.DeleteKey("level");
					EditorPrefs.DeleteKey("userName");
					Debug.Log("Cleared User Data.. Reopen Window");
					this.Close();
				}
			}

			////////////////////////////////////////////////////
			/// 
			////////////////////////////////////////////////////

		}
	}

	/// <summary>
	/// Utility to create a short date in a format 
	/// </summary>
	/// <returns>The date.</returns>
	private string shortDate()
	{

		string _date;

		_date = DateTime.Now.Month + "_" + DateTime.Now.Day + "_" + DateTime.Now.Year;

		return _date;
	}

}
