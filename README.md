# SolrOai
This adds an OAI-PMH interface to your Solr web endpoint. The solroai.php file translates OAI-PMH requests to the Solr web endpoint and translates the Solr response back to a proper OAI-PMH reply.
Different metadata formats are handled by the [Solr XsltResponseWriter](http://wiki.apache.org/solr/XsltResponseWriter).

# Install
- Put the solroai.php and config.php files at the location you want to host the oaiprovider from. 
- Add the xslt in the proper Solr container.
- Edit the config.php to your settings, make sure the xsl in $metadataprefixes corresponds with the one in the Solr container.

**requirements**
The script has the following PHP dependencies:
- curl
- simpleXML
