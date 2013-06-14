<?php

require_once('db.inc.php');
?>
<html>
<head>
<title>Comparison Tool</title>
  <link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
  <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js"></script>
  <script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
  
<script>
<?

$sql='select typename from invTypes where invTypes.published=1 order by typename';

$stmt = $dbh->prepare($sql);

$stmt->execute();

echo "source=[";
$row = $stmt->fetchObject();
echo  '"'.addslashes($row->typename).'"';
while ($row = $stmt->fetchObject()){
echo ',"'.addslashes($row->typename).'"';
}
echo "];\n";
?>

$(document).ready(function() {
    $("input#itemname").autocomplete({ source: source });
});
</script>

<style>
.ui-menu .ui-menu-item a {
    display: block;
    line-height: 1;
    padding: 0.2em 0.4em;
    text-decoration: none;
}
</style>
</head>
<body>
<?
if (array_key_exists('error',$_GET))
{
echo "There was a problem finding your item.";
}
?>
Select your first item (you'll get the whole group):
<form method=post action='display.php'>
<input type=text width=30 id="itemname" name='itemname' />
<select name=database id=database>
<option value=0>Current</option>
<?
$sql='select id,name from evesupport.dbversions order by id desc';

$stmt = $dbh->prepare($sql);

$stmt->execute();

while ($row = $stmt->fetchObject()){
echo "<option value='".$row->id."'>".$row->name."</option>";
}
?>
</select>

<input type=submit value="Start Comparison" />
</form>
<?php include('/home/web/fuzzwork/analytics.php'); ?>
</body>
</html>
