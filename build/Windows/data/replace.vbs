
Set objFS = CreateObject("Scripting.FileSystemObject")
Set objFile = objFS.OpenTextFile(WScript.Arguments.Item(2))
Do Until objFile.AtEndOfStream
    strLine = objFile.ReadLine
    If InStr(strLine,WScript.Arguments.Item(0))> 0 Then
    	strLine = Replace(strLine,WScript.Arguments.Item(0),WScript.Arguments.Item(1))
    End If 
    WScript.Echo strLine
Loop