<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:output method="html" encoding="UTF-8"/>
  <xsl:template match="/">
    <xsl:text disable-output-escaping='yes'>&lt;!DOCTYPE html &gt;</xsl:text>

    <html>
      <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE8" />
        <title>CUCglobal Log</title>

        <script>
          function myFunction()
          {
			var input, filter, table, tr, td, i;
			input = document.getElementById("myInput");
			filter = input.value.toUpperCase();
			table = document.getElementById("myTable");
			tr = table.getElementsByTagName("tr");
			for (i = 0; i &lt; tr.length; i++)
			{
				td = tr[i].getElementsByTagName("td")[0];
				if (td)
				{
					if (td.innerHTML.toUpperCase().indexOf(filter) &gt; -1)
					{
						tr[i].style.display = "";
					}
					else if(filter == "ALL")
					{
						tr[i].style.display = "";
					}
					else
					{
						tr[i].style.display = "none";
					}
				}
			}
          }
        </script>
        <style type="text/css">
          #data p
          {
          padding: 5px;
          color: #669;
          }
          #details th
          {
          font-size: 16px;
          font-weight: normal;
          padding: 4px 2px;
          color: #FFFFFF;
          text-align: center;
          border-right: 1px solid #9baff1;
          border-left: 1px solid #9baff1;
          border-top: 2px solid #9baff1;
          border-bottom: 2px solid #9baff1;
          }

          #details td
          {
          padding: 5px;
          color: #669;
          border-right: 1px solid #aabcfe;
          border-left: 1px solid #aabcfe;
          border-top: 1px solid #aabcfe;
          border-bottom: 1px solid #aabcfe;
          }
          #user th
          {
          font-weight: normal;
          padding: 4px 2px;
          color: #FFFFFF;
          text-align: center;
          border-right: 1px solid #9baff1;
          border-left: 1px solid #9baff1;
          border-top: 2px solid #9baff1;
          border-bottom: 2px solid #9baff1;
          }

          #user td
          {
          padding: 5px;
          color: #669;
          border-right: 1px solid #aabcfe;
          border-left: 1px solid #aabcfe;
          border-top: 1px solid #aabcfe;
          border-bottom: 1px solid #aabcfe;
          }
          
        .specialColor { 
            background-color: #01A9DB;
            color:white;
          }
       </style>
      </head>
      <body>
        <hr/>
        <div style="background:#01A9DB">
          <center>
            <font color="White" size="6">
              <span id="glowtext">Completeness &amp; Usability Check Log</span>
            </font>
            <br/>
            <font color="White" size="3">
              <span id="glowtext">Version </span>
              <xsl:value-of select="LogReport/@version"/>
            </font>
          </center>
        </div>
        <table id="user" style="margin: 10px auto;" align="center" cellspacing="0">
          <thead>
            <tr align="center">
              <th bgcolor="#01A9DB">
                <font size="4">Machine</font>
              </th>
              <th bgcolor="#01A9DB">
                <font size="4">User</font>
              </th>
              <th bgcolor="#01A9DB">
                <font size="4">TimeStamp</font>
              </th>
              <th bgcolor="#01A9DB">
                <font size="4">Filename</font>
              </th>
              <th bgcolor="#01A9DB">
                <font size="4">Pages</font>
              </th>
              <!--<th bgcolor="#01A9DB">
                <font size="4">WordCount</font>
              </th>-->
              <th bgcolor="#01A9DB">
                <font size="4">Size</font>
              </th>
            </tr>
          </thead>
          <tr align="center">
            <td>
              <font size="4">
                <xsl:value-of select="LogReport/UserInfo/@machine"/>
              </font>
            </td>
            <td>
              <font size="4">
                <xsl:value-of select="LogReport/UserInfo/@user"/>
              </font>
            </td>
            <td>
              <font size="4">
                <xsl:value-of select="LogReport/UserInfo/@stamp"/>
              </font>
            </td>
            <td>
              <font size="4">
                <xsl:value-of select="LogReport/FileInfo/@filename"/>
              </font>
            </td>
            <td>
              <font size="4">
                <xsl:value-of select="LogReport/FileInfo/@page"/>
              </font>
            </td>





            <td>
              <font size="4">
                <xsl:value-of select="LogReport/FileInfo/@size"/>
              </font>
            </td>
          </tr>
        </table>
        <span id="data">
          <xsl:if test="//AdditionalInfo">
            <p align="center">
              <font size="4">
                Note: <xsl:value-of select="//AdditionalInfo/p"/>
              </font>
            </p>
          </xsl:if>
        </span>
        <table id="details" align="center" style="margin: 10px auto;" cellspacing="0">
          <thead>
            <tr>
              <th bgcolor="#01A9DB">
                <!--<font size="4">Type</font>-->
                <select id="myInput" name="selectType" class="specialColor" onchange="myFunction()">				
                <option class="specialColor"  value="All" selected="selected"><font size="4">All Type</font></option>
                <option class="specialColor"  value="Error"><font size="4">Error</font></option>
                <option class="specialColor"  value="Warning"><font size="4">Warning</font></option>
                <option class="specialColor"  value="Info"><font size="4">Info</font></option>
                </select>
              </th>
              <th bgcolor="#01A9DB">
                <font size="4">Description</font>
              </th>
            </tr>
          </thead>
          <tbody id="myTable">
            <xsl:for-each select="LogReport/Item">
              <tr>
                <td>
                  <xsl:if test="@type='Error'">
                    <font size="4" color="red">
                      <xsl:value-of select="@type"/>
                    </font>
                  </xsl:if>
                  <xsl:if test="@type='Warning'">
                    <font size="4" color="darkorange">
                      <xsl:value-of select="@type"/>
                    </font>
                  </xsl:if>
                  <xsl:if test="@type='Info'">
                    <font size="4" color="darkgreen">
                      <xsl:value-of select="@type"/>
                    </font>
                  </xsl:if>
                  <xsl:apply-templates select="LogReport/Item"/>
                </td>
                <td>
                  <xsl:for-each select="node()">
                    <font size="4">
                      <xsl:value-of select="."/>
                      <br/>
                    </font>
                  </xsl:for-each>
                </td>
              </tr>
            </xsl:for-each>
          </tbody>
        </table>
        <table id="user" style="margin: 10px auto;" align="center" cellspacing="0" width="80%">
          <thead>
            <tr align="center">
              <th bgcolor="#01A9DB">
                <font size="4">Total Number of Error :</font>
              </th>
              <th bgcolor="#01A9DB">
                <font size="4">Total Number of Warning :</font>
              </th>
              <th bgcolor="#01A9DB">
                <font size="4">Total Number of Info :</font>
              </th>
            </tr>
          </thead>
          <tr align="center">
            <td>
              <font size="4" color="red">
                <xsl:value-of select="//Count[@type='Error']/@value"/>
              </font>
            </td>
            <td>
              <font size="4" color="darkorange">
                <xsl:value-of select="//Count[@type='Warning']/@value"/>
              </font>
            </td>
            <td>
              <font size="4" color="darkgreen">
                <xsl:value-of select="//Count[@type='Info']/@value"/>
              </font>
            </td>
          </tr>
        </table>
        <hr/>
        <marquee>
          <font size="2" face="Arial" color="blue">Copyright &#169; 2015-2018 SPi Global, Chennai, India. All rights reserved.</font>
        </marquee>
      </body>
    </html>
  </xsl:template>
</xsl:stylesheet>