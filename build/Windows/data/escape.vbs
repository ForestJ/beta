
Dim re1 : Set re1 = New RegExp
re1.Global = True
re1.Pattern = "\(" 

Dim re2 : Set re2 = New RegExp
re2.Global = True
re2.Pattern = "\)"

Dim re3 : Set re3 = New RegExp
re3.Global = True
re3.Pattern = " "

strLine = WScript.Arguments.Item(0)

strLine = re1.Replace(strLine, "^(")
strLine = re2.Replace(strLine, "^)")
WScript.Echo re3.Replace(strLine, "^ ")
