<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:exsl="http://exslt.org/common" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
    xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:mods="http://www.loc.gov/mods/v3"
    xmlns:ore="http://www.openarchives.org/ore/terms/"
    xmlns:edm="http://www.europeana.eu/schemas/edm/" xmlns:dm2e="http://onto.dm2e.eu/schemas/dm2e/"
    xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:foaf="http://xmlns.com/foaf/0.1/"
    xmlns:wgs84_pos="http://www.w3.org/2003/01/geo/wgs84_pos#"
    xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/"
    xmlns:repox="http://repox.ist.utl.pt" xmlns:php="http://php.net/xsl"
    xmlns:pro="http://purl.org/spar/pro/" xmlns:eclap="http://www.eclap.eu/schema/eclap/"
    xmlns:bibo="http://purl.org/ontology/bibo/" xmlns:mo="http://purl.org/ontology/mo/"
    xmlns:rdaGr2="http://rdvocab.info/ElementsGr2/"
    xmlns:gndo="http://d-nb.info/standards/elementset/gnd#" extension-element-prefixes="exsl">

    <xsl:output method="xml" indent="yes" encoding="UTF-8"/>

    <!-- version 1.0 -->
    <!-- Julia Beck - Universitätsbibliothek JCS Frankfurt am Main -->
    <!--
        ######################################################################
        ## XSL Transformation to convert authority EDM/DM2E data in XML-Format
        ## into Solr's Update XML messages for the import with vufind.
        ## Just XSLT-version 1.0 possible in vufind!!
        ################################################################### -->

    <xsl:template match="/">
        <xsl:element name="add">            
            <xsl:for-each select="/rdf:RDF/foaf:Person | /rdf:RDF/edm:Agent">
                <xsl:call-template name="doc">
                    <xsl:with-param name="type" select="'Personal Name'"/>
                </xsl:call-template>
            </xsl:for-each>
            <xsl:for-each select="/rdf:RDF/foaf:Organization">
                <xsl:call-template name="doc">
                    <xsl:with-param name="type" select="'Corporate Name'"/>
                </xsl:call-template>
            </xsl:for-each>
            <xsl:for-each select="/rdf:RDF/edm:Event">
                <xsl:call-template name="doc">
                    <xsl:with-param name="type" select="'Event'"/>
                </xsl:call-template>
            </xsl:for-each>
        </xsl:element>
    </xsl:template>

    <xsl:template name="doc">
        <xsl:param name="type"/>

        <xsl:element name="doc">
            <xsl:element name="field">
                <xsl:attribute name="name">
                    <xsl:text>record_type</xsl:text>
                </xsl:attribute>
                <xsl:value-of select="$type"/>
            </xsl:element>

            <xsl:variable name="fullrecord">
                <xsl:element name="rdf:RDF">
                    <xsl:copy-of select="."/>
                    <xsl:for-each select="./rdaGr2:dateOfBirth/@rdf:resource | ./rdaGr2:dateOfDeath/@rdf:resource">
                        <xsl:copy-of select="../../../edm:TimeSpan[@rdf:about = current()]"/>
                    </xsl:for-each>
                </xsl:element>
            </xsl:variable>
            <xsl:element name="field">
                <xsl:attribute name="name">
                    <xsl:text>fullrecord</xsl:text>
                </xsl:attribute>
                <!-- <xsl:copy-of select="exsl:node-set($fullrecord)"/> -->
                <xsl:copy-of
                    select="php:function('VuFind::xmlAsText', exsl:node-set($fullrecord)/rdf:RDF)"/>
              </xsl:element>

            <!--maybe some key map?
            <xsl:element name="field">
                <xsl:attribute name="name">
                    <xsl:text>dprovider</xsl:text>
                </xsl:attribute>
                <xsl:value-of select="."/>
            </xsl:element>
            -->

            <xsl:for-each select="child::*">
                <xsl:choose>
                    <xsl:when test="name() = 'skos:prefLabel'">
                        <xsl:element name="field">
                            <xsl:attribute name="name">
                                <xsl:text>heading</xsl:text>
                            </xsl:attribute>
                            <xsl:value-of select="normalize-space(.)"/>
                        </xsl:element>
                    </xsl:when>
                    <xsl:when test="name() = 'skos:altLabel'">
                        <xsl:element name="field">
                            <xsl:attribute name="name">
                                <xsl:text>use_for</xsl:text>
                            </xsl:attribute>
                            <xsl:value-of select="normalize-space(.)"/>
                        </xsl:element>                        
                    </xsl:when>
                    <xsl:when test="name() = 'dc:date'">
                        <xsl:element name="field">
                            <xsl:attribute name="name">
                                <xsl:text>related_date</xsl:text>
                            </xsl:attribute>
                            <xsl:value-of select="normalize-space(.)"/>
                        </xsl:element>
                    </xsl:when>
                    <xsl:when test="name() = 'owl:sameAs'"/>
                </xsl:choose>
            </xsl:for-each>

            <xsl:choose>
                <xsl:when test="$type = 'Personal Name' or $type = 'Corporate Name'">
                    <xsl:call-template name="agentSpecific"/>
                </xsl:when>
                <xsl:when test="$type = 'Event'">
                    <xsl:call-template name="eventSpecific"/>
                </xsl:when>
                <xsl:otherwise/>
            </xsl:choose>

        </xsl:element>

    </xsl:template>

    <xsl:template name="agentSpecific">

        <xsl:element name="field">
            <xsl:attribute name="name">
                <xsl:text>id</xsl:text>
            </xsl:attribute>
            <xsl:value-of select="translate(substring-after(@rdf:about,'agent/'),'/','_')"/>
        </xsl:element>

        <xsl:for-each select="child::*">
            <xsl:choose>
                <xsl:when test="name() = 'rdaGr2:placeOfBirth'">
                    <xsl:element name="field">
                        <xsl:attribute name="name">
                            <xsl:text>birth_place</xsl:text>
                        </xsl:attribute>
                        <xsl:value-of select="normalize-space(.)"/>
                    </xsl:element>
                </xsl:when>
                <xsl:when test="name() = 'rdaGr2:placeOfDeath'">
                    <xsl:element name="field">
                        <xsl:attribute name="name">
                            <xsl:text>death_place</xsl:text>
                        </xsl:attribute>
                        <xsl:value-of select="normalize-space(.)"/>
                    </xsl:element>
                </xsl:when>
                <xsl:when
                    test="name() = 'rdaGr2:dateOfBirth' or name() = 'rdaGr2:dateOfEstablishment'">
                    <xsl:variable name="date"
                        select="substring-after(@rdf:resource, 'http://performing-arts.eu/timespan/')"/>
                    <xsl:choose>
                        <xsl:when test="$date != ''">
                            <xsl:element name="field">
                                <xsl:attribute name="name">
                                    <xsl:text>birth_date</xsl:text>
                                </xsl:attribute>
                                <xsl:value-of
                                    select="concat('[',substring-before($date,'_'),' TO ',substring-after($date,'_'),']')"
                                />
                            </xsl:element>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:message>Date not formatted correctly</xsl:message>
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:when>
                <xsl:when
                    test="name() = 'rdaGr2:dateOfDeath' or name() = 'rdaGr2:dateOfTermination'">
                    <xsl:variable name="date"
                        select="substring-after(@rdf:resource, 'http://performing-arts.eu/timespan/')"/>
                    <xsl:choose>
                        <xsl:when test="$date != ''">
                            <xsl:element name="field">
                                <xsl:attribute name="name">
                                    <xsl:text>death_date</xsl:text>
                                </xsl:attribute>
                                <xsl:value-of
                                    select="concat('[',substring-before($date,'_'),' TO ',substring-after($date,'_'),']')"
                                />
                            </xsl:element>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:message>Date not formatted correctly</xsl:message>
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:when>
                <xsl:when test="name() = 'rdaGr2:professionOrOccupation'">
                    <xsl:element name="field">
                        <xsl:attribute name="name">
                            <xsl:text>occupation</xsl:text>
                        </xsl:attribute>
                        <xsl:value-of select="normalize-space(.)"/>
                    </xsl:element>
                </xsl:when>
                <xsl:when test="name() = 'rdaGr2:biographicalInformation'">
                    <xsl:element name="field">
                        <xsl:attribute name="name">
                            <xsl:text>bioInfo</xsl:text>
                        </xsl:attribute>
                        <xsl:value-of select="normalize-space(.)"/>
                    </xsl:element>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:if test="name() != 'skos:prefLabel' and name() != 'skos:altLabel' and name() != 'dc:date' and name() != 'owl:sameAs'">
                        <xsl:message>
                            <xsl:value-of select="name()"/>
                            <xsl:value-of select="."/>
                        </xsl:message>
                    </xsl:if>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:for-each>

    </xsl:template>

    <xsl:template name="eventSpecific">

        <xsl:element name="field">
            <xsl:attribute name="name">
                <xsl:text>id</xsl:text>
            </xsl:attribute>
            <xsl:value-of select="translate(substring-after(@rdf:about,'event/'),'/','_')"/>
        </xsl:element>

        <xsl:for-each select="child::*">
            <xsl:choose>
                <xsl:when test="name() = 'edm:happenedAt'">
                    <xsl:element name="field">
                        <xsl:attribute name="name">
                            <xsl:text>place</xsl:text>
                        </xsl:attribute>
                        <xsl:value-of select="normalize-space(.)"/>
                    </xsl:element>
                </xsl:when>
                <xsl:when test="name() = 'edm:occuredAt'">
                    <xsl:variable name="date"
                        select="substring-after(@rdf:resource, 'http://performing-arts.eu/timespan/')"/>
                    <xsl:choose>
                        <xsl:when test="$date != ''">
                            <xsl:element name="field">
                                <xsl:attribute name="name">
                                    <xsl:text>date</xsl:text>
                                </xsl:attribute>
                                <xsl:value-of
                                    select="concat('[',substring-before($date,'_'),' TO ',substring-after($date,'_'),']')"
                                />
                            </xsl:element>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:message>Date not formatted correctly</xsl:message>
                        </xsl:otherwise>
                    </xsl:choose>                    
                </xsl:when>
                <xsl:otherwise>
                    <xsl:if test="name() != 'skos:prefLabel' and name() != 'skos:altLabel' and name() != 'dc:date' and name() != 'owl:sameAs'">
                        <xsl:message>
                            New field ? <xsl:value-of select="name()"/>
                        </xsl:message>
                    </xsl:if>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:for-each>
    </xsl:template>

</xsl:stylesheet>
