#pragma strict

enum eWindowMode
{
	Choice				= 0 ,
	Login					,
	Create					,
	Details					,
	Waiting					
}

enum eFieldType
{
	Normal				= 0 ,
	Required				,
	Email					,
	RequiredEmail			,
	Verify					,
	ReadOnly					
}

class twoStrings{
	var _label						: String;
	var _field						: String;
	var _value						: String;
	var _fieldType					: eFieldType = eFieldType.Normal;
	var _verify						: int = -1;
	var _passwordChar				: String;
	var _length						: int = 15;
}

var Skin							: GUISkin;
var RequiredText					: GUIStyle;
var website							: String = "http://localhost";
var ImgLogin						: Texture;
var ImgNew							: Texture;
var ChoiceArea						: Rect = Rect(0, 0, 400, 400);
var LoginArea						: Rect = Rect(0, 0, 400, 400);
var CreateArea						: Rect = Rect(0, 0, 400, 400);
var DetailsArea						: Rect = Rect(0, 0, 400, 400);
var WaitingArea						: Rect = Rect(0, 0, 150, 060);

var ErrorMessages					: String[];
var Info							: twoStrings[];

private var inputError				: String;
private var errorCode				: int = 0;
private var _mode					: eWindowMode = eWindowMode.Choice;
private var username				: String = "";
private var password				: String = "";
private var formText				: String = String.Empty;

function DoLoginVerification()
{
	_mode = eWindowMode.Waiting;

	var form = new WWWForm();
	form.AddField( "username", username );
	form.AddField( "passw", password );
	var w = WWW(website + "/login.php", form);
	yield w;
	if (w.error != null)
	{
		_mode = eWindowMode.Login;
		return;
	} else
	{
		formText = w.text; 
		w.Dispose(); 
		Debug.Log("Error code from login verification: " + formText);
		errorCode = parseInt(formText);
	}

	// if account has been disabled, return error code 1
	// if username does not exist, return error code 2
	// if login was successful, return error code 0		
		
	if (errorCode == 0)
		DoLogin();
	else			
		_mode = eWindowMode.Login;
}

function DoLogin()
{
	// This function is executed upon successful login.
	// Do what you want to do upon successful login here and then destroy this prefab.
	// I would recommend sending a message to the server to say "This user is NOW logged in"
	// and letting the server handle it from there...

	Destroy(gameObject);
}

function DrawWindowChoice (windowID : int)
{
	if (GUI.Button(Rect(ChoiceArea.width * 0.2, ChoiceArea.height * 0.10, ChoiceArea.width * 0.6, ChoiceArea.height * 0.4), ImgLogin))
		_mode = eWindowMode.Login;

	if (GUI.Button(Rect(ChoiceArea.width * 0.2, ChoiceArea.height * 0.55, ChoiceArea.width * 0.6, ChoiceArea.height * 0.4), ImgNew))
		_mode = eWindowMode.Create;
}

function DrawWindowLogin (windowID : int)
{
	var w	: float = LoginArea.width - 195;
	var w2	: float = (LoginArea.width - 45) / 2;

	GUI.Label(Rect(15, 35, 150, 30), "Username");
	GUI.Label(Rect(15, 75, 150, 30), "Password");

	if (errorCode > 0)
		GUI.Label(Rect(15, 185, LoginArea.width - 30, 50), ErrorMessages[errorCode]);

	username = GUI.TextField(Rect(180, 35, w, 30), username);
	password = GUI.PasswordField(Rect(180, 75, w, 30), password, "*"[0]);
	
	if (GUI.Button(Rect(15, 115, w2, 50), "Accept"))
		DoLoginVerification();

	if (GUI.Button(Rect(w2 + 30, 115, w2, 50), "Cancel"))
	{
		errorCode = 0;
		_mode = eWindowMode.Choice;
	}
}

function IsValidEmailFormat(s: String) : boolean
{
	var chars 	: String = " ,?,|,&,%,!,<,>";
	var invalids : String[] = chars.Split(","[0]);
	
	s = s.Trim();
	var atIndex	: int = s.IndexOf("@");
	var lastAt	: int = s.LastIndexOf("@");
	var dotCom	: int = s.LastIndexOf(".");

	var result : boolean = true;
	for(var str : String in invalids)
		if (s.IndexOf(str) >= 0)
			result = false;
	
	if (result) result = (atIndex > 0);
	if (result) result = (atIndex == lastAt);
	if (result)	result = (dotCom > atIndex + 1);
	
	return result;
}

function VerifyInput() : boolean
{
	for (var f : twoStrings in Info)
	{
		switch (f._fieldType)
		{
			case eFieldType.Required:
				if (f._value == String.Empty)
				{
					inputError = "Field " + f._label + " is required...";
					return false;
				}
				break;
				
			case eFieldType.RequiredEmail:
				if (f._value == String.Empty)
				{
					inputError = "Field " + f._label + " is required...";
					return false;
				}
				if (!IsValidEmailFormat(f._value))
				{
					inputError = "Invalid email address for field " + f._label;  
					return false;
				}
				break;
				
			case eFieldType.Email:
				if (f._value != String.Empty)
					if (!IsValidEmailFormat(f._value))
					{
						inputError = "Invalid email address for field " + f._label;  
						return false;
					}
				break;
				
			case eFieldType.Verify:
				if (f._verify >= 0)
				{
					if (f._value != Info[f._verify]._value)
					{
						inputError = f._label + " does not match field " + Info[f._verify]._label;
						return false;
					}
				}
				break;		
		}
	}
	inputError = String.Empty;
	return true;
}

function CreateAccount()
{
	//update database with the new account info
	_mode = eWindowMode.Waiting;

	var form = new WWWForm();
	for (var f : twoStrings in Info)
	{
		if (f._field != String.Empty)
		{
			form.AddField( f._field, f._value);
		}
	}
	
	var w = WWW(website + "/newacct.php", form);
	yield w;
	if (w.error != null)
	{
		errorCode = 4;
		_mode = eWindowMode.Create;
		return;
	} else
	{
		formText = w.text; //here we return the data our PHP told us
		w.Dispose(); //clear our form in game
		Debug.Log("Error code from account creation: " + formText);
		errorCode = parseInt(formText);
		
		if (errorCode == 0)
		{
			_mode = eWindowMode.Choice;
			for (var i : int = 0 ; i < Info.length; i++)
				Info[i]._value = String.Empty;	
		} else
		{
			_mode = eWindowMode.Create;
		}
	}
}

function DrawWindowCreate (windowID : int)
{

  	var w		: float = (DetailsArea.width - 75) / 4;
	var xpos1 	: float = 15;
	var xpos2 	: float = xpos1 + 15 + w;
	var xpos3 	: float = xpos2 + 15 + w;
	var xpos4 	: float = xpos3 + 15 + w;
	
	var x : int = 1;
	var thispos : Rect = Rect(15, -30, DetailsArea.width - 30, 30);
	thispos.width = w;
	for (var i : int = 0; i < Info.length; i++)
	{
		x = (x == 1) ? 0 : 1;
		if (x == 0)
			thispos.y += 45;

		thispos.x = (x == 0) ? xpos1 : xpos3;
		if (Info[i]._fieldType == eFieldType.Required || Info[i]._fieldType == eFieldType.RequiredEmail)
			GUI.Label(thispos, Info[i]._label, RequiredText);
		else
			GUI.Label(thispos, Info[i]._label);

		thispos.x = (x == 0) ? xpos2 : xpos4;
		if (Info[i]._passwordChar != String.Empty)
			Info[i]._value = GUI.PasswordField(thispos, Info[i]._value, Info[i]._passwordChar[0], Info[i]._length);
		else
			Info[i]._value = GUI.TextField(thispos, Info[i]._value, Info[i]._length);
	}

	thispos.y += 45;
	w = (DetailsArea.width - 45) / 2;
	if (GUI.Button(Rect(15, thispos.y, w, 50), "Create account..."))
	{
		if (VerifyInput())
			CreateAccount();
	}

	if (GUI.Button(Rect(w + 30, thispos.y, w, 50), "Cancel..."))
	{
		errorCode = 0;
		_mode = eWindowMode.Choice;
	}

	thispos.x = 15;
	thispos.y += 55;
	thispos.width = DetailsArea.width - 30;
	if (errorCode > 0)
	{
		GUI.Label(thispos, ErrorMessages[errorCode]);
	}
	else
		GUI.Label(thispos, inputError);

}

function DrawWindowWaiting (windowID : int)
{
}

function Start()
{
	ChoiceArea.x  = (Screen.width - ChoiceArea.width)		/ 2;
	LoginArea.x   = (Screen.width - LoginArea.width)		/ 2;
	CreateArea.x  = (Screen.width - CreateArea.width)		/ 2;
	DetailsArea.x = (Screen.width - DetailsArea.width)		/ 2;
	WaitingArea.x = (Screen.width - WaitingArea.width)		/ 2;
	ChoiceArea.y  = (Screen.height - ChoiceArea.height)		/ 2;
	LoginArea.y   = (Screen.height - LoginArea.height)		/ 2;
	CreateArea.y  = (Screen.height - CreateArea.height)		/ 2;
	DetailsArea.y = (Screen.height - DetailsArea.height)	/ 2;
	WaitingArea.y = (Screen.height - WaitingArea.height)	/ 2;
}

function OnGUI ()
{
	GUI.skin = Skin;
	switch(_mode)
	{
		case eWindowMode.Choice:
			GUI.Window (0, ChoiceArea, DrawWindowChoice, "");
			break;

		case eWindowMode.Login:
			GUI.Window (0, LoginArea, DrawWindowLogin, "Login to your account...");
			break;

		case eWindowMode.Create:
			GUI.Window (0, DetailsArea, DrawWindowCreate, "Create new account...");
			break;

		case eWindowMode.Waiting:
			GUI.Window (0, WaitingArea, DrawWindowWaiting, "Please wait...");
			break;
	}
}