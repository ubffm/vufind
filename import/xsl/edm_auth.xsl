<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:exsl="http://exslt.org/common" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
    xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:ore="http://www.openarchives.org/ore/terms/"
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
    <xsl:strip-space elements="*"/>

    <!-- version 1.0 -->
    <!-- Julia Beck - Universitätsbibliothek JCS Frankfurt am Main -->
    <!--
        ######################################################################
        ## XSL Transformation to convert authority EDM/DM2E data in XML-Format
        ## into Solr's Update XML messages for the import with vufind.
        ## Just XSLT-version 1.0 possible in vufind!!
        ################################################################### -->

    <!-- <xsl:param name="concepts" select="document('file:/C:/Users/Administrator/vbox_common/data/agents/add/valid_concepts.xml')"/>
    <xsl:param name="timespans" select="document('file:/C:/Users/Administrator/vbox_common/data/agents/add/valid_timespans.xml')"/> -->
    <xsl:param name="concepts"
        select="document('/var/lib/fiddk/data/authority/add/valid_concepts.xml')"/>
    <xsl:param name="timespans"
        select="document('/var/lib/fiddk/data/authority/add/valid_timespans.xml')"/>

    <!-- identity transform -->
    <xsl:template match="@* | node()">
        <xsl:apply-templates select="@* | node()"/>
    </xsl:template>

    <xsl:template match="/">
        <!-- because of namespaces it can't just be <add> -->
        <xsl:element name="add">
            <xsl:apply-templates select="@* | node()"/>
        </xsl:element>
    </xsl:template>

    <xsl:template match="foaf:Person | edm:Agent">
        <xsl:element name="doc">
            <xsl:element name="field">
                <xsl:attribute name="name">record_format</xsl:attribute>
                <xsl:text>author</xsl:text>
            </xsl:element>
            <xsl:element name="field">
                <xsl:attribute name="name">record_type</xsl:attribute>
                <xsl:text>Personal Name</xsl:text>
            </xsl:element>
            <xsl:element name="field">
                <xsl:attribute name="name">id</xsl:attribute>
                <xsl:value-of select="translate(substring-after(@rdf:about, 'agent/'), '/', '_')"/>
            </xsl:element>
            <xsl:apply-templates select="@* | node()"/>
            <xsl:element name="field">
                <xsl:attribute name="name">fullrecord</xsl:attribute>
                <xsl:copy-of select="php:function('VuFind::xmlAsText', exsl:node-set(.))"/>
                <!-- <xsl:copy-of select="exsl:node-set($full)"/> -->
            </xsl:element>
        </xsl:element>
    </xsl:template>

    <xsl:template match="foaf:Organization">
        <xsl:element name="doc">
            <xsl:element name="field">
                <xsl:attribute name="name">record_format</xsl:attribute>
                <xsl:text>author</xsl:text>
            </xsl:element>
            <xsl:element name="field">
                <xsl:attribute name="name">record_type</xsl:attribute>
                <xsl:text>Corporate Name</xsl:text>
            </xsl:element>
            <xsl:element name="field">
                <xsl:attribute name="name">id</xsl:attribute>
                <xsl:value-of select="translate(substring-after(@rdf:about, 'agent/'), '/', '_')"/>
            </xsl:element>
            <xsl:apply-templates select="@* | node()"/>
            <xsl:element name="field">
                <xsl:attribute name="name">fullrecord</xsl:attribute>
                <xsl:copy-of select="php:function('VuFind::xmlAsText', exsl:node-set(.))"/>
                <!-- <xsl:copy-of select="exsl:node-set($full)"/> -->
            </xsl:element>
        </xsl:element>
    </xsl:template>

    <xsl:template match="edm:Event">
        <xsl:element name="doc">
            <xsl:element name="field">
                <xsl:attribute name="name">record_format</xsl:attribute>
                <xsl:text>event</xsl:text>
            </xsl:element>
            <xsl:element name="field">
                <xsl:attribute name="name">record_type</xsl:attribute>
                <xsl:text>Event</xsl:text>
            </xsl:element>
            <xsl:element name="field">
                <xsl:attribute name="name">id</xsl:attribute>
                <xsl:value-of select="translate(substring-after(@rdf:about, 'event/'), '/', '_')"/>
            </xsl:element>
            <xsl:apply-templates select="@* | node()"/>
            <xsl:element name="field">
                <xsl:attribute name="name">fullrecord</xsl:attribute>
                <xsl:copy-of select="php:function('VuFind::xmlAsText', exsl:node-set(.))"/>
                <!-- <xsl:copy-of select="exsl:node-set($full)"/> -->
            </xsl:element>
        </xsl:element>
    </xsl:template>

    <xsl:template match="skos:prefLabel">
        <xsl:element name="field">
            <xsl:attribute name="name">heading</xsl:attribute>
            <xsl:value-of select="normalize-space(.)"/>
        </xsl:element>
    </xsl:template>

    <xsl:template match="skos:altLabel">
        <xsl:element name="field">
            <xsl:attribute name="name">use_for</xsl:attribute>
            <xsl:value-of select="normalize-space(.)"/>
        </xsl:element>
    </xsl:template>

    <xsl:template match="rdaGr2:professionOrOccupation">
        <xsl:variable name="occu">
            <xsl:variable name="id" select="@rdf:resource"/>
            <xsl:choose>
                <xsl:when test="$id != ''">
                    <xsl:choose>
                        <xsl:when test="starts-with($id, 'http://d-nb.info/')">
                            <xsl:variable name="conceptId"
                                select="normalize-space(concat('http://performing-arts.eu/concept/', substring-after($id, 'http://d-nb.info/')))"/>
                            <xsl:value-of
                                select="$concepts/rdf:RDF/skos:Concept[@rdf:about = $conceptId]/skos:prefLabel"
                            />
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:value-of
                                select="$concepts/rdf:RDF/skos:Concept[@rdf:about = $id]/skos:prefLabel"
                            />
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="text()"/>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        <xsl:if test="$occu != ''">
            <xsl:element name="field">
                <xsl:attribute name="name">occupation</xsl:attribute>
                <xsl:value-of select="$occu"/>
            </xsl:element>
        </xsl:if>
    </xsl:template>

    <xsl:template match="foaf:depiction">
        <xsl:element name="field">
            <xsl:attribute name="name">thumbnail</xsl:attribute>
            <xsl:value-of select="normalize-space(@rdf:resource)"/>
        </xsl:element>
    </xsl:template>

    <xsl:template match="edm:happenedAt">
        <xsl:element name="field">
            <xsl:attribute name="name">place</xsl:attribute>
            <xsl:value-of select="normalize-space(.)"/>
        </xsl:element>
    </xsl:template>
    
    <xsl:template match="edm:hasType">
        <xsl:element name="field">
            <xsl:attribute name="name">event_type</xsl:attribute>
            <xsl:value-of select="normalize-space(.)"/>
        </xsl:element>
    </xsl:template>

    <xsl:template match="edm:occuredAt">
        <xsl:variable name="date" select="substring-after(@rdf:resource, 'timespan/')"/>
        <xsl:choose>
            <xsl:when test="$date != ''">
                <xsl:element name="field">
                    <xsl:attribute name="name">date</xsl:attribute>
                    <xsl:value-of
                        select="concat('[', substring-before($date, '_'), ' TO ', substring-after($date, '_'), ']')"
                    />
                </xsl:element>
            </xsl:when>
            <xsl:otherwise>
                <xsl:message>Date not formatted correctly</xsl:message>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    
</xsl:stylesheet>
