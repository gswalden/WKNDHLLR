<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title></title>
	<meta name="description" content="">
	<meta name="author" content="">
	<!-- <link rel="stylesheet" href="css/styles.css?v=1.0"> -->
	<!--[if lt IE 9]>
	<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
</head>
<body style="background-color:#303030">
	<?php 
	foreach ($photo_array as $photo) 
	{
		echo '<div style="float: left">';
		echo '<a href="' . $photo->link . '">'; 
        echo '<img src="' . $photo->images->low_resolution->url . '" alt="" />';
        echo '</a>';
        echo '<form method="post" action="/wkndhllr/index.php/tastemaker/add/'. $photo->id .'">';
        echo '<button type="submit">Add</button>';
        echo '</button></form>';
        echo '</div>';
	} 
	?>
	<button id="load_more">Load More</button>
	<form id="instagram">
		<input type="text" id="suggest_input" placeholder="http://instagr.am/p/BUG/" />
		<input id="suggest_submit" type="submit" value="Suggest!" disabled />
	</form>
	<div id="success"></div>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
	<script>
	
	</script>
	<script>
		$(document).ready(function() {
			var tags = {
				<?php 
					foreach ($pagination as $key => $value) 
					{
						echo $key . ':' . $value . ',';
					}
				?>
			};
			$('#load_more').click(function() {
				$.ajax({
				    type: 'POST',
				    url: 'http://localhost/wkndhllr/index.php/photo/tags/',
				    data: tags,
				    dataType: "json",
				    success: function(response) {
				      	tags = response.pagination;
				      	$.each(response.data, function() {
				      		$('#load_more').before('<div style="float: left"><a href="'+this.link+'"><img src="'+this.images.low_resolution.url+'" alt=""></a><form method="post" action="/wkndhllr/index.php/tastemaker/add/'+this.id+'"><button type="submit">Add</button></form></div>');
				      	});
						return true;
				    },
				    error: function() {
						return false;
				    }
				});
			});

			var match;
			$('#suggest_input').keyup(function() {
				// get input box value, trims whitepage and runs regex
				match = /^.*((instagram.com|instagr.am)\/p\/[\w-]+\/?)$/i.exec($.trim($('#suggest_input').val()));
				if (match)
				{
					$('#suggest').prop('disabled', false);
				}
				else
					$('#suggest').prop('disabled', true);
			});
			$('#instagram').submit(function() {
				if (match != null)
				{
					$.ajax({
					    type: 'POST',
					    url: 'http://localhost/wkndhllr/index.php/photo/suggest/',
					    data: 'url=' + match[1],
					    dataType: "json",
					    success: function(data) {
					      	$.each(data, function(key, value) {
								var msg = value.message;
								$('div#suggest_message').html(msg);
							});
							$('#suggest_input').val('');
							$('#success').show();
							$('#success').html('Photo suggested!').fadeOut(3500);
							return true;
					    },
					    error: function() {
							return false;
					    }
					});

					return false;
				}
				$("#box").focus();
				$('#success').show();
				$('#success').html('Invalid URL!').fadeOut(3500);
				
				
				return false;
			});
		});
	</script>
</body>
</html>
