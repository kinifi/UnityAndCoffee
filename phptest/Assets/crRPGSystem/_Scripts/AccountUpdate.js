#pragma strict

var Skin							: GUISkin;
var RequiredText					: GUIStyle;
var website							: String = "http://localhost";
var ImgLogin						: Texture;
var ImgNew							: Texture;
var LoginArea						: Rect = Rect(0, 0, 400, 400);
var DetailsArea						: Rect = Rect(0, 0, 400, 400);
var WaitingArea						: Rect = Rect(0, 0, 150, 060);

var ErrorMessages					: String[];
var Info							: twoStrings[];

private var errorCode				: int = 0;
private var _mode					: eWindowMode = eWindowMode.Login;
private var username				: String = String.Empty;
private var password				: String = String.Empty;
private var formText				: String = String.Empty;

function DrawWindowLogin (windowID : int)
{
	var w  : float = LoginArea.width - 195;
	var w2 : float = (LoginArea.width - 45) / 2;

	GUI.Label(Rect(15, 35, 150, 30), "Username");
	GUI.Label(Rect(15, 75, 150, 30), "Password");

	if (errorCode > 0)
	GUI.Label(Rect(15, 185, LoginArea.width - 30, 50), ErrorMessages[errorCode]);

	username = GUI.TextField(Rect(180, 35, w, 30), username);
	password = GUI.PasswordField(Rect(180, 75, w, 30), password, "*"[0]);
	
	if (GUI.Button(Rect(15, 115, w2, 50), "Accept"))
	{
		GetAccountDetails();
	}
	
	if (GUI.Button(Rect(w2 + 30, 115, w2, 50), "Cancel"))
	{
		Destroy(gameObject);
	}
}

function UpdateDetails()
{
	// update record in database
	_mode = eWindowMode.Waiting;

	var form = new WWWForm();
	form.AddField( "username", username);
	form.AddField( "passw", password);
	form.AddField( "intent", "1");
	for (var f : twoStrings in Info)
	{
		if (f._field != String.Empty)
		{
			form.AddField( f._field.Trim()+"", f._value.Trim());
		}
	}
	var _web = WWW(website + "/updateacct.php", form);
	yield _web;
	if (_web.error != null)
	{
		errorCode = 5;
		_mode = eWindowMode.Details;
		return;
	} else
	{
		formText = _web.text; 
		_web.Dispose(); 
		Debug.Log(formText);
		Destroy(gameObject);
	}

	errorCode = 0;
	_mode = eWindowMode.Login;	
}

private var inputError			: String;

function IsValidEmailFormat(s: String) : boolean
{
	var chars 	: String = " ,?,|,&,%,!,<,>";
	var invalids : String[] = chars.Split(","[0]);
	
	s = s.Trim();
	var atIndex : int = s.IndexOf("@");
	var dotCom	: int = s.LastIndexOf(".");

	var result : boolean= true;
	for(var str : String in invalids)
		if (s.IndexOf(str) >= 0)
			result = false;
	
	if (result) result = (atIndex > 0);
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

function GetAccountDetails()
{
	// retrieve data from record in database
	_mode = eWindowMode.Waiting;

	var form = new WWWForm();
	form.AddField( "username", username.Trim());
	form.AddField( "passw", password.Trim());
	form.AddField( "intent", 0);
	var _web = WWW(website + "/updateacct.php", form);
	yield _web;
	if (_web.error != null)
	{
		errorCode = 5;
		_mode = eWindowMode.Login;
		return;
	} else
	{
		formText = _web.text; 
		_web.Dispose(); 
		Debug.Log("Return string when obtaining personal details: " +formText);
		
		if (formText == "2")
		{
			errorCode = 2;
			_mode = eWindowMode.Login;
		} else
		{
			var args : String[] = formText.Split("|"[0]);
			for (var i : int = 0; i < args.length; i++)
			{
				Info[i]._value = args[i].Trim();
			}
			errorCode = 0;
			_mode = eWindowMode.Details;
		}
	}
}

function DrawWindowDetails (windowID : int)
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
			Info[i]._value = GUI.PasswordField(thispos, Info[i]._value, Info[i]._passwordChar[0]);
		else
			Info[i]._value = GUI.TextField(thispos, Info[i]._value);
	}

	thispos.y += 45;
	w = (DetailsArea.width - 45) / 2;
	if (GUI.Button(Rect(15, thispos.y, w, 50), "Update account..."))
	{
		if (VerifyInput())
			UpdateDetails();
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
		GUI.Label(thispos, ErrorMessages[errorCode]);
	else
		GUI.Label(thispos, inputError);

}

function DrawWindowWaiting (windowID : int)
{
}

function Start()
{
	LoginArea.x   = (Screen.width - LoginArea.width)		/ 2;
	DetailsArea.x = (Screen.width - DetailsArea.width)		/ 2;
	WaitingArea.x = (Screen.width - WaitingArea.width)		/ 2;
	LoginArea.y   = (Screen.height - LoginArea.height)		/ 2;
	DetailsArea.y = (Screen.height - DetailsArea.height)	/ 2;
	WaitingArea.y = (Screen.height - WaitingArea.height)	/ 2;
}

function OnGUI ()
{
	GUI.skin = Skin;
	switch(_mode)
	{
		case eWindowMode.Login:
			GUI.Window (0, LoginArea, DrawWindowLogin, "Login to your account...");
			break;

		case eWindowMode.Details:
			GUI.Window (0, DetailsArea, DrawWindowDetails, "Update account details...");
			break;

		case eWindowMode.Waiting:
			GUI.Window (0, WaitingArea, DrawWindowWaiting, "Please wait...");
			break;
	}
}