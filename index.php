<?php
/**
BLINKING TALLY MARKS
TYPE OUT TEXT ONTO SCREEN

12 Characters per line
17 lines per column
2 columns
408 characters
/**/
?>
<html>
<head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script type="text/javascript">
$(document).ready( function(){
	setInterval(function () {
	   var vis = $("#command-line .tally-mark").css("visibility");
	   vis = (!vis || vis == "visible") ? "hidden" : "visible";
	   $("#command-line .tally-mark").css("visibility", vis);
	}, 500);

	var hack_answer = '';
	var remaining_attempts = 0;
	var all_answers = '';
	
	max_attempts = 0;
	duds_removed = new Array();
	$.ajax({
		type: 'GET' 
		,url: 'fallout_hacking.php'
		,data: {'difficulty': 'random', 'pa': 'initialize'}
		,dataType: 'json'
		,success: function(data) {
			console.log(data);
			$('#answers').html(data.blob);
			
			var data_set = 0;
			var data_tag = 0;
			var prev_letter = false;
			var chars_per_line = 17;
			var char_counter = 17;
			var hex_counter = 0;
			var open_paren = false;
			var open_tag = false;
			var open_square = false;
			var open_curly = false;
			
			hack_answer = data.answer;
			all_answers = data.words;
			max_attempts = data.attempts;
			$('#remaining-attempts').text(max_attempts);
			for (var b = 0; b < max_attempts; b++) {
				$('#remaining-attempts-tally').append('<span class="tally-mark"></span>');
			}
			
			//Create sets of letters and brackets
			$('#answers span').each(function(){
				if (char_counter >= 17) {
					var hex_string = data.hexes[hex_counter] + ' ';
					$(this).before(hex_string);
					hex_counter++;
					char_counter = 0;
				}//END IF
				
				//This needs to be more robust; looking for matching braces (what about when we have '<[/]+>' which has a pair inside a pair?)
				if (/^[\[\(\{\<]+$/.test($(this).text())) {
					switch ($(this).text()) {
						case '(':
							open_paren = $(this).index();
							break;
						case '{':
							open_curly = $(this).index();
							break;
						case '[':
							open_square = $(this).index();
							break;
						case '<':
							open_tag = $(this).index();
							break;
					}//END SWITCH
				}//END IF
				
				/**/
				if (/^[\]\)\}\>]+$/.test($(this).text())) {
					var endex = $(this).index();
					if (open_paren !== false && $(this).text() == ')') {
						for(var k = open_paren; k <= endex; k++) {
							$('#answers span').eq(k).attr('data-tag',data_tag);
						}
					
						open_curly = false;
						open_square = false;
						open_tag = false;
						open_paren = false;
						
						data_tag++;
					}
					if (open_curly !== false && $(this).text() == '}') {
						for(var k = open_curly; k <= endex; k++) {
							$('#answers span').eq(k).attr('data-tag',data_tag);
						}
					
						open_curly = false;
						open_square = false;
						open_tag = false;
						open_paren = false;
						
						data_tag++;
					}
					if (open_square !== false && $(this).text() == ']') {
						for(var k = open_square; k <= endex; k++) {
							$('#answers span').eq(k).attr('data-tag',data_tag);
						}
					
						open_curly = false;
						open_square = false;
						open_tag = false;
						open_paren = false;
						
						data_tag++;
					}
					if (open_tag !== false && $(this).text() == '>') {
						for(var k = open_tag; k <= endex; k++) {
							$('#answers span').eq(k).attr('data-tag',data_tag);
						}
					
						open_curly = false;
						open_square = false;
						open_tag = false;
						open_paren = false;
						
						data_tag++;
					};
				}//END IF
				/**/
				
				if (/^[a-zA-Z]+$/.test($(this).text())) {
					$(this).attr('data-set',data_set);
					prev_letter = true;
					
					open_curly = false;
					open_square = false;
					open_tag = false;
					open_paren = false;
				} else {
					if (prev_letter == true) {
						data_set++;
					}//END IF
					
					prev_letter = false;
				}//END IF
				
				char_counter++;
			});
		}
		,complete: function() {
		}
	});
	
	//Highlighting
	$('#answers').on('mouseover','span',function() {
		var parent_hover = $(this);
		var entire_word = '';
		if (parent_hover.attr('data-set') !== undefined) {
			$('body span[data-set="' + parent_hover.attr('data-set') + '"]').addClass('highlight');
			
			$('body span[data-set="' + parent_hover.attr('data-set') + '"]').each(function() {
				entire_word += $(this).text();
			});
			
			$('#command-line').text(entire_word).append('<span class="tally-mark"></span>');
		} else if (parent_hover.attr('data-tag') !== undefined) {
			$('body span[data-tag="' + parent_hover.attr('data-tag') + '"]').addClass('highlight');
			
			$('body span[data-tag="' + parent_hover.attr('data-tag') + '"]').each(function() {
				entire_word += $(this).text();
			});
			
			$('#command-line').text(entire_word).append('<span class="tally-mark"></span>');
		} else {
			parent_hover.addClass('highlight');
			$('#command-line .tally-mark').before(parent_hover.text());
		}//END IF
	}).on('mouseout','span',function() {
		var parent_hover = $(this);
		var entire_word = '';
		if (parent_hover.attr('data-set') !== undefined) {
			$('body span[data-set="' + parent_hover.attr('data-set') + '"]').removeClass('highlight');
			
			$('#command-line').html('<span class="tally-mark"></span>');
		} else if (parent_hover.attr('data-tag') !== undefined) {
			$('body span[data-tag="' + parent_hover.attr('data-tag') + '"]').removeClass('highlight');
			
			$('#command-line').html('<span class="tally-mark"></span>');
		} else {
			parent_hover.removeClass('highlight');
			$('#command-line').html('<span class="tally-mark"></span>');
		}//END IF
	});
	
	$('#answers').on('click','span',function() {
		var parent_click = $(this);
		var word_to_send = '';
		if (parent_click.attr('data-tag') !== undefined) {
			$('body span[data-tag="' + parent_click.attr('data-tag') + '"]').each(function() {
				word_to_send += $(this).text();
			});
			
			var xor = Math.round(Math.random());
			
			if (xor == 1) {
				removeDud(hack_answer, all_answers);
				
				$('#return').append('<span>&gt;' + word_to_send + '<br />&gt;Dud removed.</span>');
				clean_return();
			} else {
				resetAllowance(max_attempts);
				
				$('#return').append('<span>&gt;' + word_to_send + '<br />&gt;allowance<br />&gt;replenished.</span>');
				clean_return();
			}//END IF
			
			$('body span[data-tag="' + parent_click.attr('data-tag') + '"]').removeAttr('data-tag').removeClass('highlight');
		} else {
			if (parent_click.attr('data-set') !== undefined) {
				$('body span[data-set="' + parent_click.attr('data-set') + '"]').each(function() {
					word_to_send += $(this).text();
				});
			} else {
				word_to_send = parent_click.text();
			}//END IF
			
			remaining_attempts = $('#remaining-attempts').text();
			if(remaining_attempts > 0) {
				$.ajax({
					type: 'GET' 
					,url: 'fallout_hacking.php'
					,data: {'choice': word_to_send, 'correct_word': hack_answer, 'attempts': (max_attempts - remaining_attempts) + 1, 'pa': 'compare'}
					,dataType: 'json'
					,success: function(data) {
						$('#return').append(data.msg);
						clean_return();
					}
					,complete: function() {
						remaining_attempts--;
						$('#remaining-attempts').text(remaining_attempts);
						$('#remaining-attempts-tally span:first').remove();
					}
				});
			}//END IF
			
			if ((remaining_attempts - 1) == 0) {
				$('body').html('<p>TERMINAL LOCKED<br />PLEASE CONTACT AN ADMINISTRATOR</p>');
			}//END IF
		}//END IF
	});
});

function removeDud(answer, words) {
	if (duds_removed.length == (words.length - 1)) {
		return false;
	}
	
	var random_key = Math.floor(Math.random() * words.length);
	var random_word = words[random_key];
	
	//Random word cannot be the answer and random word cannot be a word we've already chosen
	if (random_word == answer || duds_removed.indexOf(random_word) != -1) {
		removeDud(answer, words);
		return false;
	}//END IF
	
	var set_to_remove = words.indexOf(random_word);
	duds_removed.push(random_word);
	
	$('span[data-set="' + set_to_remove + '"]').html('.');
	$('span[data-set="' + set_to_remove + '"]').removeAttr('data-set');
	
	return true;
}

function clean_return() {
	if ($('#return span').length > 3) {
		$('#return span:first').remove();
	}//END IF
}//END FUNCTION

function resetAllowance(max_allowance) {
	$('#remaining-attempts').text(max_allowance);
	$('#remaining-attempts-tally').html('');
	
	for (var t = 0; t < max_allowance; t++) {
		$('#remaining-attempts-tally').append('<span class="tally-mark"></span>');
	}//END FOR
}

function is_matching_tag(close_tag, open_tag) {
	if (close_tag == '>') {
		if (open_tag == '<') {
			return true;
		}
	}
	
	if (close_tag == '}') {
		if (open_tag == '{') {
			return true;
		}
	}
	
	if (close_tag == ')') {
		if (open_tag == '(') {
			return true;
		}
	}
	
	if (close_tag == ']') {
		if (open_tag == '[') {
			return true;
		}
	}
	
	return false;
}
</script>
<style type="text/css">
body {
	margin: 0;
	padding: 1em;
	background-color: #0D271A;
	color: #33DD88;
	font-family: monospace;
	font-weight: bold;
	font-size: 24px;
	cursor: default;
}
.tally-mark {
	display: inline-block;
	background-color: #33DD88;
	height: 24px;
	width: 16px;
}
#remaining-attempts-tally .tally-mark {
	margin: 0 5px;
}
.highlight {
	background-color: #33DD88;
	color: #0D271A;
}
#answers {
	width: 700px;
	float: left;
	-webkit-column-count: 2;
	-webkit-column-width: 320px;
	-webkit-column-gap: 50px;
}
#results {
	float: left;
	margin-left: 50px;
}
#return {
	margin: 0;
}
#return span {
	display: block;
}
#answers {
	word-break: break-all;
	word-wrap: break-word;
}
#answers span {
	display: inline-block;
	word-break: break-all;
	word-wrap: break-word;
}
</style>
</head>

<body>
	<p>ROBCO INDUSTRIES (TM) TERMALINK PROTOCOL<br />ENTER PASSWORD NOW</p>
	
	<p>
		<span id="remaining-attempts">4</span> ATTEMPT(S) LEFT: 
		<span id="remaining-attempts-tally">
		</span>
	</p>
	
	<div id="answers">
		
	</div>
	
	<div id="results">
		<p id="return">
		</p>
		<p id="command-line"><span class="tally-mark"></span></p>
	</div>
</body>
</html>