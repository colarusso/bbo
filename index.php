<?php header('Content-type: text/html');
$check = "";
$list = $_POST['list'];
?>
<html>
<head>
<title>BBO Lookup</title>
</head>
<body>
<form name=FORM action="" method="POST">
<h3>Input</h3> Comma delineated list: last name, first name, bbo # (optional)
<p>For example:</p> 
<pre style="background:#eee;padding:15px;">
Finch, Atticus
Mason, Perry
</pre>
<p><textarea name="list" style="width:100%;height:200px;"><?php echo $list?></textarea></p>
<p><input type=submit value="See what the BBO has on record."></p>
<h3>Output</h3> Comma delineated list: last name, first name, bbo #, date admitted
<p><textarea style="width:100%;height:200px;">
<?php 

if ($list) {

	$listing = preg_split("/\n/", $list);
	foreach ($listing as $value) {
		$elements = preg_split("/,(\s)*/", $value);
		$last = $elements[0];
		$first = $elements[1];
		$bbo = $elements[2];
		$first = preg_replace("/(\s)*/", "", $first);
		$last = preg_replace("/(\s)*/", "", $last);
		$bbo = preg_replace("/(\s)*/", "", $bbo);

		$hits = 0;
		$content = file_get_contents('http://massbbo.org/bbolookup.php?sf='.$first.'&sl='.$last.'&sc=', true);
		$hits = preg_match_all("/Board of Bar Overseers number:\s*\d*<br>/i",$content,$matches);
		$morehits = preg_match_all("/Admitted(\s)*to(\s)*the(\s)*bar(\s)*on(\s)*.*<br>/i",$content,$match);

		if ($hits == 1) {
			$addmitted = "";
			foreach ($match[0] as $value) {
				$value = preg_replace("/Admitted(\s)*to(\s)*the(\s)*bar(\s)*on(\s)*/", "", $value);
				$value = preg_replace("/\s*\n*<br>/", "", $value);
				$admitted = $value;
			}

			foreach ($matches[0] as $value) {
				$value = preg_replace("/Board of Bar Overseers number:(\s)*/", "", $value);
				$value = preg_replace("/\s*\n*<br>/", "", $value);
				if ($bbo == $value) {
					echo "$last, $first, $bbo, $admitted\n";
				} else if ($bbo == "") {
					echo "$last, $first, $value, $admitted\n";
					$check = $check."<li style=\"background:#ddffdd;\"><a href=\"http://massbbo.org/bbolookup.php?sf=".$first."&sl=".$last."\" target=_blank>$last, $first</a> (BBO# inferred)</li>";
				} else {
					echo "$last, $first, $value, ERROR: mismatched BBO#\n";
					$check = $check."<li style=\"background:#ffdddd;\"><a href=\"http://massbbo.org/bbolookup.php?sf=".$first."&sl=".$last."\" target=_blank>$last, $first</a> (Mismatched BBO#)</li>";
				}
			}

		} else {
				echo "$first, $last, $bbo, ERROR: Name matches 0 or more than 1 entry.\n";
				$check = $check."<li style=\"background:#ffdddd;\"><a href=\"http://massbbo.org/bbolookup.php?sf=".$first."&sl=".$last."\" target=_blank>$last, $first</a> (Name matches 0 or more than 1 entry)</li>";
		}
	}

}

if ($check == "") { $check ="<li>none</li>";  }
?>
</textarea>
</p>
<h3>Entries of Note:</h3>
<?php echo $check ?>
</form>
</body>
</html>
