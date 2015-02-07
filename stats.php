<?php

include("util.php");

$total = 0.0;
$donations = 0.0;

$results = $db->query("
SELECT *
FROM movies
ORDER BY m_id
");

echo "<table border='1'><caption>Movies</caption><tbody>";
echo "<tr>  <th>movie</th>  <th>views</th>  <th>€/view</th>  <th>downloads</th>  <th>€/dl</th>  <th>cost</th>  </tr>";
while ($r = $results->fetchArray()) {
	echo "<tr>";
	echo "  <td>".$r['m_name']."</td>";
	echo "  <td>".$r['m_views']."</td>";
	echo "  <td>".$r['m_viewcost']."</td>";
	echo "  <td>".$r['m_downloads']."</td>";
	echo "  <td>".$r['m_dlcost']."</td>";
	$cost = (int)$r['m_views'] * (double)$r['m_viewcost'] + (int)$r['m_downloads'] * (double)$r['m_dlcost'];
	$total += $cost;
	echo "  <td>".round($cost, 2)." €</td>";
	echo "</tr>\n";
}
echo "</tbody></table>";

$results = $db->query("
SELECT *
FROM donations
ORDER BY d_dtime
");

echo "<br/><table border='1'><caption>Donations</caption><tbody>";
echo "<tr>  <th>Datetime</th>  <th>Amount</th>  <th>Comment</th>  </tr>";
while ($r = $results->fetchArray()) {
	echo "<tr>";
	echo "  <td>".$r['d_dtime']."</td>";
	$am = (double)$r['d_amount'];
	$donations += $am;
	echo "  <td>".round($am, 2)." €</td>";
	if( ! is_null($r['d_comment']) )
		echo "  <td>".$r['d_comment']."</td>";
	echo "</tr>\n";
}
echo "</tbody></table>";

echo "<br/>total cost: ".round($total, 2)." €";
echo "<br/>donations: ".round($donations, 2)." €";
echo "<br/>balance: ".round($donations - $total, 2)." €";
