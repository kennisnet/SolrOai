<?php
/**
 * PHP class to add an OAI-PMH provider interface to a Solr web endpoint.
 *
 * OAI-PMH requests are translated to SOLR queries. It makes use of the SOLR
 * XSLT Response Writer in combination with the metadataPRefix to retrieve
 * the records in the correct format.
 *
 * @version 0.2
 * @author Wim Muskee <wimmuskee@gmail.com>
 *
 * This class does not support sets.
 * 
 *
 * Copyright 2014-2015 Stichting Kennisnet
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:

 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.

 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
*/

date_default_timezone_set('UTC');

class SolrOai {
	# request
	private $verb = "";
	private $metadataprefix = "";
	private $from = "";
	private $fromts = "";
	private $identifier = "";

	# config
	private $scriptpath = "";
	private $solrurl = "";
	private $identify = "";
	private $metadataprefixes = array();
	private $debug = true;
	private $pagesize = 0;

	# error during process
	private $error = false;
	# actual responsedate
	private $responsedate = "";
	# server response simplexml document
	private $xml = "";
	# amount of records in response
	private $recordcount = 0;
	# position in response
	private $cursor = 0;


	public function __construct() {
		header('Content-type: text/xml');
		$this->setConfig();
		$this->responsedate = date("Y-m-d\TH:i:s\Z");

		# catch input
		if ( array_key_exists( "verb", $_GET ) ) {
			$this->verb = $_GET["verb"];
		}
		else {
			$this->error("badArgument");
		}

		# setting from, cursor and prefix,
		# either from resumptiontoken or first explicit request
		if ( array_key_exists( "resumptionToken", $_GET) ) {
			if ( substr_count( $_GET["resumptionToken"], "|") != 2 ) {
				$this->error("badResumptionToken");
			}
			else {
				$tokenarr = explode( "|", $_GET["resumptionToken"] );
				$this->cursor = $tokenarr[0];
				$this->metadataprefix = $tokenarr[1];
				$this->fromts = $tokenarr[2];
				$date = date_parse_from_format( "Y-m-d\TH:i:s\Z", $this->fromts );
				$this->from = mktime( $date["hour"], $date["minute"], $date["second"], $date["month"], $date["day"], $date["year"] );
			}
		}
		else {
			if ( array_key_exists( "metadataPrefix", $_GET) ) {
				if ( array_key_exists( $_GET["metadataPrefix"], $this->metadataprefixes ) ) {
					$this->metadataprefix = $_GET["metadataPrefix"];
				}
				else {
					$this->error("cannotDisseminateFormat");
				}
			}
			
			# handle two types of from request, always send out a long request to solr
			if ( array_key_exists( "from", $_GET ) ) {
				if ( strlen( $_GET["from"] ) == 10 ) {
					$datetime = date_parse_from_format( "Y-m-d", $_GET["from"] );
					if ( $datetime["warning_count"] > 0 || $datetime["error_count"] > 0 ) {
						$this->error("badArgument");
					}
					else {
						$this->from = $_GET["from"]."T00:00:00Z";
					}
				}
				elseif ( strlen( $_GET["from"] ) == 20 ) {
					$datetime = date_parse_from_format( "Y-m-d\TH:i:s\Z", $_GET["from"] );
					if ( $datetime["warning_count"] > 0 || $datetime["error_count"] > 0 ) {
						$this->error("badArgument");
					}
					else {
						$this->from = $_GET["from"];
					}
				}
				else {
					$this->error("badArgument");
				}
			}
			
			if ( array_key_exists( "identifier", $_GET ) ) {
				$this->identifier = $_GET["identifier"];
			}
			
			if ( array_key_exists( "set", $_GET ) ) {
				$this->error("noSetHierarchy");
			}
		}

		switch ( $this->verb ) {
			case "Identify": $this->Identify(); break;
			case "ListRecords": $this->ListRecords(); break;
			case "ListIdentifiers": $this->ListIdentifiers(); break;
			case "GetRecord": $this->GetRecord(); break;
			case "ListMetadataFormats": $this->ListMetadataFormats(); break;
			case "ListSets": $this->ListSets(); break;
			default:
				$this->error("badVerb");
		}
	}
	
	private function Identify() {
		$this->oaiHeader();
		echo "<oai:Identify>";
		echo "<oai:repositoryName>".$this->identify["name"]."</oai:repositoryName>";
		echo "<oai:baseURL>".$this->scriptpath."</oai:baseURL>";
		echo "<oai:protocolVersion>2.0</oai:protocolVersion>";
		echo "<oai:adminEmail>".$this->identify["email"]."</oai:adminEmail>";
		echo "<oai:earliestDatestamp>".$this->identify["datestamp"]."</oai:earliestDatestamp>";
		echo "<oai:deletedRecord>".$this->identify["deleted"]."</oai:deletedRecord>";
		echo "<oai:granularity>".$this->identify["granularity"]."</oai:granularity>";
		echo "</oai:Identify>";
		$this->oaiFooter();
	}
	
	private function ListRecords() {
		if ( empty( $this->metadataprefix ) ) {
			$this->error("badArgument");
		}

		# change query depending on data
		if ( !empty( $this->from ) ) {
			$this->solrurl = $this->solrurl."%20+%20changed:[".$this->from."%20TO%20*]";
		}

		# set correct xslt from metadataprefix
		$this->solrurl = $this->solrurl."&wt=xslt&tr=".$this->metadataprefixes[$this->metadataprefix]["xslt"];
		$this->solrurl = $this->solrurl."&start=".$this->cursor;
		
		$this->setXml();
		$this->setResponseData();
		
		if ( $this->recordcount == 0 ) {
			$this->error("noRecordsMatch");
		}
		
		# print response
		$this->oaiHeader();
		echo "<oai:ListRecords>";

		foreach ( $this->getRecordHeaderData() as $key => $header ) {
			echo "<oai:record>";
			echo "<oai:header>";
			echo "<oai:identifier>".$header[0]."</oai:identifier>";
			echo "<oai:datestamp>".$header[1]."</oai:datestamp>";
			echo "</oai:header>";
			echo "<oai:metadata>";
			$data = $this->xml->xpath($this->metadataprefixes[$this->metadataprefix]["recordpath"]);
			echo $data[$key]->asXML();
			echo "</oai:metadata>";
			echo "</oai:record>";
		}
		$this->setResumptionToken();
		echo "</oai:ListRecords>";
		$this->oaiFooter();
	}
	
	private function ListIdentifiers() {
		if ( empty( $this->metadataprefix ) ) {
			$this->error("badArgument");
		}

		# change query depending on data
		if ( !empty( $this->from ) ) {
			$this->solrurl = $this->solrurl."%20+%20changed:[".$this->from."%20TO%20*]";
		}

		# set correct xslt from metadataprefix
		$this->solrurl = $this->solrurl."&wt=xslt&tr=".$this->metadataprefixes[$this->metadataprefix]["xslt"];
		$this->solrurl = $this->solrurl."&start=".$this->cursor;

		$this->setXml();
		$this->setResponseData();
		
		if ( $this->recordcount == 0 ) {
			$this->error("noRecordsMatch");
		}
		
		# print response
		$this->oaiHeader();
		echo "<oai:ListIdentifiers>";

		foreach ( $this->getRecordHeaderData() as $header ) {
			echo "<oai:record>";
			echo "<oai:header>";
			echo "<oai:identifier>".$header[0]."</oai:identifier>";
			echo "<oai:datestamp>".$header[1]."</oai:datestamp>";
			echo "</oai:header>";
			echo "</oai:record>";
		}
		$this->setResumptionToken();
		echo "</oai:ListIdentifiers>";
		$this->oaiFooter();
	}
	
	private function GetRecord() {
		if ( empty( $this->metadataprefix ) ) {
			$this->error("badArgument");
		}
		
		# lookup specific identifier
		if ( empty( $this->identifier ) ) {
			$this->error("badArgument");
		}
		else {
			$this->solrurl = $this->solrurl."%20+%20uid:".$this->identifier;
		}
		
		# set correct xslt from metadataprefix
		$this->solrurl = $this->solrurl."&wt=xslt&tr=".$this->metadataprefixes[$this->metadataprefix]["xslt"];

		$this->setXml();
		$this->setResponseData();
		
		if ( $this->recordcount == 0 ) {
			$this->error("idDoesNotExist");
		}

		$header = $this->getRecordHeaderData();
		$record_identifier = $header[0][0];
		$record_datestamp = $header[0][1];
		
		# check to make sure response identifier is the same as the request one
		# Solr seems to return all identifiers when the request one does not exist
		if ( $record_identifier != $this->identifier ) {
			$this->error("idDoesNotExist");
		}
		
		# print response
		$this->oaiHeader();
		echo "<oai:GetRecord>";
		echo "<oai:record>";
		echo "<oai:header>";
		echo "<oai:identifier>".$record_identifier."</oai:identifier>";
		echo "<oai:datestamp>".$record_datestamp."</oai:datestamp>";
		echo "</oai:header>";
		echo "<oai:metadata>";
		$data = $this->xml->xpath($this->metadataprefixes[$this->metadataprefix]["recordpath"]);
		echo $data[0]->asXML();
		echo "</oai:metadata>";
		echo "</oai:record>";
		$this->setResumptionToken();
		echo "</oai:GetRecord>";
		$this->oaiFooter();
	}
	
	private function ListMetadataFormats() {
		$this->oaiHeader();
		echo "<oai:ListMetadataFormats>";
		foreach ( $this->metadataprefixes as $prefix => $values ) {
			echo "<oai:metadataFormat>";
			echo "<oai:metadataPrefix>".$prefix."</oai:metadataPrefix>";
			echo "<oai:schema>".$values["schema"]."</oai:schema>";
			echo "<oai:metadataNamespace>".$values["namespace"]."</oai:metadataNamespace>";
			echo "</oai:metadataFormat>";
		}
		echo "</oai:ListMetadataFormats>";
		$this->oaiFooter();
	}
	
	private function ListSets() {
		$this->error("noSetHierarchy");
	}

	private function error( $code ) {
		$this->error = true;
		$this->oaiHeader();
		echo "<oai:error code=\"".$code."\">".$this->errors[$code]."</oai:error>";
		$this->oaiFooter();
		exit;
	}
	
	private function getData() {
		$this->curl = curl_init( $this->solrurl );
		curl_setopt( $this->curl, CURLOPT_HEADER, FALSE );
		curl_setopt( $this->curl, CURLOPT_RETURNTRANSFER, TRUE );
		curl_setopt( $this->curl, CURLOPT_ENCODING, "gzip,deflate" );
		$data = curl_exec( $this->curl );
		curl_close( $this->curl );
		return $data;
	}

	private function setXml() {
		if ( !$this->debug ) {
			$this->xml = simplexml_load_string($this->getData());
		}
		else {
			$this->xml = simplexml_load_file( $this->metadataprefixes[$this->metadataprefix]["testoutput"]);
			
		}
		$this->xml->registerXPathNamespace('p', $this->metadataprefixes[$this->metadataprefix]["namespace"]);
	}
	
	private function setResponseData() {
		$data = $this->xml->xpath("/response/result/@numFound");
		$this->recordcount = (int) $data[0];
		
		$data = $this->xml->xpath("/response/result/@start");
		$this->cursor = (int) $data[0];
	}
	
	/**
	 * I know this is pretty ghetto, but this function retrieves the timestamp and identifier
	 * from each record in properly formatted array,
	 * The headerxpath should contain a union of 2 xpath queries. The result is a 1-dimensional
	 * array, which is then processed to get a 2-dimensional one separating the values for each
	 * record.
	 */
	private function getRecordHeaderData() {
		$rawdata = $this->xml->xpath($this->metadataprefixes[$this->metadataprefix]["headerxpath"]);
		$data = array();
		$arr = array();
		foreach ( $rawdata as $key => $value ) {
			if ( (($key/2) - floor($key/2)) == 0 ) {
				$arr[] = $value;
			}
			else {
				$arr[] = $value;
				$data[] = $arr;
				$arr = array();
			}
		}
		return $data;
	}
	
	private function setConfig() {
		require_once('config.php');

		if ( empty( $scriptpath ) ) {
			$p = "http://".$_SERVER["SERVER_NAME"].$_SERVER["SCRIPT_NAME"];
			$this->scriptpath = substr( $p, 0, strrpos($p , "/") );
		}
		else {
			$this->scriptpath = $scriptpath;
		}
		
		$this->solrurl = $solrurlbase."&rows=".$pagesize.$solrurlq;
		$this->pagesize = $pagesize;
		$this->identify = $identify;
		$this->errors = $errors;
		$this->metadataprefixes = $metadataprefixes;
		$this->debug = $debug;
	}
	
	private function setResumptionToken() {
		if ( ( $this->cursor + $this->pagesize ) < $this->recordcount ) {
			echo "<oai:resumptionToken completeListSize=\"".$this->recordcount."\"  cursor=\"".$this->cursor."\">";
			echo ($this->cursor + $this->pagesize)."|".$this->metadataprefix."|".$this->fromts;
			echo "</oai:resumptionToken>";
		}
	}

	private function oaiHeader() {
		echo "<?xml version=\"1.0\"?>";
		echo "<oai:OAI-PMH xmlns:oai=\"http://www.openarchives.org/OAI/2.0/\">";
		echo "<oai:responseDate>".$this->responsedate."</oai:responseDate>";
		
		if ( $this->error ) {
			echo "<oai:request>".$this->scriptpath."</oai:request>";
		}
		else {
			$prefix = (!empty($this->metadataprefix) ? "metadataPrefix=\"".$this->metadataprefix."\"" : "" );
			echo "<oai:request verb=\"".$this->verb."\" ".$prefix.">".$this->scriptpath."</oai:request>";
		}
	}
	
	private function oaiFooter() {
		echo "</oai:OAI-PMH>";
	}
}
?>
