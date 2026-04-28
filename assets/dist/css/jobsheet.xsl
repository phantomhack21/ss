<?xml version='1.0' encoding='utf-8'?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output method="html" />

<xsl:template match="/">
<table align="center" width="100%">
<tr>
   <td align="center">
	   <p>
	   <img src="../assets/dist/img/spi-global.png"/> 
	   <span style="font:bold 25px arial;margin-left: 20px">
	   <br/>
	   <br/>SPRINGER - JOBSHEET </span>
	   </p>
   </td>
</tr>
</table>
    <font size="5" color="blue"><xsl:apply-templates/></font>
</xsl:template>

<xsl:template match="//PublisherInfo">
<br/>
 <table width="75%" bgcolor="abcddc" cellpadding="10" bordercolor="black" align="center">
     <tr><th bgcolor="lightcyan" colspan="2"><font color="purple"><b><u>PUBLISHER INFO:</u></b></font></th></tr>
          
<xsl:for-each select="*">
    <tr> 
       <td width="28%"><p><b><xsl:value-of select="name(.)"/></b></p></td>
       <td width="28%"><p><font color="red"><b><xsl:value-of select="."/></b></font></p></td>
   </tr>
</xsl:for-each>
</table>
</xsl:template>

<!-- SeriesInfo-->

<xsl:template match="//SeriesInfo">
<table width="75%" bgcolor="abcddc" cellpadding="10" bordercolor="black" align="center">
<tr><td bgcolor="lightcyan" colspan="2" align="center"><font color="purple"><b><u>SERIES INFO:</u></b></font></td></tr>
<xsl:for-each select="//SeriesInfo">
<tr><td colspan="2"><b><font color="darkviolet"><xsl:value-of select="name(.)"/></font></b>
<xsl:for-each select="@*"><br/><font color="green"><i>@<xsl:value-of select="name(.)"/>: <xsl:value-of select="."/></i></font></xsl:for-each></td></tr>
</xsl:for-each>
<xsl:for-each select="//SeriesInfo//*">
<xsl:choose>
<xsl:when test="*">
<tr><td colspan="2"><b><font color="blue"><xsl:value-of select="name(.)"/></font></b>
<xsl:for-each select="@*"><br/><font color="green"><i>@<xsl:value-of select="name(.)"/>: <xsl:value-of select="."/></i></font></xsl:for-each></td></tr>
</xsl:when>
<xsl:otherwise>
<tr>
<td width="28%" valign="top"><p><b><xsl:value-of select="name(.)"/></b></p></td>
<td width="28%"><p><font color="red"><b><xsl:value-of select="."/></b></font><xsl:for-each select="@*"><br/><font color="green"><i>@<xsl:value-of select="name(.)"/>: <xsl:value-of select="."/></i></font></xsl:for-each></p></td>
</tr>
</xsl:otherwise>
</xsl:choose>
</xsl:for-each>
</table>
</xsl:template>


<!-- SubSeriesInfo-->

<xsl:template match="//SubSeriesInfo">
<table width="75%" bgcolor="abcddc" cellpadding="10" bordercolor="black" align="center">
<tr><td bgcolor="lightcyan" colspan="2" align="center"><font color="purple"><b><u>SUBSERIES INFO:</u></b></font></td></tr>
<xsl:for-each select="//SeriesInfo">
<tr><td colspan="2"><b><font color="darkviolet"><xsl:value-of select="name(.)"/></font></b>
<xsl:for-each select="@*"><br/><font color="green"><i>@<xsl:value-of select="name(.)"/>: <xsl:value-of select="."/></i></font></xsl:for-each></td></tr>
</xsl:for-each>
<xsl:for-each select="//SubSeriesInfo//*">
<xsl:choose>
<xsl:when test="*">
<tr><td colspan="2"><b><font color="blue"><xsl:value-of select="name(.)"/></font></b>
<xsl:for-each select="@*"><br/><font color="green"><i>@<xsl:value-of select="name(.)"/>: <xsl:value-of select="."/></i></font></xsl:for-each></td></tr>
</xsl:when>
<xsl:otherwise>
<tr>
<td width="28%" valign="top"><p><b><xsl:value-of select="name(.)"/></b></p></td>
<td width="28%"><p><font color="red"><b><xsl:value-of select="."/></b></font><xsl:for-each select="@*"><br/><font color="green"><i>@<xsl:value-of select="name(.)"/>: <xsl:value-of select="."/></i></font></xsl:for-each></p></td>
</tr>
</xsl:otherwise>
</xsl:choose>
</xsl:for-each>
</table>
</xsl:template>


<!-- SeriesHeader-->

<xsl:template match="//SeriesHeader">
<table width="75%" bgcolor="abcddc" cellpadding="10" bordercolor="black" align="center">
<tr><td bgcolor="lightcyan" colspan="2" align="center"><font color="purple"><b><u>SERIES HEADER:</u></b></font></td></tr>
<xsl:for-each select="//SeriesHeader">
<tr><td colspan="2"><b><font color="darkviolet"><xsl:value-of select="name(.)"/></font></b>
<xsl:for-each select="@*"><br/><font color="green"><i>@<xsl:value-of select="name(.)"/>: <xsl:value-of select="."/></i></font></xsl:for-each></td></tr>
</xsl:for-each>
<xsl:for-each select="//SeriesHeader//*">
<xsl:choose>
<xsl:when test="*">
<tr><td colspan="2"><b><font color="blue"><xsl:value-of select="name(.)"/></font></b>
<xsl:for-each select="@*"><br/><font color="green"><i>@<xsl:value-of select="name(.)"/>: <xsl:value-of select="."/></i></font></xsl:for-each></td></tr>
</xsl:when>
<xsl:otherwise>
<tr>
<td width="28%" valign="top"><p><b><xsl:value-of select="name(.)"/></b></p></td>
<td width="28%"><p><font color="red"><b><xsl:value-of select="."/></b></font><xsl:for-each select="@*"><br/><font color="green"><i>@<xsl:value-of select="name(.)"/>: <xsl:value-of select="."/></i></font></xsl:for-each></p></td>
</tr>
</xsl:otherwise>
</xsl:choose>
</xsl:for-each>
</table>
</xsl:template>

<!-- SubSeriesHeader-->

<xsl:template match="//SubSeriesHeader">
<table width="75%" bgcolor="abcddc" cellpadding="10" bordercolor="black" align="center">
<tr><td bgcolor="lightcyan" colspan="2" align="center"><font color="purple"><b><u>SUBSERIES HEADER:</u></b></font></td></tr>
<xsl:for-each select="//SubSeriesHeader">
<tr><td colspan="2"><b><font color="darkviolet"><xsl:value-of select="name(.)"/></font></b>
<xsl:for-each select="@*"><br/><font color="green"><i>@<xsl:value-of select="name(.)"/>: <xsl:value-of select="."/></i></font></xsl:for-each></td></tr>
</xsl:for-each>
<xsl:for-each select="//SubSeriesHeader//*">
<xsl:choose>
<xsl:when test="*">
<tr><td colspan="2"><b><font color="blue"><xsl:value-of select="name(.)"/></font></b>
<xsl:for-each select="@*"><br/><font color="green"><i>@<xsl:value-of select="name(.)"/>: <xsl:value-of select="."/></i></font></xsl:for-each></td></tr>
</xsl:when>
<xsl:otherwise>
<tr>
<td width="28%" valign="top"><p><b><xsl:value-of select="name(.)"/></b></p></td>
<td width="28%"><p><font color="red"><b><xsl:value-of select="."/></b></font><xsl:for-each select="@*"><br/><font color="green"><i>@<xsl:value-of select="name(.)"/>: <xsl:value-of select="."/></i></font></xsl:for-each></p></td>
</tr>
</xsl:otherwise>
</xsl:choose>
</xsl:for-each>
</table>
</xsl:template>


<!-- BookInfo-->

<xsl:template match="//BookInfo">
<table width="75%" bgcolor="abcddc" cellpadding="10" bordercolor="black" align="center">
<tr><td bgcolor="lightcyan" colspan="2" align="center"><font color="purple"><b><u>BOOK INFO:</u></b></font></td></tr>
<xsl:for-each select="//BookInfo">
<tr><td colspan="2"><b><font color="darkviolet"><xsl:value-of select="name(.)"/></font></b>
<xsl:for-each select="@*"><br/><font color="green"><i>@<xsl:value-of select="name(.)"/>: <xsl:value-of select="."/></i></font></xsl:for-each></td></tr>
</xsl:for-each>
<xsl:for-each select="//BookInfo//*">
<xsl:choose>
<xsl:when test="*">
<tr><td colspan="2"><b><font color="blue"><xsl:value-of select="name(.)"/></font></b>
<xsl:for-each select="@*"><br/><font color="green"><i>@<xsl:value-of select="name(.)"/>: <xsl:value-of select="."/></i></font></xsl:for-each></td></tr>
</xsl:when>
<xsl:otherwise>
<tr>
<td width="28%" valign="top"><p><b><xsl:value-of select="name(.)"/></b></p></td>
<td width="28%"><p><font color="red"><b><xsl:value-of select="."/></b></font><xsl:for-each select="@*"><br/><font color="green"><i>@<xsl:value-of select="name(.)"/>: <xsl:value-of select="."/></i></font></xsl:for-each></p></td>
</tr>
</xsl:otherwise>
</xsl:choose>
</xsl:for-each>
</table>
</xsl:template>


<!-- AuthorGroup-->

<xsl:template match="//AuthorGroup">
<table width="75%" bgcolor="abcddc" cellpadding="10" bordercolor="black" align="center">
<tr><td bgcolor="lightcyan" colspan="2" align="center"><font color="purple"><b><u>AUTHOR GROUP:</u></b></font></td></tr>
<xsl:for-each select="//AuthorGroup">
<tr><td colspan="2"><b><font color="darkviolet"><xsl:value-of select="name(.)"/></font></b>
<xsl:for-each select="@*"><br/><font color="green"><i>@<xsl:value-of select="name(.)"/>: <xsl:value-of select="."/></i></font></xsl:for-each></td></tr>
</xsl:for-each>
<xsl:for-each select="//AuthorGroup//*">
<xsl:choose>
<xsl:when test="*">
<tr><td colspan="2"><b><font color="blue"><xsl:value-of select="name(.)"/></font></b>
<xsl:for-each select="@*"><br/><font color="green"><i>@<xsl:value-of select="name(.)"/>: <xsl:value-of select="."/></i></font></xsl:for-each></td></tr>
</xsl:when>
<xsl:otherwise>
<tr>
<td width="28%" valign="top"><p><b><xsl:value-of select="name(.)"/></b></p></td>
<td width="28%"><p><font color="red"><b><xsl:value-of select="."/></b></font><xsl:for-each select="@*"><br/><font color="green"><i>@<xsl:value-of select="name(.)"/>: <xsl:value-of select="."/></i></font></xsl:for-each></p></td>
</tr>
</xsl:otherwise>
</xsl:choose>
</xsl:for-each>
</table>
</xsl:template>

<!-- EditorGroup-->

<xsl:template match="//EditorGroup">
<table width="75%" bgcolor="abcddc" cellpadding="10" bordercolor="black" align="center">
<tr><td bgcolor="lightcyan" colspan="2" align="center"><font color="purple"><b><u>EDITOR GROUP:</u></b></font></td></tr>
<xsl:for-each select="//EditorGroup">
<tr><td colspan="2"><b><font color="darkviolet"><xsl:value-of select="name(.)"/></font></b>
<xsl:for-each select="@*"><br/><font color="green"><i>@<xsl:value-of select="name(.)"/>: <xsl:value-of select="."/></i></font></xsl:for-each></td></tr>
</xsl:for-each>
<xsl:for-each select="//EditorGroup//*">
<xsl:choose>
<xsl:when test="*">
<tr><td colspan="2"><b><font color="blue"><xsl:value-of select="name(.)"/></font></b>
<xsl:for-each select="@*"><br/><font color="green"><i>@<xsl:value-of select="name(.)"/>: <xsl:value-of select="."/></i></font></xsl:for-each></td></tr>
</xsl:when>
<xsl:otherwise>
<tr>
<td width="28%" valign="top"><p><b><xsl:value-of select="name(.)"/></b></p></td>
<td width="28%"><p><font color="red"><b><xsl:value-of select="."/></b></font><xsl:for-each select="@*"><br/><font color="green"><i>@<xsl:value-of select="name(.)"/>: <xsl:value-of select="."/></i></font></xsl:for-each></p></td>
</tr>
</xsl:otherwise>
</xsl:choose>
</xsl:for-each>
</table>
</xsl:template>

<!-- CollaboratorGroup-->

<xsl:template match="//CollaboratorGroup">
<table width="75%" bgcolor="abcddc" cellpadding="10" bordercolor="black" align="center">
<tr><td bgcolor="lightcyan" colspan="2" align="center"><font color="purple"><b><u>COLLABORATOR GROUP:</u></b></font></td></tr>
<xsl:for-each select="//CollaboratorGroup">
<tr><td colspan="2"><b><font color="darkviolet"><xsl:value-of select="name(.)"/></font></b>
<xsl:for-each select="@*"><br/><font color="green"><i>@<xsl:value-of select="name(.)"/>: <xsl:value-of select="."/></i></font></xsl:for-each></td></tr>
</xsl:for-each>
<xsl:for-each select="//CollaboratorGroup//*">
<xsl:choose>
<xsl:when test="*">
<tr><td colspan="2"><b><font color="blue"><xsl:value-of select="name(.)"/></font></b>
<xsl:for-each select="@*"><br/><font color="green"><i>@<xsl:value-of select="name(.)"/>: <xsl:value-of select="."/></i></font></xsl:for-each></td></tr>
</xsl:when>
<xsl:otherwise>
<tr>
<td width="28%" valign="top"><p><b><xsl:value-of select="name(.)"/></b></p></td>
<td width="28%"><p><font color="red"><b><xsl:value-of select="."/></b></font><xsl:for-each select="@*"><br/><font color="green"><i>@<xsl:value-of select="name(.)"/>: <xsl:value-of select="."/></i></font></xsl:for-each></p></td>
</tr>
</xsl:otherwise>
</xsl:choose>
</xsl:for-each>
</table>
</xsl:template>


<!-- ChapterInfo -->
<xsl:template match="//ChapterInfo">
<table width="75%" bgcolor="eeeeaa" cellpadding="10" bordercolor="black" align="center">
<tr><td bgcolor="eeeecc" colspan="2" align="center"><font color="purple"><b><u>CHAPTER INFO:</u></b></font></td></tr>
<xsl:for-each select="//ChapterInfo">
<tr><td colspan="2"><b><font color="darkviolet"><xsl:value-of select="name(.)"/></font></b>
<xsl:for-each select="@*"><br/><font color="green"><i>@<xsl:value-of select="name(.)"/>: <xsl:value-of select="."/></i></font></xsl:for-each></td></tr>
</xsl:for-each>
<xsl:for-each select="//ChapterInfo//*">
<xsl:choose>
<xsl:when test="*">
<tr><td colspan="2"><b><font color="blue"><xsl:value-of select="name(.)"/></font></b>
<xsl:for-each select="@*"><br/><font color="green"><i>@<xsl:value-of select="name(.)"/>: <xsl:value-of select="."/></i></font></xsl:for-each></td></tr>
</xsl:when>
<xsl:otherwise>
<tr>
<td width="28%" valign="top"><p><b><xsl:value-of select="name(.)"/></b></p></td>
<td width="28%"><p><font color="red"><b><xsl:value-of select="."/></b></font><xsl:for-each select="@*"><br/><font color="green"><i>@<xsl:value-of select="name(.)"/>: <xsl:value-of select="."/></i></font></xsl:for-each></p></td>
</tr>
</xsl:otherwise>
</xsl:choose>
</xsl:for-each>
</table>
</xsl:template>


<!-- PartInfo -->
<xsl:template match="//PartInfo">
<table width="75%" bgcolor="eeeeaa" cellpadding="10" bordercolor="black" align="center">
<tr><td bgcolor="eeeecc" colspan="2" align="center"><font color="purple"><b><u>PART INFO:</u></b></font></td></tr>
<xsl:for-each select="//PartInfo">
<tr><td colspan="2"><b><font color="darkviolet"><xsl:value-of select="name(.)"/></font></b>
<xsl:for-each select="@*"><br/><font color="green"><i>@<xsl:value-of select="name(.)"/>: <xsl:value-of select="."/></i></font></xsl:for-each></td></tr>
</xsl:for-each>
<xsl:for-each select="//PartInfo//*">
<xsl:choose>
<xsl:when test="*">
<tr><td colspan="2"><b><font color="blue"><xsl:value-of select="name(.)"/></font></b>
<xsl:for-each select="@*"><br/><font color="green"><i>@<xsl:value-of select="name(.)"/>: <xsl:value-of select="."/></i></font></xsl:for-each></td></tr>
</xsl:when>
<xsl:otherwise>
<tr>
<td width="28%" valign="top"><p><b><xsl:value-of select="name(.)"/></b></p></td>
<td width="28%"><p><font color="red"><b><xsl:value-of select="."/></b></font><xsl:for-each select="@*"><br/><font color="green"><i>@<xsl:value-of select="name(.)"/>: <xsl:value-of select="."/></i></font></xsl:for-each></p></td>
</tr>
</xsl:otherwise>
</xsl:choose>
</xsl:for-each>
</table>
</xsl:template>


<!-- BookFeatureText-->

<xsl:template match="//BookFeatureText">
<table width="75%" bgcolor="abcddc" cellpadding="10" bordercolor="black" align="center">
<tr><td bgcolor="lightcyan" colspan="2" align="center"><font color="purple"><b><u>BOOK FEATURE TEXT:</u></b></font></td></tr>

<xsl:for-each select="//BookFeatureText">
<tr><td colspan="2"><b><font color="blue"><xsl:value-of select="name(.)"/></font>: 
<font color="red"><xsl:apply-templates/></font></b></td></tr>
</xsl:for-each>

<xsl:for-each select="//BookFeatureText//*">
<xsl:choose>
<xsl:when test="*">
<tr><td colspan="2"><b><font color="blue"><xsl:value-of select="name(.)"/></font></b>
<xsl:for-each select="@*"><br/><font color="green"><i>@<xsl:value-of select="name(.)"/>: <xsl:value-of select="."/></i></font></xsl:for-each></td></tr>
</xsl:when>
<xsl:otherwise>
<tr>
<td width="28%" valign="top"><p><b><xsl:value-of select="name(.)"/></b></p></td>
<td width="28%"><p><font color="red"><b><xsl:value-of select="."/></b></font><xsl:for-each select="@*"><br/><font color="green"><i>@<xsl:value-of select="name(.)"/>: <xsl:value-of select="."/></i></font></xsl:for-each></p></td>
</tr>
</xsl:otherwise>
</xsl:choose>
</xsl:for-each>
</table>
</xsl:template>


<xsl:template name="PreserveLineBreaks">
        <xsl:param name="text"/>
        <xsl:choose>
            <xsl:when test="contains($text,'&#xA;')">
                <xsl:value-of select="substring-before($text,'&#xA;')"/>
                <br/>
                <xsl:call-template name="PreserveLineBreaks">
                    <xsl:with-param name="text">
                        <xsl:value-of select="substring-after($text,'&#xA;')"/>
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="$text"/>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>


<!-- ProductionInfo -->
<xsl:template match="//ProductionInfo">
<table width="75%" bgcolor="abcddc" cellpadding="10" bordercolor="black" align="center">
<tr><td bgcolor="lightcyan" colspan="2" align="center"><font color="purple"><b><u>PRODUCTION INFO:</u></b></font></td></tr>

<xsl:for-each select="//ProductionInfo">
<tr><td colspan="2"><b><font color="darkviolet"><xsl:value-of select="name(.)"/></font></b>
<xsl:for-each select="@*"><br/><font color="green"><i>@<xsl:value-of select="name(.)"/>: <xsl:value-of select="."/></i></font></xsl:for-each></td></tr>
</xsl:for-each>
<xsl:for-each select="//ProductionInfo//*">
<xsl:choose>
<xsl:when test="*">
<tr><td colspan="2"><b><font color="blue"><xsl:value-of select="name(.)"/></font></b>
            <xsl:for-each select="@*"><br/><font color="green"><i>@<xsl:value-of select="name(.)"/>: <xsl:value-of select="."/></i></font></xsl:for-each></td></tr>
</xsl:when>

<xsl:otherwise>
<tr>
<td width="28%" valign="top"><p><b><xsl:value-of select="name(.)"/></b></p></td>
<td width="28%"><p><font color="red"><b><xsl:call-template name="PreserveLineBreaks">
            <xsl:with-param name="text" select="."/>
        </xsl:call-template></b></font><xsl:for-each select="@*"><br/><font color="green"><i>@<xsl:value-of select="name(.)"/>: <xsl:value-of select="."/></i></font></xsl:for-each></p></td>
</tr>
</xsl:otherwise>
</xsl:choose>
</xsl:for-each>
</table>
</xsl:template>



</xsl:stylesheet>