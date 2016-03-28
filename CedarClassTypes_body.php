<?php
class CedarClassTypes extends SpecialPage
{
    var $dbuser, $dbpwd ;

    function CedarClassTypes() {
	SpecialPage::SpecialPage("CedarClassTypes");
	#wfLoadExtensionMessages( 'CedarClassTypes' ) ;

	$this->dbuser = "madrigal" ;
	$this->dbpwd = "shrot-kash-iv-po" ;
    }
    
    function execute( $par ) {
	global $wgRequest, $wgOut, $wgDBserver, $wgServer ;
	
	$this->setHeaders() ;
	$this->addCedarScripts() ;
	$this->displayClassTypes() ;
    }

    private function displayClassTypes()
    {
	global $wgRequest, $wgOut, $wgDBserver, $wgServer, $wgUser ;

	$allowed = $wgUser->isAllowed( 'cedar_admin' ) ;

	// Get the catalog database
	$dbh = new DatabaseMysql( $wgDBserver, $this->dbuser, $this->dbpwd, "CEDARCATALOG" ) ;
	if( !$dbh )
	{
	    $wgOut->addHTML( "Unable to connect to the CEDAR Catalog database\n" ) ;
	    return ;
	}

	$res = $dbh->query( "select ID, NAME from tbl_class_type WHERE PARENT=0 ORDER BY NAME" ) ;
	if( !$res )
	{
	    $db_error = $dbh->lastError() ;
	    $dbh->close() ;
	    $wgOut->addHTML( "Unable to query the CEDAR Catalog database<BR />\n" ) ;
	    $wgOut->addHTML( $db_error ) ;
	    $wgOut->addHTML( "<BR />\n" ) ;
	    return ;
	}

	$wgOut->addHTML( "<table width=\"800\" border=\"1\" cellspacing=\"0\" cellpadding=\"10\">\n" ) ;
	$wgOut->addHTML( "    <tr>\n" ) ;
	$wgOut->addHTML( "	<td width=\"50%\" valign=\"top\">\n" ) ;
	$wgOut->addHTML( "	    <div id=\"instrument_class_types\">\n" ) ;

	while( ( $obj = $dbh->fetchObject( $res ) ) )
	{
	    $id = $obj->ID ;
	    $id_img = $id . "_img" ;
	    $id_name = $id . "_name" ;
	    $id_children = $id . "_children" ;
	    $name = $obj->NAME ;

	    $wgOut->addHTML( "		<img src=\"/wiki/icons/arrow_right.gif\" height=\"10\" width=\"10\" id=\"$id_img\" title=\"expand\" alt=\"expand\" onclick=\"class_expand($id,0)\"/><span id=\"$id_name\" onclick=\"class_highlight($id)\" style=\"background-color:transparent\">&nbsp;$name</span>\n" ) ;
	    $wgOut->addHTML( "		<br>\n" ) ;
	    $wgOut->addHTML( "		<span id=\"$id_children\"></span>\n" ) ;
	}

	$wgOut->addHTML( "	    </div>\n" ) ;
	$wgOut->addHTML( "	</td>\n" ) ;
	$wgOut->addHTML( "	<td width=\"50%\" valign=\"top\">\n" ) ;
	$wgOut->addHTML( "	    Instruments for selected Class Type:<br>\n" ) ;
	$wgOut->addHTML( "	    <span id=\"instrument_info\">&nbsp;</span>\n" ) ;
	$wgOut->addHTML( "	</td>\n" ) ;
	$wgOut->addHTML( "    </tr>\n" ) ;
	$wgOut->addHTML( "</table>\n" ) ;
    }

    private function addCedarScripts()
    {
	global $wgOut ;

	$wgOut->addScript( "<script language=\"javascript\">

var xmlHttp=null
var class_type_id=0

function class_highlight(class_type)
{
    var x=document.getElementById(\"instrument_class_types\").getElementsByTagName(\"span\");
    for (var i=0;i<x.length;i++)
    { 
	x[i].style.backgroundColor=\"transparent\"
    }
    var html_id=class_type+\"_name\"
    document.getElementById(html_id).style.backgroundColor=\"yellow\"

    xmlHttp=GetXmlHttpObject()
    if (xmlHttp==null)
    {
	alert (\"Browser does not support HTTP Request\")
	return
    }
    var url=\"/class_instruments.php\"
    url=url+\"?class_type=\"+class_type
    xmlHttp.onreadystatechange=instrumentInfo 
    xmlHttp.open(\"GET\",url,true)
    xmlHttp.send(null)
}

function instrumentInfo() 
{
    if (xmlHttp.readyState==4 || xmlHttp.readyState==\"complete\")
    { 
	document.getElementById(\"instrument_info\").innerHTML=xmlHttp.responseText 
    }
}

function class_expand(class_type,class_indent)
{
    class_type_id=class_type
    var html_id=class_type_id+\"_children\"
    var img_id=class_type_id+\"_img\"
    if (document.getElementById(img_id).title==\"collapse\")
    {
	document.getElementById(html_id).innerHTML=\"\"
	document.getElementById(img_id).src=\"/wiki/icons/arrow_right.gif\"
	document.getElementById(img_id).title=\"expand\"
	class_highlight(class_type_id)
    }
    else
    {
	xmlHttp=GetXmlHttpObject()
	if (xmlHttp==null)
	{
	    alert (\"Browser does not support HTTP Request\")
	    return
	}
	var url=\"/class_children.php\"
	url=url+\"?class_type=\"+class_type
	url=url+\"&class_indent=\"+class_indent
	xmlHttp.onreadystatechange=expandClass 
	xmlHttp.open(\"GET\",url,true)
	xmlHttp.send(null)
    }
}

function expandClass() 
{ 
    var html_id=class_type_id+\"_children\"
    var img_id=class_type_id+\"_img\"
    if (xmlHttp.readyState==4 || xmlHttp.readyState==\"complete\")
    { 
	document.getElementById(html_id).innerHTML=xmlHttp.responseText 
	document.getElementById(img_id).src=\"/wiki/icons/arrow_down.gif\"
	document.getElementById(img_id).title=\"collapse\"
	class_highlight(class_type_id)
    }
}

function GetXmlHttpObject()
{
    if (xmlHttp)
    {
	localxmlHttp=xmlHttp
    }
    else
    {
	var localxmlHttp=null;
	try
	{
	    // Firefox, Opera 8.0+, Safari
	    localxmlHttp=new XMLHttpRequest();
	}
	catch (e)
	{
	    // Internet Explorer
	    try
	    {
		localxmlHttp=new ActiveXObject(\"Msxml2.XMLHTTP\");
	    }
	    catch (e)
	    {
		localxmlHttp=new ActiveXObject(\"Microsoft.XMLHTTP\");
	    }
	}
    }
    return localxmlHttp;
}

	</script>\n" ) ;
    }
}
?>
