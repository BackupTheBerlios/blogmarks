<div id="footer">

<hr />

<p><strong>Powered by</strong> : Mbertier / Benfle / Znarf</p>

<p>

<a href="javascript:Q='';docref='';if (document.all) Q = document.selection.createRange().text;else Q=window.getSelection();if (document.referrer) docref=escape(document.referrer);if (typeof(_ref)!= 'undefined') docref=escape(_ref);void(btw=window.open('http://localhost/bm/blogmarks.net/new.php?mini=1&summary='+escape(Q)+'&url='+escape(location.href)+'&title='+escape(document.title)+'&via='+docref+'&mini=1','BlogTHIS','location=no,toolbar=no,scrollbars=yes,width=325,height=400,left=75,top=175,status=no'));">Advanced bookmarklet</a> 

/

<a href="javascript:url=location.href;title=document.title;void( open('http://localhost/bm/blogmarks.net/new.php?mini=1&url='+escape(url)+'&title='+escape(title),'BlogMarks', 'location=no,toolbar=no,scrollbars=yes,width=325,height=400,left=75,top=175,status=no'));">Simple bookmarklet</a>

</p>

<?php

$time_end = getmicrotime();

$time = $time_end - $time_start;

echo "<p>Exec. time : " . round( $time , 5 ) . " secondes</p>";

?>

</div> <!-- /#footer -->