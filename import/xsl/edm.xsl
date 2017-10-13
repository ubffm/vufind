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
    xmlns:gndo="http://d-nb.info/standards/elementset/gnd#" extension-element-prefixes="exsl">

    <xsl:output method="xml" indent="yes" encoding="UTF-8"/>

    <!-- version 1.0 -->
    <!-- Julia Beck - Universitätsbibliothek JCS Frankfurt am Main -->
    <!--
        ######################################################################
        ## XSL Transformation to convert EDM/DM2E data in XML-Format
        ## into Solr's Update XML messages for the import with vufind.
        ## Just XSLT-version 1.0 possible in vufind!!
        ################################################################### -->

    <xsl:template match="/">
        <xsl:element name="add">
            <xsl:for-each select="/rdf:RDF/ore:Aggregation">
                <xsl:variable name="aggregation" select="."/>
                <xsl:variable name="cho"
                    select="../edm:ProvidedCHO[@rdf:about=$aggregation/edm:aggregatedCHO/@rdf:resource]"/>
                <xsl:variable name="choID" select="$cho/@rdf:about"/>
                <xsl:variable name="contextuals">
                    <xsl:for-each
                        select="../edm:TimeSpan[@rdf:about = $cho/*/@rdf:resource] | ../edm:Event[@rdf:about = $cho/*/@rdf:resource]
                        | ../edm:Place[@rdf:about = $cho/*/@rdf:resource] | ../edm:Agent[@rdf:about = $cho/*/@rdf:resource]
                        | ../foaf:Person[@rdf:about = $cho/*/@rdf:resource] | ../foaf:Organization[@rdf:about = $cho/*/@rdf:resource]
                        | ../skos:Concept[@rdf:about = $cho/*/@rdf:resource] | ../edm:WebResource[@rdf:about = $aggregation/*/@rdf:resource]">
                        <xsl:copy-of select="."/>
                    </xsl:for-each>
                </xsl:variable>
                <xsl:variable name="containerTitles">
                    <xsl:for-each
                        select="../edm:ProvidedCHO[@rdf:about = $cho/dcterms:isPartOf/@rdf:resource]/dc:title">
                        <xsl:element name="title">
                            <xsl:attribute name="id">
                                <xsl:value-of select="../@rdf:about"/>
                            </xsl:attribute>
                            <xsl:value-of select="."/>
                        </xsl:element>
                    </xsl:for-each>
                </xsl:variable>
                <xsl:variable name="premierDates"
                    select="../edm:TimeSpan[@rdf:about = ../edm:Event[@rdf:about = $cho/*/@rdf:resource]/eclap:firstPerformanceDate/@rdf:resource]/skos:prefLabel"/>
                <xsl:variable name="provider"
                    select="../foaf:Organization[@rdf:about = $aggregation/edm:provider/@rdf:resource]"/>
                <xsl:variable name="dataProvider"
                    select="../foaf:Organization[@rdf:about = $aggregation/edm:dataProvider/@rdf:resource]"/>
                <xsl:variable name="fullrecord">
                    <xsl:element name="rdf:RDF">
                        <xsl:copy-of select="$aggregation"/>
                        <xsl:copy-of select="$provider"/>
                        <xsl:copy-of select="$cho"/>
                        <xsl:copy-of select="$contextuals"/>
                        <xsl:copy-of select="$dataProvider"/>
                    </xsl:element>
                </xsl:variable>
                <xsl:element name="doc">
                    <xsl:element name="field">
                        <xsl:attribute name="name">
                            <xsl:text>recordtype</xsl:text>
                        </xsl:attribute>
                        <xsl:text>edm</xsl:text>
                    </xsl:element>
                    <xsl:element name="field">
                        <xsl:attribute name="name">
                            <xsl:text>fullrecord</xsl:text>
                        </xsl:attribute>
                        <!-- <xsl:copy-of select="exsl:node-set($fullrecord)"/> -->
                        <xsl:copy-of
                            select="php:function('VuFind::xmlAsText', exsl:node-set($fullrecord)/rdf:RDF)"
                        />
                    </xsl:element>
                    <xsl:call-template name="dprovider">
                        <xsl:with-param name="dataProvider" select="$dataProvider"/>
                        <xsl:with-param name="id" select="substring-after($choID,'Record/')"/>
                        <xsl:with-param name="containerTitles" select="$containerTitles"/>
                    </xsl:call-template>
                    <xsl:call-template name="providedCHO">
                        <xsl:with-param name="cho" select="$cho"/>
                        <xsl:with-param name="choID" select="$choID"/>
                        <xsl:with-param name="contextuals" select="$contextuals"/>
                        <xsl:with-param name="containerTitles" select="$containerTitles"/>
                        <xsl:with-param name="premierDates" select="$premierDates"/>
                    </xsl:call-template>
                </xsl:element>
            </xsl:for-each>
        </xsl:element>
    </xsl:template>

    <!-- ######################### aggregation ######################### -->

    <xsl:template name="dprovider">
        <xsl:param name="id"/>
        <xsl:param name="dataProvider"/>
        <xsl:param name="containerTitles"/>
        <xsl:variable name="dProvider" select="$dataProvider/skos:prefLabel"/>
        <xsl:if test="$dProvider != ''">
            <xsl:element name="field">
                <xsl:attribute name="name">
                    <xsl:text>institution</xsl:text>
                </xsl:attribute>
                <xsl:value-of select="normalize-space($dProvider)"/>
            </xsl:element>
            <xsl:element name="field">
                <xsl:attribute name="name">
                    <xsl:text>institutionID</xsl:text>
                </xsl:attribute>
                <xsl:value-of select="normalize-space(translate(substring-after($dataProvider/@rdf:about,'agent/'),'/','_'))"/>
            </xsl:element>
            <xsl:choose>
                <xsl:when
                    test="$dProvider = 'Universitätsbibliothek Frankfurt am Main'">
                    <xsl:element name="field">
                        <xsl:attribute name="name">
                            <xsl:text>institutionFacet</xsl:text>
                        </xsl:attribute>
                        <xsl:text>0/Universitätsbibliothek Frankfurt am Main/</xsl:text>
                    </xsl:element>
                    <xsl:choose>
                        <xsl:when test="starts-with($id,'FUB_OLC_')">
                            <xsl:element name="field">
                                <xsl:attribute name="name">
                                    <xsl:text>institutionFacet</xsl:text>
                                </xsl:attribute>
                                <xsl:text>1/Universitätsbibliothek Frankfurt am Main/Online Contents/</xsl:text>
                            </xsl:element>
                            <xsl:element name="field">
                                <xsl:attribute name="name">
                                    <xsl:text>archive</xsl:text>
                                </xsl:attribute>
                                <xsl:text>0</xsl:text>
                            </xsl:element>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:element name="field">
                                <xsl:attribute name="name">
                                    <xsl:text>institutionFacet</xsl:text>
                                </xsl:attribute>
                                <xsl:text>1/Universitätsbibliothek Frankfurt am Main/Bibliotheksbestand/</xsl:text>
                            </xsl:element>
                            <xsl:element name="field">
                                <xsl:attribute name="name">
                                    <xsl:text>archive</xsl:text>
                                </xsl:attribute>
                                <xsl:text>0</xsl:text>
                            </xsl:element>
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:when>
                <xsl:when
                    test="$dProvider = 'Theaterwissenschaftliche Sammlung der Universität zu Köln'">
                    <xsl:element name="field">
                        <xsl:attribute name="name">
                            <xsl:text>institutionFacet</xsl:text>
                        </xsl:attribute>
                        <xsl:text>0/Theaterwissenschaftliche Sammlung der Universität zu Köln/</xsl:text>
                    </xsl:element>
                    <xsl:choose>
                        <xsl:when test="starts-with($id,'SLW_A_')">
                            <xsl:element name="field">
                                <xsl:attribute name="name">
                                    <xsl:text>institutionFacet</xsl:text>
                                </xsl:attribute>
                                <xsl:text>1/Theaterwissenschaftliche Sammlung der Universität zu Köln/Archivbestand/</xsl:text>
                            </xsl:element>
                            <xsl:element name="field">
                                <xsl:attribute name="name">
                                    <xsl:text>archive</xsl:text>
                                </xsl:attribute>
                                <xsl:text>1</xsl:text>
                            </xsl:element>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:element name="field">
                                <xsl:attribute name="name">
                                    <xsl:text>institutionFacet</xsl:text>
                                </xsl:attribute>
                                <xsl:text>1/Theaterwissenschaftliche Sammlung der Universität zu Köln/Bibliotheksbestand/</xsl:text>
                            </xsl:element>
                            <xsl:element name="field">
                                <xsl:attribute name="name">
                                    <xsl:text>archive</xsl:text>
                                </xsl:attribute>
                                <xsl:text>0</xsl:text>
                            </xsl:element>
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:when>
                <xsl:when test="$dProvider = 'Akademie der Künste Berlin, Bibliothek'">
                    <xsl:element name="field">
                        <xsl:attribute name="name">
                            <xsl:text>institutionFacet</xsl:text>
                        </xsl:attribute>
                        <xsl:text>0/Akademie der Künste Berlin/</xsl:text>
                    </xsl:element>
                    <xsl:element name="field">
                        <xsl:attribute name="name">
                            <xsl:text>institutionFacet</xsl:text>
                        </xsl:attribute>
                        <xsl:text>1/Akademie der Künste Berlin/Bibliotheksbestand/</xsl:text>
                    </xsl:element>
                    <xsl:element name="field">
                        <xsl:attribute name="name">
                            <xsl:text>archive</xsl:text>
                        </xsl:attribute>
                        <xsl:text>0</xsl:text>
                    </xsl:element>
                </xsl:when>
                <xsl:when
                    test="$dProvider = 'Deutsches Tanzarchiv Köln' or $dProvider = 'Mime Centrum Berlin'
                            or $dProvider = 'Tanzarchiv Leipzig' or $dProvider = 'Akademie der Künste Berlin, Archiv Darstellende Kunst'
                            or $dProvider = 'Deutsches Tanzfilminstitut Bremen'">
                    <xsl:element name="field">
                        <xsl:attribute name="name">
                            <xsl:text>archive</xsl:text>
                        </xsl:attribute>
                        <xsl:text>1</xsl:text>
                    </xsl:element>
                    <xsl:choose>
                        <xsl:when
                            test="$dProvider = 'Akademie der Künste Berlin, Archiv Darstellende Kunst'">
                            <xsl:variable name="parentid">
                                <xsl:value-of
                                    select="normalize-space(substring-after(exsl:node-set($containerTitles)/title/@id, 'Record/'))"/>
                            </xsl:variable>
                            <xsl:choose>
                                <!-- it's dance if part of "Gret Palucca (BK_1254),  Mary Wigman (BK_5822), Gerhard Bohner, Valeska Gert, Lilo Gruber, Tatjana Gsovsky,
                                        Ulrich Kessler, Johann Kresnik, Harald Kreutzberg, Susanne Linke, Maya Plisetskaya, Gert Reinholm, Tom Schilling, Joachim Schlömer, Karin Waehner,
                                        Tanztheater der Komischen Oper Berlin, Tanzsammlung" -->
                                <xsl:when test="$id = 'ADK_A_BK_1254' or $id = 'ADK_A_BK_5822' or $parentid = 'ADK_A_BK_1254' or $parentid = 'ADK_A_BK_5822'">
                                    <xsl:element name="field">
                                        <xsl:attribute name="name">
                                            <xsl:text>institutionFacet</xsl:text>
                                        </xsl:attribute>
                                        <xsl:text>0/Verbund Deutscher Tanzarchive/</xsl:text>
                                    </xsl:element>
                                    <xsl:element name="field">
                                        <xsl:attribute name="name">
                                            <xsl:text>institutionFacet</xsl:text>
                                        </xsl:attribute>
                                        <xsl:value-of
                                            select="concat('1/Verbund Deutscher Tanzarchive/', $dProvider, '/')"
                                        />
                                    </xsl:element>                                    
                                </xsl:when>
                                <xsl:otherwise>
                                    <xsl:element name="field">
                                        <xsl:attribute name="name">
                                            <xsl:text>institutionFacet</xsl:text>
                                        </xsl:attribute>
                                        <xsl:text>0/Akademie der Künste Berlin/</xsl:text>
                                    </xsl:element>
                                    <xsl:element name="field">
                                        <xsl:attribute name="name">
                                            <xsl:text>institutionFacet</xsl:text>
                                        </xsl:attribute>
                                        <xsl:text>1/Akademie der Künste Berlin/Archivbestand/</xsl:text>
                                    </xsl:element>
                                </xsl:otherwise>
                            </xsl:choose>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:element name="field">
                                <xsl:attribute name="name">
                                    <xsl:text>institutionFacet</xsl:text>
                                </xsl:attribute>
                                <xsl:text>0/Verbund Deutscher Tanzarchive/</xsl:text>
                            </xsl:element>
                            <xsl:element name="field">
                                <xsl:attribute name="name">
                                    <xsl:text>institutionFacet</xsl:text>
                                </xsl:attribute>
                                <xsl:value-of
                                    select="concat('1/Verbund Deutscher Tanzarchive/', $dProvider, '/')"
                                />
                            </xsl:element>
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:when>
                <xsl:when test="$dProvider = 'Schweizerische Theatersammlung'">
                    <xsl:element name="field">
                        <xsl:attribute name="name">
                            <xsl:text>institutionFacet</xsl:text>
                        </xsl:attribute>
                        <xsl:value-of select="concat('0/', $dProvider, '/')"/>
                    </xsl:element>
                    <xsl:choose>
                        <xsl:when test="starts-with($id,'STS_A_')">
                            <xsl:element name="field">
                                <xsl:attribute name="name">
                                    <xsl:text>institutionFacet</xsl:text>
                                </xsl:attribute>
                                <xsl:value-of select="concat('1/', $dProvider, '/Archivbestand/')"/>
                            </xsl:element>
                            <xsl:element name="field">
                                <xsl:attribute name="name">
                                    <xsl:text>archive</xsl:text>
                                </xsl:attribute>
                                <xsl:text>1</xsl:text>
                            </xsl:element>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:element name="field">
                                <xsl:attribute name="name">
                                    <xsl:text>institutionFacet</xsl:text>
                                </xsl:attribute>
                                <xsl:value-of
                                    select="concat('1/', $dProvider, '/Bibliotheksbestand/')"/>
                            </xsl:element>
                            <xsl:element name="field">
                                <xsl:attribute name="name">
                                    <xsl:text>archive</xsl:text>
                                </xsl:attribute>
                                <xsl:text>0</xsl:text>
                            </xsl:element>
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:element name="field">
                        <xsl:attribute name="name">
                            <xsl:text>institutionFacet</xsl:text>
                        </xsl:attribute>
                        <xsl:value-of select="concat('0/', $dProvider, '/')"/>
                    </xsl:element>
                    <xsl:element name="field">
                        <xsl:attribute name="name">
                            <xsl:text>archive</xsl:text>
                        </xsl:attribute>
                        <xsl:text>0</xsl:text>
                    </xsl:element>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:if>
    </xsl:template>

    <!-- ######################### providedCHO ######################### -->

    <xsl:template name="providedCHO">
        <xsl:param name="cho"/>
        <xsl:param name="choID"/>
        <xsl:param name="contextuals"/>
        <xsl:param name="containerTitles"/>
        <xsl:param name="premierDates"/>
        <xsl:variable name="id" select="substring-after($choID,'Record/')"/>
        <xsl:element name="field">
            <xsl:attribute name="name">
                <xsl:text>id</xsl:text>
            </xsl:attribute>
            <xsl:value-of select="$id"/>
        </xsl:element>
        <xsl:element name="field">
            <xsl:attribute name="name">
                <xsl:text>title</xsl:text>
            </xsl:attribute>
            <xsl:value-of select="normalize-space($cho/dc:title)"/>
        </xsl:element>
        <xsl:for-each select="$cho/dm2e:subtitle">
            <xsl:element name="field">
                <xsl:attribute name="name">
                    <xsl:text>title_sub</xsl:text>
                </xsl:attribute>
                <xsl:value-of select="normalize-space(.)"/>
            </xsl:element>
        </xsl:for-each>
        <xsl:for-each select="$cho/dcterms:alternative">
            <xsl:element name="field">
                <xsl:attribute name="name">
                    <xsl:text>title_alt</xsl:text>
                </xsl:attribute>
                <xsl:value-of select="normalize-space(.)"/>
            </xsl:element>
        </xsl:for-each>
        <xsl:for-each select="$cho/bibo:isbn">
            <xsl:element name="field">
                <xsl:attribute name="name">
                    <xsl:text>isbn</xsl:text>
                </xsl:attribute>
                <xsl:value-of select="normalize-space(.)"/>
            </xsl:element>
        </xsl:for-each>
        <xsl:for-each select="$cho/bibo:issn">
            <xsl:element name="field">
                <xsl:attribute name="name">
                    <xsl:text>issn</xsl:text>
                </xsl:attribute>
                <xsl:value-of select="normalize-space(.)"/>
            </xsl:element>
        </xsl:for-each>
        <xsl:if test="$cho/dcterms:hasPart">
            <xsl:element name="field">
                <xsl:attribute name="name">
                    <xsl:text>is_hierarchy_id</xsl:text>
                </xsl:attribute>
                <xsl:value-of select="$id"/>
            </xsl:element>
        </xsl:if>
        <xsl:for-each select="$cho/dcterms:hasPart/@rdf:resource">
            <xsl:element name="field">
                <xsl:attribute name="name">
                    <xsl:text>relatedTo</xsl:text>
                </xsl:attribute>
                <xsl:value-of select="normalize-space(substring-after(., 'Record/'))"/>
            </xsl:element>
        </xsl:for-each>
        <!-- TODO: this needs to be changed! Child with two parents -->
        <xsl:for-each select="$cho/dcterms:isPartOf[1]">
            <xsl:variable name="resourceID" select="@rdf:resource"/>
            <xsl:choose>
                <xsl:when test="$resourceID != ''">
                    <xsl:element name="field">
                        <xsl:attribute name="name">
                            <xsl:text>is_hierarchy_id</xsl:text>
                        </xsl:attribute>
                        <xsl:value-of select="$id"/>
                    </xsl:element>
                    <xsl:variable name="parentid">
                        <xsl:value-of
                            select="normalize-space(substring-after($resourceID, 'Record/'))"/>
                    </xsl:variable>
                    <xsl:if test="$parentid">
                        <xsl:element name="field">
                            <xsl:attribute name="name">
                                <xsl:text>hierarchy_parent_id</xsl:text>
                            </xsl:attribute>
                            <xsl:value-of select="$parentid"/>
                        </xsl:element>
                    </xsl:if>
                    <xsl:element name="field">
                        <xsl:attribute name="name">
                            <xsl:text>container_title</xsl:text>
                        </xsl:attribute>
                        <xsl:value-of
                            select="exsl:node-set($containerTitles)/title[@id = $resourceID]"/>
                    </xsl:element>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:element name="field">
                        <xsl:attribute name="name">
                            <xsl:text>container_title</xsl:text>
                        </xsl:attribute>
                        <xsl:value-of select="."/>
                    </xsl:element>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:for-each>
        <xsl:for-each select="$cho/dc:description">
            <xsl:element name="field">
                <xsl:attribute name="name">
                    <xsl:text>description</xsl:text>
                </xsl:attribute>
                <xsl:value-of select="normalize-space(.)"/>
            </xsl:element>
        </xsl:for-each>
        <xsl:for-each select="$cho/dc:language">
            <xsl:if test=". != 'und'">
                <xsl:element name="field">
                    <xsl:attribute name="name">
                        <xsl:text>language</xsl:text>
                    </xsl:attribute>
                    <xsl:value-of select="normalize-space(.)"/>
                </xsl:element>
            </xsl:if>
        </xsl:for-each>
        <xsl:for-each select="$cho/dc:format">
            <xsl:element name="field">
                <xsl:attribute name="name">
                    <xsl:text>description</xsl:text>
                </xsl:attribute>
                <xsl:value-of select="normalize-space(.)"/>
            </xsl:element>
        </xsl:for-each>
        <xsl:for-each select="$cho/dc:type">
            <xsl:choose>
                <xsl:when test="@rdf:resource != ''">
                    <xsl:call-template name="mapType">
                        <xsl:with-param name="type"
                            select="exsl:node-set($contextuals)/skos:Concept[@rdf:about = current()/@rdf:resource]/skos:prefLabel"
                        />
                    </xsl:call-template>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:call-template name="mapType">
                        <xsl:with-param name="type" select="."/>
                    </xsl:call-template>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:for-each>
        <xsl:variable name="thumbnail"
            select="exsl:node-set($contextuals)/edm:WebResource[dc:description = 'Cover' or dc:description = 'Thumbnail']/@rdf:about"/>
        <xsl:if test="$thumbnail != ''">
            <xsl:element name="field">
                <xsl:attribute name="name">
                    <xsl:text>thumbnail</xsl:text>
                </xsl:attribute>
                <xsl:value-of select="$thumbnail"/>
            </xsl:element>
        </xsl:if>
        <xsl:for-each
            select="exsl:node-set($contextuals)/*[starts-with(@rdf:about,'http://performing-arts.eu/agent/')]/@rdf:about">
            <xsl:element name="field">
                <xsl:attribute name="name">
                    <xsl:text>agentID</xsl:text>
                </xsl:attribute>
                <xsl:choose>
                    <xsl:when test="contains(.,'agent/gnd/')">
                        <xsl:value-of select="concat('gnd_',substring-after(.,'agent/gnd/'))"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="substring-after(.,'agent/')"/>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:element>
        </xsl:for-each>
        <xsl:for-each
            select="$cho/dcterms:spatial | $cho/eclap:performanceCity | $cho/eclap:performancePlace">
            <xsl:element name="field">
                <xsl:attribute name="name">
                    <xsl:text>geographic</xsl:text>
                </xsl:attribute>
                <xsl:choose>
                    <xsl:when
                        test="
                            not(contains(@rdf:resource, 'http://performing-arts.eu/place'))">
                        <xsl:value-of select="normalize-space(.)"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of
                            select="normalize-space(../../*[@rdf:about = current()/@rdf:resource]/skos:prefLabel)"
                        />
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:element>
        </xsl:for-each>
        <xsl:for-each select="$cho/eclap:performingArtType">
            <xsl:element name="field">
                <xsl:attribute name="name">
                    <xsl:text>genre</xsl:text>
                </xsl:attribute>
                <xsl:value-of select="normalize-space(.)"/>
            </xsl:element>
        </xsl:for-each>
        <xsl:for-each
            select="
            $cho/dc:creator | $cho/dc:contributor | $cho/dc:publisher
                | $cho/pro:author | $cho/pro:translator | $cho/pro:illustrator | $cho/pro:printer
                | $cho/bibo:editor | $cho/bibo:recipient
                | $cho/dm2e:honoree | $cho/dm2e:artist | $cho/mo:conductor
                | $cho/eclap:actor | $cho/eclap:performer | $cho/eclap:choreographer
                | $cho/eclap:composer | $cho/eclap:musician | $cho/eclap:director
                | $cho/eclap:producer | $cho/eclap:performingArtGroup | $cho/eclap:costumeDesigner
                | $cho/eclap:lightDesigner | $cho/eclap:soundDesigner | $cho/eclap:dancer
                | $cho/eclap:playwright | $cho/eclap:dramaturge | $cho/eclap:singer | $cho/eclap:adaptor
                | $cho/gndo:engraver | $cho/gndo:photographer | $cho/gndo:librettist | $cho/gndo:directorOfPhotography">
            <xsl:call-template name="indexPersons"/>
        </xsl:for-each>
        <xsl:for-each select="$cho/dcterms:issued | $cho/dcterms:created | $cho/dcterms:temporal">
            <xsl:variable name="date"
                select="substring-after(@rdf:resource, 'http://performing-arts.eu/timespan/')"/>
            <xsl:choose>
                <xsl:when test="$date != ''">
                    <xsl:element name="field">
                        <xsl:attribute name="name">
                            <xsl:text>dateSpan</xsl:text>
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
        </xsl:for-each>
        <xsl:variable name="topicList">
            <xsl:for-each select="$cho/dc:subject">
                <xsl:variable name="matchingClass"
                    select="../../*[@rdf:about = current()/@rdf:resource]"/>
                <xsl:choose>
                    <xsl:when
                        test="
                            not(contains(@rdf:resource, 'http'))">
                        <topic>
                            <xsl:value-of select="normalize-space(.)"/>
                        </topic>
                    </xsl:when>
                    <!--
                    <xsl:when test="$matchingClass/skos:broader/@rdf:resource != ''">
                        <topic>
                            <xsl:value-of select="normalize-space($matchingClass/skos:broader/@rdf:resource )"/>
                        </topic>
                        <xsl:for-each select="$matchingClass/skos:broader/@rdf:resource">
                            <xsl:variable name="broaderName"
                                select="normalize-space(../../../*[@rdf:about = current()]/skos:prefLabel)"/>
                            <xsl:variable name="generalName"
                                select="normalize-space(substring-before($matchingClass/skos:prefLabel, '-/-'))"/>
                            <xsl:if test="$broaderName != $generalName">
                                <xsl:message>moin: <xsl:value-of select="count(ancestor::*)"/></xsl:message>
                                <topic>
                                    <xsl:value-of
                                        select="concat('1/', $generalName, '/', $broaderName, '/')"
                                    />
                                </topic>
                            </xsl:if>
                        </xsl:for-each>
                    </xsl:when>
                                            -->
                    <xsl:otherwise>
                        <topic>
                            <xsl:value-of select="normalize-space($matchingClass/skos:prefLabel)"/>
                        </topic>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:for-each>
        </xsl:variable>
        <xsl:if test="$topicList != ''">
            <xsl:for-each select="exsl:node-set($topicList)/topic">
                <xsl:sort select="."/>
                <xsl:if test="not(. = preceding-sibling::*)">
                    <xsl:element name="field">
                        <xsl:attribute name="name">
                            <xsl:text>topic</xsl:text>
                        </xsl:attribute>
                        <xsl:value-of select="."/>
                    </xsl:element>
                </xsl:if>
            </xsl:for-each>
        </xsl:if>
        <xsl:for-each select="$cho/dcterms:tableOfContents">
            <xsl:element name="field">
                <xsl:attribute name="name">
                    <xsl:text>contents</xsl:text>
                </xsl:attribute>
                <xsl:value-of select="normalize-space(.)"/>
            </xsl:element>
        </xsl:for-each>
        <xsl:for-each select="$premierDates">
            <xsl:element name="field">
                <xsl:attribute name="name">
                    <xsl:text>pDates</xsl:text>
                </xsl:attribute>
                <xsl:value-of select="normalize-space(.)"/>
            </xsl:element>
        </xsl:for-each>
        <xsl:for-each select="$cho/edm:isRelatedTo/@rdf:resource">
            <xsl:element name="field">
                <xsl:attribute name="name">
                    <xsl:text>relatedTo</xsl:text>
                </xsl:attribute>
                <xsl:value-of select="normalize-space(substring-after(., 'Record/'))"/>
            </xsl:element>
        </xsl:for-each>

    </xsl:template>

    <!-- ######################################################### -->
    <!-- ######################### functions ######################### -->
    <xsl:template name="indexPersons">
        <xsl:element name="field">
            <xsl:attribute name="name">
                <xsl:choose>
                    <xsl:when test="name() = 'dc:publisher'">
                        <xsl:text>publisher</xsl:text>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:text>agent</xsl:text>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:attribute>
            <xsl:choose>
                <xsl:when test="not(@rdf:resource)">
                    <xsl:value-of select="normalize-space(.)"/>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:value-of
                        select="normalize-space(../../*[@rdf:about = current()/@rdf:resource]/skos:prefLabel)"
                    />
                </xsl:otherwise>
            </xsl:choose>
        </xsl:element>
    </xsl:template>


    <xsl:template name="getSubstrAfterLast">
        <xsl:param name="value"/>
        <xsl:param name="separator"/>
        <xsl:choose>
            <xsl:when test="contains($value, $separator)">
                <xsl:call-template name="getSubstrAfterLast">
                    <xsl:with-param name="value" select="substring-after($value, $separator)"/>
                    <xsl:with-param name="separator" select="$separator"/>
                </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="$value"/>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <xsl:template name="mapType">
        <xsl:param name="type"/>
        <xsl:variable name="mapType">
            <xsl:choose>
                <xsl:when
                    test="contains($type, 'grafik') or contains($type, 'koloriert') or contains($type,'stich') or contains($type,'Radierung') 
                or contains($type,'ithographie') or $type = 'Grafik-Konvolut' or $type = 'Punktiermanier' or contains($type,'gravure') or $type = 'Aquatinta'">
                    <xsl:text>Grafik</xsl:text>
                </xsl:when>
                <xsl:when
                    test="$type = 'Foto' or $type = 'Filmfoto' or $type = 'Szenenfoto' or $type = 'Bühnenbildfotografie'">
                    <xsl:text>Fotografie</xsl:text>
                </xsl:when>
                <xsl:when test="$type = 'Musikalien'">
                    <xsl:text>Noten</xsl:text>
                </xsl:when>
                <xsl:when test="$type = 'Schallplatte'">
                    <xsl:text>Audio</xsl:text>
                </xsl:when>
                <xsl:when test="$type = 'Inszenierung'">
                    <xsl:text>Inszenierungsbeschreibung</xsl:text>
                </xsl:when>
                <!-- has another type anyway -->
                <xsl:when
                    test="$type = 'Ton/bewegtes Bild/elektronische Kunst' or $type = 'Teil der Sammlung' or $type = 'Angewandte Kunst / Kunstgewerbe'"/>
                <xsl:when test="$type = '3D Kunst' or $type = 'Objektkunst'">
                    <xsl:text>Objekt</xsl:text>
                </xsl:when>
                <xsl:when
                    test="$type = 'Buch (gedruckt)' or $type = 'Lebensdokument' or $type = 'Druck- und Schriftgut'">
                    <xsl:text>Druckschrift</xsl:text>
                </xsl:when>
                <xsl:when
                    test="starts-with($type,'Zeichnung') or contains($type,'zeichnung') or $type = 'Gemälde' or $type = 'Malerei'">
                    <xsl:text>Zeichnung / Gemälde</xsl:text>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="$type"/>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        <xsl:if test="$mapType != ''">
            <xsl:element name="field">
                <xsl:attribute name="name">
                    <xsl:text>format</xsl:text>
                </xsl:attribute>
                <xsl:value-of select="$mapType"/>
            </xsl:element>
        </xsl:if>
    </xsl:template>

</xsl:stylesheet>
