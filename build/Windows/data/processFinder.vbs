
Set objFS = CreateObject("Scripting.FileSystemObject")
Set objFile = objFS.OpenTextFile(WScript.Arguments.Item(3))

Dim myArray
myArray = Array("", "", "", "", "", "", "", "", "", "")

Dim i
i = 0

Do Until objFile.AtEndOfStream
    strLine = objFile.ReadLine
	Set regex1 = New RegExp
	regex1.IgnoreCase = True
	regex1.Pattern = WScript.Arguments.Item(0)
	Set matches1 = regex1.Execute(strLine)
	
	Set regex2 = New RegExp
	regex2.IgnoreCase = True
	regex2.Pattern = WScript.Arguments.Item(1)
	
	if matches1.Count > 0 Then
		Set matches2 = regex2.Execute(strLine)
		
		Dim found
		found = False
		For Each element In myArray
			if element = matches2.Item(0).SubMatches(0) Then
				found = True
			End If
		Next
		
		if found = False and matches2.Count > 0  Then
			myArray(i) = matches2.Item(0).SubMatches(0) 
			i = i + 1
		End If
	End If
Loop

Dim myArray1
myArray1 = Array("", "", "", "", "", "", "", "", "", "")

Set objFile1 = objFS.OpenTextFile(WScript.Arguments.Item(4))

Dim found2
found2 = False

Do Until objFile1.AtEndOfStream
    strLine = objFile1.ReadLine
	Set regex1 = New RegExp
	regex1.IgnoreCase = True
	regex1.Pattern = WScript.Arguments.Item(2)
	Set matches1 = regex1.Execute(strLine)

	if matches1.Count > 0 Then
	
		i = 0
		For Each element In myArray
			if element = matches1.Item(0).SubMatches(1) and not element = "" Then
				myArray1(i) = matches1.Item(0).SubMatches(0)
				found2 = True
			End If
			i = i + 1
		Next

	End If
Loop

if found2 = True Then

	WScript.Echo "echo The following programs must be terminated before apache can start:"
	WScript.Echo "echo ^|"
	For Each element In myArray1
		if not element = "" Then
			WScript.Echo "echo - " + element
		End If
	Next

	WScript.Echo "echo ^|"

	WScript.Echo "set /p answer=May I go ahead and taskkill them? (Y/N):"

	WScript.Echo "if %answer% == Y goto mycontin"
	WScript.Echo "if %answer% == y goto mycontin"
	WScript.Echo "if %answer% == N goto H"
	WScript.Echo "if %answer% == n goto H"

	WScript.Echo ":H"
	WScript.Echo "echo Alright don't continue..."
	WScript.Echo "ping -n 2 127.0.0.1 > nul"
	WScript.Echo "exit"

	WScript.Echo ":mycontin"

	For Each element In myArray
		if not element = "" Then
			WScript.Echo "taskkill /F /PID " + element
		End If
	Next
	
End If

if found2 = False Then

	WScript.Echo "echo ^|"
	
End If

