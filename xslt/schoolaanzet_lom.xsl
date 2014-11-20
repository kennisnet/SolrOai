<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xmlns:lom="http://www.imsglobal.org/xsd/imsmd_v1p2">
	<xsl:output media-type="text/xml" method="xml" indent="yes"/>
	
	<xsl:template match="@*|node()">
		<xsl:copy>
			<xsl:apply-templates select="@*|node()"/>
		</xsl:copy>
	</xsl:template>
	
	
	<xsl:template match="/response/result/doc">
		<lom:lom xmlns:lom="http://www.imsglobal.org/xsd/imsmd_v1p2">
			<xsl:element name="lom:general">
				<xsl:call-template name="langstring">
					<xsl:with-param name="element" select="'lom:title'"/>
					<xsl:with-param name="value" select="str[@name='title']"/>
				</xsl:call-template>
				<xsl:element name="lom:catalogentry">
					<xsl:element name="lom:catalog">
						<xsl:value-of select="'URN'"/>
					</xsl:element>
					<xsl:call-template name="langstring">
						<xsl:with-param name="element" select="'lom:entry'"/>
						<xsl:with-param name="value" select="concat('urn:schoolaanzet:', int[@name='uid'])"/>
						<xsl:with-param name="lang" select="'x-none'"/>
					</xsl:call-template>
				</xsl:element>
				<xsl:element name="lom:catalogentry">
					<xsl:element name="lom:catalog">
						<xsl:value-of select="'schoolaanzet'"/>
					</xsl:element>
					<xsl:call-template name="langstring">
						<xsl:with-param name="element" select="'lom:entry'"/>
						<xsl:with-param name="value" select="int[@name='uid']"/>
						<xsl:with-param name="lang" select="'x-none'"/>
					</xsl:call-template>
				</xsl:element>
				<xsl:call-template name="langstring">
					<xsl:with-param name="element" select="'lom:description'"/>
					<xsl:with-param name="value" select="str[@name='teaser']"/>
				</xsl:call-template>
				<xsl:for-each select="arr[@name='theme_stringM']">
					<xsl:call-template name="langstring">
						<xsl:with-param name="element" select="'lom:keyword'"/>
						<xsl:with-param name="value" select="."/>
					</xsl:call-template>
				</xsl:for-each>
				<xsl:call-template name="vocabulary">
					<xsl:with-param name="element" select="'lom:aggregationlevel'"/>
					<xsl:with-param name="source" select="'LOMv1.0'"/>
					<xsl:with-param name="value" select="'2'"/>
				</xsl:call-template>
			</xsl:element>

			<xsl:element name="lom:lifecycle">
				<xsl:element name="lom:contribute">
					<xsl:call-template name="vocabulary">
						<xsl:with-param name="element" select="'lom:role'"/>
						<xsl:with-param name="source" select="'LOMv1.0'"/>
						<xsl:with-param name="value" select="'publisher'"/>
					</xsl:call-template>
					<xsl:element name="lom:centity">
						<xsl:element name="lom:vcard">
							<xsl:value-of select="'BEGIN:VCARD&#xA;VERSION:3.0&#xA;FN:School Aan Zet&#xA;END:VCARD'"/>
						</xsl:element>
					</xsl:element>
					<xsl:element name="lom:date">
						<xsl:element name="lom:datetime">
							<xsl:value-of select="date[@name='changed']"/>
						</xsl:element>
					</xsl:element>
				</xsl:element>
			</xsl:element>
			
			<xsl:element name="lom:metametadata">
				<xsl:element name="metadataschema">
					<xsl:value-of select="'LOMv1.0'"/>
				</xsl:element>
			</xsl:element>
			
			<xsl:element name="lom:technical">
				<xsl:element name="lom:location">
					<xsl:value-of select="concat('http://www.schoolaanzet.nl/', str[@name='url'])"/>
				</xsl:element>
				<xsl:element name="lom:format">
					<xsl:value-of select="'text/html'"/>
				</xsl:element>
			</xsl:element>
			
			<xsl:element name="lom:educational">
				<xsl:call-template name="vocabulary">
					<xsl:with-param name="element" select="'lom:learningresourcetype'"/>
					<xsl:with-param name="source" select="'http://purl.edustandaard.nl/vdex_learningresourcetype_czp_20060628.xml'"/>
					<xsl:with-param name="value" select="'informatiebron'"/>
				</xsl:call-template>
				<xsl:for-each select="arr[@name='sector_intM']">
					<xsl:call-template name="contextmapper">
						<xsl:with-param name="input" select="."/>
					</xsl:call-template>
				</xsl:for-each>
				<xsl:call-template name="vocabulary">
					<xsl:with-param name="element" select="'lom:intendedenduserrole'"/>
					<xsl:with-param name="source" select="'LOMv1.0'"/>
					<xsl:with-param name="value" select="'teacher'"/>
				</xsl:call-template>
			</xsl:element>
			
			<xsl:element name="lom:rights">
				<xsl:call-template name="vocabulary">
					<xsl:with-param name="element" select="'lom:cost'"/>
					<xsl:with-param name="source" select="'LOMv1.0'"/>
					<xsl:with-param name="value" select="'no'"/>
				</xsl:call-template>
				<xsl:call-template name="vocabulary">
					<xsl:with-param name="element" select="'lom:copyrightandotherrestrictions'"/>
					<xsl:with-param name="source" select="'LOMv1.0'"/>
					<xsl:with-param name="value" select="'yes'"/>
				</xsl:call-template>
				<xsl:call-template name="langstring">
					<xsl:with-param name="element" select="'lom:description'"/>
					<xsl:with-param name="value" select="'&#169; School Aan Zet 2012'"/>
				</xsl:call-template>
			</xsl:element>
			
			<xsl:element name="lom:classification">
				<xsl:call-template name="vocabulary">
					<xsl:with-param name="element" select="'lom:purpose'"/>
					<xsl:with-param name="source" select="'LOMv1.0'"/>
					<xsl:with-param name="value" select="'educationallevel'"/>
				</xsl:call-template>
				<xsl:element name="lom:taxonpath">
					<xsl:call-template name="langstring">
						<xsl:with-param name="element" select="'lom:source'"/>
						<xsl:with-param name="value" select="'http://purl.edustandaard.nl/begrippenkader'"/>
					</xsl:call-template>
					<xsl:for-each select="arr[@name='sector_intM']">
						<xsl:call-template name="educationallevelmapper">
							<xsl:with-param name="input" select="."/>
						</xsl:call-template>
					</xsl:for-each>
				</xsl:element>
			</xsl:element>
		</lom:lom>
	</xsl:template>

	<xsl:template name="contextmapper">
		<xsl:param name="input"/>
		<xsl:choose>
			<xsl:when test="$input='11614'">
				<xsl:call-template name="vocabulary">
					<xsl:with-param name="element" select="'lom:context'"/>
					<xsl:with-param name="source" select="'http://purl.edustandaard.nl/vdex_context_czp_20060628.xml'"/>
					<xsl:with-param name="value" select="'PO'"/>
				</xsl:call-template>
			</xsl:when>
			<xsl:when test="$input='11491'">
				<xsl:call-template name="vocabulary">
					<xsl:with-param name="element" select="'lom:context'"/>
					<xsl:with-param name="source" select="'http://purl.edustandaard.nl/vdex_context_czp_20060628.xml'"/>
					<xsl:with-param name="value" select="'VO'"/>
				</xsl:call-template>
			</xsl:when>
			<xsl:when test="$input='11364'">
				<xsl:call-template name="vocabulary">
					<xsl:with-param name="element" select="'lom:context'"/>
					<xsl:with-param name="source" select="'http://purl.edustandaard.nl/vdex_context_czp_20060628.xml'"/>
					<xsl:with-param name="value" select="'SO'"/>
				</xsl:call-template>
			</xsl:when>
		</xsl:choose>
	</xsl:template>

	<xsl:template name="educationallevelmapper">
		<xsl:param name="input"/>
		<xsl:choose>
			<xsl:when test="$input='11614'">
				<xsl:element name="lom:taxon">
					<xsl:element name="lom:id">
						<xsl:value-of select="'512e4729-03a4-43a2-95ba-758071d1b725'"/>
					</xsl:element>
					<xsl:call-template name="langstring">
						<xsl:with-param name="element" select="'lom:entry'"/>
						<xsl:with-param name="value" select="'Primair Onderwijs'"/>
					</xsl:call-template>
				</xsl:element>
			</xsl:when>
			<xsl:when test="$input='11491'">
				<xsl:element name="lom:taxon">
					<xsl:element name="lom:id">
						<xsl:value-of select="'2a1401e9-c223-493b-9b86-78f6993b1a8d'"/>
					</xsl:element>
					<xsl:call-template name="langstring">
						<xsl:with-param name="element" select="'lom:entry'"/>
						<xsl:with-param name="value" select="'Voortgezet Onderwijs'"/>
					</xsl:call-template>
				</xsl:element>
			</xsl:when>
			<xsl:when test="$input='11364'">
				<xsl:element name="lom:taxon">
					<xsl:element name="lom:id">
						<xsl:value-of select="'e7f1c08f-08fb-48ab-be8e-c131b1bce54a'"/>
					</xsl:element>
					<xsl:call-template name="langstring">
						<xsl:with-param name="element" select="'lom:entry'"/>
						<xsl:with-param name="value" select="'Speciaal Onderwijs'"/>
					</xsl:call-template>
				</xsl:element>
			</xsl:when>
		</xsl:choose>
	</xsl:template>
	
	<xsl:template name="langstring">
		<xsl:param name="element"/>
		<xsl:param name="value"/>
		<xsl:param name="lang" select="'nl'"/>

		<xsl:element name="{$element}">
			<xsl:element name="lom:langstring">
				<xsl:attribute name="xml:lang">
					<xsl:value-of select="$lang"/>
				</xsl:attribute>
				<xsl:value-of select="$value"/>
			</xsl:element>
		</xsl:element>
	</xsl:template>

	<xsl:template name="vocabulary">
		<xsl:param name="element"/>
		<xsl:param name="source"/>
		<xsl:param name="value"/>

		<xsl:element name="{$element}">
			<xsl:call-template name="langstring">
				<xsl:with-param name="element" select="'lom:source'"/>
				<xsl:with-param name="value" select="$source"/>
				<xsl:with-param name="lang" select="'x-none'"/>
			</xsl:call-template>
			<xsl:call-template name="langstring">
				<xsl:with-param name="element" select="'lom:value'"/>
				<xsl:with-param name="value" select="$value"/>
				<xsl:with-param name="lang" select="'x-none'"/>
			</xsl:call-template>
		</xsl:element>
	</xsl:template>
	
</xsl:stylesheet>
