
Set objFS = CreateObject("Scripting.FileSystemObject")
Set objFile = objFS.OpenTextFile(WScript.Arguments.Item(1))
Do Until objFile.AtEndOfStream
    strLine = objFile.ReadLine
	Set myRegExp = New RegExp
	myRegExp.IgnoreCase = True
	myRegExp.Pattern = WScript.Arguments.Item(0)
	Set myMatches = myRegExp.Execute(strLine)
	if myMatches.Count > 0 Then
		WScript.Echo myMatches.Item(0).SubMatches(0)
	End If
Loop