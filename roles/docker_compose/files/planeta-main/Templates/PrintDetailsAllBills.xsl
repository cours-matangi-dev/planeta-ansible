<?xml version="1.0" encoding="ISO-8859-1" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:template match="/">
  <html xmlns="http://www.w3.org/1999/xhtml">
    <xsl:attribute name="xml:lang"><xsl:value-of select="$lang" /></xsl:attribute>
    <xsl:attribute name="lang"><xsl:value-of select="$lang" /></xsl:attribute>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
    <title>
        <xsl:value-of select="$preview-title" />
        <xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text>
        <xsl:value-of select="document/listbills/bill[0]/detailsbill/billfordate" />
    </title> 
    <style type="text/css" media="all">
      <xsl:value-of select="document/stylesheet" />
    </style>
  </head>
  <body>
    <div id="page">
      <xsl:apply-templates select="document/listbills" />
      <xsl:apply-templates select="document/url" />
    </div>
  </body>
  </html>
</xsl:template>

<xsl:template match="listbills">
  <xsl:for-each select="bill">  
    <xsl:if test="position() mod 4 = 1">
      <xsl:choose>
        <xsl:when test="position() = 1">
          <xsl:text disable-output-escaping="yes">&lt;table class="BillsOfPage" cellspacing="0"&gt;</xsl:text>
        </xsl:when>
        <xsl:otherwise>
          <xsl:text disable-output-escaping="yes">&lt;table class="BillsOfPage" style="page-break-before: always;" cellspacing="0"&gt;</xsl:text>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:if>

    <xsl:if test="position() mod 2 = 1"> 
      <tr>
        <td class="FamilyBill"><xsl:apply-templates select="." /></td> 
        <xsl:variable name="CurrPos">
          <xsl:value-of select="position()" />
        </xsl:variable>

        <xsl:choose>
          <xsl:when test="position() + 1 &lt; last()">      
            <td class="FamilyBill"><xsl:apply-templates select="../bill[position() = $CurrPos + 1]" /></td> 
          </xsl:when>
          <xsl:otherwise>
            <td class="FamilyBill"><xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text></td>
          </xsl:otherwise>
        </xsl:choose>
      </tr>
    </xsl:if>

    <xsl:if test="(position() mod 4 = 0) or (position() = last())">
      <xsl:text disable-output-escaping="yes">&lt;/table&gt;</xsl:text>
    </xsl:if>
  </xsl:for-each>
</xsl:template>

<xsl:template match="bill">
    <xsl:apply-templates select="detailsbill" />    
</xsl:template>

<xsl:template match="detailsbill">
  <table cellspacing="0">
    <tr> 
      <th class="FamilyName" colspan="2"><xsl:value-of select="$family" /><xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text><em><xsl:value-of select="familylastname" disable-output-escaping="yes" /></em></th>
      <th class="BillForDateLabel" colspan="2"><xsl:value-of select="$for-date" /></th>
      <th class="BillForDate"><xsl:value-of select="billfordate" /></th>
    </tr>
    <tr>
      <td class="Separator" colspan="5"><xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text></td>
    </tr>
    <tr>
      <td class="BillDate" colspan="5"><xsl:value-of select="$to" /><xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text><em><xsl:value-of select="billdate" /></em></td>
    </tr>
    <tr>
      <td class="PreviousBalance" colspan="4"><xsl:value-of select="$previous-months-balance" /></td>
      <td class="Amount"><xsl:value-of select="billpreviousbalance" /></td>
    </tr>
    <tr>
      <td class="Deposit" colspan="4"><xsl:value-of select="$advance-paid" /></td>
      <td class="Amount"><xsl:value-of select="billdeposit" /></td>
    </tr>
    <tr>
      <td class="Separator" colspan="5"><xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text></td>
    </tr>
    <tr>
      <td class="Label"><xsl:value-of select="$monthly-contribution" /></td>
      <td class="MonthYear" colspan="3"><xsl:value-of select="billmonthyear" /></td>
      <td class="Amount"><xsl:value-of select="billmonthlycontribution" /></td>
    </tr>
    <tr>
      <td class="Separator" colspan="5"><xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text></td>
    </tr>
    <tr>
      <td class="Label"><xsl:value-of select="$canteen" /></td>
      <td class="MonthYear"><xsl:value-of select="billmonthyear" /></td>
      <td class="NumberOf">(<xsl:value-of select="nbcanteenregistrations" />)</td>
      <td><xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text></td>
      <td class="Amount"><xsl:value-of select="billcanteenamount" /></td>
    </tr>
    <tr>
      <td class="Label"><xsl:value-of select="$nursery" /></td>
      <td class="MonthYear"><xsl:value-of select="billmonthyear" /></td>
      <xsl:choose>
        <xsl:when test="nbnurserydelays > 0">
          <td class="NumberOf">(<xsl:value-of select="nbnurseryregistrations" /> / <strong><xsl:value-of select="nbnurserydelays" /></strong>)</td>
        </xsl:when>
        <xsl:otherwise>
          <td class="NumberOf">(<xsl:value-of select="nbnurseryregistrations" />)</td>
        </xsl:otherwise>
      </xsl:choose>
      <td><xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text></td>
      <td class="Amount"><xsl:value-of select="billnurseryamount" /></td>
    </tr>
    <tr>
      <td class="Label"><xsl:value-of select="$canteen-without-meal" /></td>
      <td class="MonthYear"><xsl:value-of select="billmonthyear" /></td>
      <td class="NumberOf">(<xsl:value-of select="nbwithoutmeals" />)</td>
      <td><xsl:text disable-output-escaping="yes">&amp;nbsp;</xsl:text></td>
      <td class="Amount"><xsl:value-of select="billwithoutmealamount" /></td>
    </tr>
    <tr>
      <td colspan="3"></td>
      <td class="BillSubTotalLabel"><xsl:value-of select="$sub-total" /></td>
      <td class="Amount"><xsl:value-of select="billsubtotalamount" /></td>
    </tr>
    <tr>
      <td class="BillTotalLabel" colspan="4"><xsl:value-of select="$total-to-pay" /></td> 
      <td class="BillTotalAmount"><xsl:value-of select="billtotalamount" /></td> 
    </tr>
  </table>
  <xsl:if test="$infos != ''">
    <p class="BillInfos"><xsl:value-of select="$infos" /></p>
  </xsl:if>
</xsl:template>

<xsl:template match="url">
  <p id="backlink">
    <a>
      <xsl:attribute name="href"><xsl:value-of select="link" /></xsl:attribute>
      <xsl:value-of select="text" />
    </a>
  </p>
</xsl:template>

</xsl:stylesheet>