# SolrOai
This adds an OAI-PMH interface to your Solr web endpoint. The solroai.php file translates OAI-PMH requests to the Solr web endpoint and translates the Solr response back to a proper OAI-PMH reply.
Different metadata formats are handled by the [Solr XsltResponseWriter](http://wiki.apache.org/solr/XsltResponseWriter).
