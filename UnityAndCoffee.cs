using UnityEngine;
using UnityEditor;
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
		Debug.Log("Starting Unity and Coffee");

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
			Debug.Log("Not First Time");
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

	private void loadDailyChallenge() {

		Debug.Log("Please Wait... Loading Daily Challenge");

		HttpWebRequest myReq = (HttpWebRequest)WebRequest.Create("https://raw.githubusercontent.com/kinifi/UnityAndCoffee/master/Test.txt");

		WebResponse _myResponse = myReq.GetResponse ();

		using (Stream stream = _myResponse.GetResponseStream())
		{
			StreamReader reader = new StreamReader(stream, Encoding.UTF8);
			m_dailyPuzzle = reader.ReadToEnd();
		}

		_myResponse.Close();

		if(m_dailyPuzzle != null)
		{
			m_dailyLoadedPuzzle = true;
			//Debug.Log(m_dailyPuzzle);
		}
		else
		{
			Debug.Log("puzzle is null" + m_dailyPuzzle);
		}

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
				GUILayout.Label(m_UserName);
				//display the level for the player
				GUILayout.Label("Level: " + m_Level);
			GUILayout.EndHorizontal();

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
				GUILayout.Label("Daily Challenge Code");
				GUILayout.TextArea(m_dailyPuzzle, 10000);
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
	

}
