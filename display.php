<?php
require_once('db.inc.php');

$database='eve';
$databasenumber=0;
if ((array_key_exists('database',$_POST) &&  is_numeric($_POST['database']))|| (array_key_exists('database',$_GET) &&  is_numeric($_GET['database'])))
{

    if (array_key_exists('database',$_POST))
    {
        $dbnum=$_POST['database'];
    }
    else
    {
        $dbnum=$_GET['database'];
    }

    $sql='select id,version from evesupport.dbversions where id=?';

    $stmt = $dbh->prepare($sql);

    $stmt->execute(array($dbnum));

    while ($row = $stmt->fetchObject()){
        $databasenumber=$row->id;
        $database=$row->version;
    }

}


if (array_key_exists('itemname',$_POST))
{
$bpid=strtolower($_POST['itemname']);
$sql="select typename,coalesce(parenttypeid,invTypes.typeid) typeid from $database.invTypes left join $database.invMetaTypes on (invMetaTypes.typeid=invTypes.typeid) where lower(typename)=lower(?)";
}
else
{
$bpid=$_GET['itemid'];
$sql="select typename,coalesce(parenttypeid,invTypes.typeid) typeid from $database.invTypes left join $database.invMetaTypes on (invMetaTypes.typeid=invTypes.typeid) where invTypes.typeid=?";
}
$stmt = $dbh->prepare($sql);
$stmt->execute(array($bpid));

if ($row = $stmt->fetchObject())
{
$itemname=$row->typename;
$itemid=$row->typeid;
}
else
{
header('Location: index.php?error=1');
exit;
}


$memcache = new Memcache;
$memcache->connect('localhost', 11211) or die ("Could not connect");
?>
<html>
<head>
<title>Comparison Tool</title>
  <link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
  <link href="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/jquery.dataTables.css" rel="stylesheet" type="text/css"/>
  <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js"></script>
  <script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
  <script type="text/javascript" src="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/jquery.dataTables.min.js"></script>
  <script type="text/javascript" charset="utf-8" src="ColReorder.min.js"></script>
  <script type="text/javascript" charset="utf-8" src="ColVis.min.js"></script>
<script>
jQuery.extend( jQuery.fn.dataTableExt.oSort, {
    "currency-pre": function ( a ) {
        a = (a==="-") ? 0 : a.replace( /[^\d\-\.]/g, "" );
        return parseFloat( a );
    },
 
    "currency-asc": function ( a, b ) {
        return a - b;
    },
 
    "currency-desc": function ( a, b ) {
        return b - a;
    }
} );


<?
$sql="select attributename,dgmTypeAttributes.attributeID from $database.dgmTypeAttributes join $database.dgmAttributeTypes where dgmTypeAttributes.attributeID=dgmAttributeTypes.attributeID and typeid=? order by dgmTypeAttributes.attributeID";
$stmt = $dbh->prepare($sql);
$stmt->execute(array($itemid));

$attributecount=0;
$attributeslist=array();
while ($row = $stmt->fetchObject()){
$attributes.="<th>".$row->attributename."</th>";
$attributecount++;
$attributeslist[]=$row->attributeID;
}
?>



$(document).ready(function()
    {
        var oTable = $("#comparison").dataTable({
              "sDom": 'RC<"clear">lfrtip',
              "oColVis": {
			"aiExclude": [ 1,2 ],
	      },
              "iDisplayLength": 50,
              "aoColumns": [ null,null <? for ($x=1;$x<=$attributecount;$x++){echo ',{ "bVisible": false }';}?>]
});
    }
);
</script>
</head>
<body>
<table border=1 id="comparison" class="tablesorter">
<thead>
<tr><th>ID</th><th>Name</th>
<?
echo $attributes;
?>
</tr>
</thead>
<tbody>
<?

$sql="select invTypes.typeid,attributeid,invTypes.typename,coalesce(valueint,valuefloat) value from $database.invTypes join $database.dgmTypeAttributes on (invTypes.typeid=dgmTypeAttributes.typeid) where invTypes.typeid=? union select invTypes.typeid,attributeid,invTypes.typename,coalesce(valueint,valuefloat) value from $database.invTypes join $database.dgmTypeAttributes on (invTypes.typeid=dgmTypeAttributes.typeid) join $database.invMetaTypes on (invTypes.typeid=invMetaTypes.typeid) where (invMetaTypes.parenttypeid=?) order by typeid,attributeID";
$stmt = $dbh->prepare($sql);
$stmt->execute(array($itemid,$itemid));
$display=array();
$row = $stmt->fetchObject();
$dispid=$row->typeid;
$dispname=$row->typename;
$display[$row->attributeid]=$row->value;
while ($row = $stmt->fetchObject()){
    if ($row->typeid != $dispid)
    {
       echo "<tr><td>".$dispid."</td><td>".$dispname."</td>";
       foreach ($attributeslist as $attribute)
       {
           echo "<td data-attributeid=".$attribute.">".$display[$attribute]."</td>\n";
       }
       echo "</tr>";
       $dispid=$row->typeid;
       $dispname=$row->typename;
    }
    $display[$row->attributeid]=$row->value;
}
?>
</tbody>
</table>
<?php include('/home/web/fuzzwork/analytics.php'); ?>

</body>
</html>

