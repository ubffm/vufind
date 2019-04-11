<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">
  <xsl:output method="html" indent="yes"/>

  <xsl:template match="/rdf:RDF">
    <table class="citation table">
      <tr class="pace-car">
        <th width="25%"/>
        <td width="25%"/>
        <td width="*"/>
      </tr>
      <xsl:for-each select="./*">
        <xsl:variable name="tag" select="name()"/>
        <xsl:variable name="count" select="count(./*)+1"/>
        <tr>
          <th class="edm-tag" rowspan="{$count}"><xsl:value-of select="$tag"/><br/>
          <div class="breaking edm-attr"><xsl:value-of select="concat('rdf:about=&quot;',./@rdf:about,'&quot;')"/></div></th>
        </tr>
        <xsl:for-each select="./*">
          <tr>
          <td class="edm-tag"><xsl:value-of select="name()"/></td>
          <td>
            <xsl:choose>
              <xsl:when test="text()">
                <div class="breaking"><xsl:value-of select="text()"/></div>
              </xsl:when>
              <xsl:otherwise>
                <div class="breaking edm-attr"><xsl:value-of select="concat('rdf:resource=&quot;',./@rdf:resource,'&quot;')"/></div>
              </xsl:otherwise>
            </xsl:choose>
          </td>
          </tr>
        </xsl:for-each>
      </xsl:for-each>
    </table>
  </xsl:template>

</xsl:stylesheet>
