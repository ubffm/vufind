<?xml version="1.0" encoding="utf-8"?>                                                                                                                          
<xsl:stylesheet version="1.0"                                                                                                                                   
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"                                                                                                              
  xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">                                                                                                      
                                                                                                                                                                
  <xsl:output method="html" indent="yes"/>                                                                                                                      
                                                                                                                                                                
  <xsl:template match="/rdf:RDF">                                                                                                                               
    <style>                                                                                                                                                     
      .edm-table { table-layout: fixed; width: 100%; }                                                                                                          
      .edm-table th, .edm-table td { vertical-align: top; }                                                                                                     
      .edm-table th.edm-tag-main { width: 25%; }                                                                                                                
      .edm-table td.edm-tag-sub { width: 25%; }                                                                                                                 
      .edm-table td.edm-value { width: 50%; }                                                                                                                   
                                                                                                                                                                
      .edm-wrap {                                                                                                                                               
        white-space: pre-wrap;                                                                                                                                  
        word-break: break-word;                                                                                                                                 
        overflow-wrap: anywhere;                                                                                                                                
        margin: 0;                                                                                                                                              
      }                                                                                                                                                         
                                                                                                                                                                
      .edm-attr {                                                                                                                                               
        color: #555;                                                                                                                                            
        font-family: monospace;                                                                                                                                 
      }                                                                                                                                                         
    </style>                                                                                                                                                    
                                                                                                                                                                
    <table class="dataview table table-striped edm-table">                                                                                                      
      <xsl:for-each select="./*">                                                                                                                               
        <xsl:variable name="tag" select="name()"/>                                                                                                              
        <xsl:variable name="count" select="count(./*) + 1"/>                                                                                                    
                                                                                                                                                                
        <tr>                                                                                                                                                    
          <th class="edm-tag edm-tag-main" rowspan="{$count}">                                                                                                  
            <div class="edm-wrap"><xsl:value-of select="$tag"/></div>                                                                                           
            <xsl:if test="./@rdf:about">                                                                                                                        
              <div class="edm-wrap edm-attr">                                                                                                                   
                <xsl:text>rdf:about="</xsl:text>                                                                                                                
                <xsl:value-of select="./@rdf:about"/>                                                                                                           
                <xsl:text>"</xsl:text>                                                                                                                          
              </div>                                                                                                                                            
            </xsl:if>                                                                                                                                           
          </th>                                                                                                                                                 
        </tr>                                                                                                                                                   
                                                                                                                                                                
        <xsl:for-each select="./*">                                                                                                                             
          <tr>                                                                                                                                                  
            <td class="edm-tag edm-tag-sub">                                                                                                                    
              <div class="edm-wrap"><xsl:value-of select="name()"/></div>                                                                                       
            </td>                                                                                                                                               
            <td class="edm-value">                                                                                                                              
              <xsl:choose>                                                                                                                                      
                <xsl:when test="normalize-space(text()) != ''">                                                                                                 
                  <div class="edm-wrap"><xsl:value-of select="text()"/></div>                                                                                   
                </xsl:when>                                                                                                                                     
                                                                                                                                                                
                <xsl:when test="@rdf:label">                                                                                                                    
                  <div class="edm-wrap edm-attr">                                                                                                               
                    <xsl:text>rdf:label="</xsl:text>                                                                                                            
                    <xsl:value-of select="./@rdf:label"/>                                                                                                       
                    <xsl:text>"</xsl:text>                                                                                                                      
                  </div>                                                                                                                                        
                  <xsl:if test="@rdf:resource">                                                                                                                 
                    <div class="edm-wrap edm-attr">                                                                                                             
                      <xsl:text>rdf:resource="</xsl:text>                                                                                                       
                      <xsl:value-of select="./@rdf:resource"/>                                                                                                  
                      <xsl:text>"</xsl:text>                                                                                                                    
                    </div>                                                                                                                                      
                  </xsl:if>                                                                                                                                     
                </xsl:when>                                                                                                                                     
                                                                                                                                                                
                <xsl:otherwise>                                                                                                                                 
                  <xsl:if test="@rdf:resource">                                                                                                                 
                    <div class="edm-wrap edm-attr">                                                                                                             
                      <xsl:text>rdf:resource="</xsl:text>                                                                                                       
                      <xsl:value-of select="./@rdf:resource"/>                                                                                                  
                      <xsl:text>"</xsl:text>                                                                                                                    
                    </div>                                                                                                                                      
                  </xsl:if>                                                                                                                                     
                </xsl:otherwise>                                                                                                                                
              </xsl:choose>                                                                                                                                     
            </td>                                                                                                                                               
          </tr>                                                                                                                                                 
        </xsl:for-each>                                                                                                                                         
      </xsl:for-each>                                                                                                                                           
    </table>                                                                                                                                                    
  </xsl:template>                                                                                                                                               
                                                                                                                                                                
</xsl:stylesheet> 
