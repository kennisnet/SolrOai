<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/"
	xmlns:dc="http://purl.org/dc/elements/1.1/">
	<xsl:output media-type="text/xml" method="xml" indent="yes"/>

	<xsl:template match="@*|node()">
		<xsl:copy>
			<xsl:apply-templates select="@*|node()"/>
		</xsl:copy>
	</xsl:template>
	
	
	<xsl:template match="/response/result/doc">
		<oai_dc:dc xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/">
			<xsl:element name="dc:identifier">
				<xsl:value-of select="int[@name='uid']"/>
			</xsl:element>
			<xsl:element name="dc:title">
				<xsl:attribute name="xml:lang">
					<xsl:value-of select="'nl'"/>
				</xsl:attribute>
				<xsl:value-of select="str[@name='title']"/>
			</xsl:element>
			<xsl:element name="dc:description">
				<xsl:attribute name="xml:lang">
					<xsl:value-of select="'nl'"/>
				</xsl:attribute>
				<xsl:value-of select="str[@name='teaser']"/>
			</xsl:element>
			<xsl:element name="dc:publisher">
				<xsl:value-of select="'School aan Zet'"/>
			</xsl:element>
			<xsl:element name="dc:date">
				<xsl:value-of select="date[@name='changed']"/>
			</xsl:element>
		</oai_dc:dc>
	</xsl:template>
</xsl:stylesheet>