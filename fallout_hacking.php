<?php
/**
The popular video games Fallout 3 and Fallout: New Vegas have a computer "hacking" minigame[1] where 
the player must correctly guess the correct password from a list of same-length words. Your challenge 
is to implement this game yourself.

The game operates similarly to the classic board game Mastermind[2] . The player has only 4 guesses 
and on each incorrect guess the computer will indicate how many letter positions are correct.

For example, if the password is MIND and the player guesses MEND, the game will indicate that 3 out of 
4 positions are correct (M_ND). If the password is COMPUTE and the player guesses PLAYFUL, the game will 
report 0/7. While some of the letters match, they're in the wrong position.

Ask the player for a difficulty (very easy, easy, average, hard, very hard), then present the player 
with 5 to 15 words of the same length. The length can be 4 to 15 letters. More words and letters make 
for a harder puzzle. The player then has 4 guesses, and on each incorrect guess indicate the number of 
correct positions.

Here's an example game:
	Difficulty (1-5)? 3
	SCORPION
	FLOGGING
	CROPPERS
	MIGRAINE
	FOOTNOTE
	REFINERY
	VAULTING
	VICARAGE
	PROTRACT
	DESCENTS
	Guess (4 left)? migraine
	0/8 correct
	Guess (3 left)? protract
	2/8 correct
	Guess (2 left)? croppers
	8/8 correct
	You win!

You can draw words from our favorite dictionary file: enable1.txt[3] . Your program should completely 
ignore case when making the position checks.

There may be ways to increase the difficulty of the game, perhaps even making it impossible to guarantee 
a solution, based on your particular selection of words. For example, your program could words that have 
little letter position overlap so that guesses reveal as little information to the player as possible.
/**/
$pa 						= (isset($_GET['pa']) ? $_GET['pa'] : '');
$difficulty_choice 	= (isset($_GET['difficulty']) ? $_GET['difficulty'] : 'very easy');
$attempts 				= (isset($_GET['attempts']) ? $_GET['attempts'] : 1);
$choice 					= (isset($_GET['choice']) ? $_GET['choice'] : '');
$correct_word 			= (isset($_GET['correct_word']) ? $_GET['correct_word'] : '');
define('MAX_ATTEMPTS',4);

$word_length = array(
	'very easy' 	=> 4
	,'easy' 			=> 6
	,'average' 		=> 9
	,'hard'			=> 12
	,'very hard' 	=> 15
);

$num_words = array(
	'very easy' 	=> 5
	,'easy' 			=> 8
	,'average' 		=> 10
	,'hard'			=> 12
	,'very hard' 	=> 15
);

$results = '';
$html = '';

if ($difficulty_choice == 'random') {
	$difficulty_choice = array_rand($num_words);
}//END IF

switch (strtolower($pa)) {
	case 'initialize':
		$word_list = generate_word_list($difficulty_choice);
		shuffle($word_list);
		
		$lines_on_screen = 34;
		$character_limit = 408;
		$next_word = 0;
		
		$hex_codes = array();
		while (count($hex_codes) < $lines_on_screen) {
			$hex_codes[] = generate_hex_code();
		}//END WHILE
		
		$text_blob = str_split(generate_character_blob($character_limit));
		
		$even_div = floor($character_limit / ($num_words[$difficulty_choice]));
		$last_start_point_in_chunk = $even_div - $word_length[$difficulty_choice];
		
		for ($i = 0; $i < count($text_blob); $i += $even_div) {
			$word_start = rand($i, ($i + $last_start_point_in_chunk));
			
			$temp_word = str_split($word_list[$next_word]);
			$temp_key = 0;
			for ($w = $word_start; $w < ($word_start + strlen($word_list[$next_word])); $w++) {
				$text_blob[$w] = $temp_word[$temp_key];
				$temp_key++;
			}//END FOR
			
			$next_word++;
			
			if ($next_word >= count($word_list)) {
				break;
			}
		}//END FOR
		
		$text_blob = array_values($text_blob);
		
		$temp_blob = array();
		foreach ($text_blob as $char) {
			$temp_blob[] = htmlspecialchars(strtoupper($char));
		}//END FOREACH
		$text_blob = '<span>' . implode('</span><span>', $temp_blob) . '</span>';
		
		echo json_encode(
			array(
				'words' 		=> $word_list
				,'hexes' 	=> $hex_codes
				,'blob' 		=> $text_blob
				,'len' 		=> $word_length[$difficulty_choice]
				,'num' 		=> $num_words[$difficulty_choice]
				,'answer'	=> trim($word_list[array_rand($word_list)])
				,'attempts'	=> MAX_ATTEMPTS
			)
		);
		break;
	case 'compare':
		$cva = choice_vs_answer($choice, $correct_word);
		if ($cva === true) {
			$msg = 	'' . 
						'<span>' . 
							'>' . strtoupper($choice) . 
							'<br />' . 
							'>Exact match!' . 
							'<br />' . 
							'>Please wait' . 
							'<br />' . 
							'>while system' . 
							'<br />' . 
							'>is accessed.' . 
						'</span>' . 
						'';
		} else {
			if (MAX_ATTEMPTS == $attempts) {
				$msg = 	'' . 
							'<span>' . 
								'>' . strtoupper($choice) . 
								'<br />' . 
								'>Entry denied' .
								'<br />' . 
								'>' . $cva . ' correct.' .  
								'<br />' . 
								'>Terminal locked.' . 
							'</span>' . 
							'';
				
			} else {
				$msg = 	'' . 
							'<span>' . 
								'>' . strtoupper($choice) . 
								'<br />' . 
								'>Entry denied' .
								'<br />' . 
								'>' . $cva . ' correct.' .  
							'</span>' . 
							'';
			}//END IF
		}//END IF
		
		echo json_encode(
			array(
				'msg' => $msg
			)
		);
		break;
}
/**
switch (strtolower($pa)) {
	case 'compare':
		$cva = choice_vs_answer($choice, $correct_word);
		if ($cva === true) {
			$html = 	'<p>You win!</p>' . 
						'<form method="get"><input type="submit" value="Try Again?" /></form>';
			break;
		} else {
			if (MAX_ATTEMPTS == $attempts) {
				$results = 	'<p>' . $cva . ' correct</p>';
				$html = 	'<p>You lose...</p>' . 
							'<form method="get"><input type="submit" value="Try Again?" /></form>';
				break;
			} else {
				$results = 	'<p>' . $cva . ' correct</p>' . 
								'<p>Guess (' . (MAX_ATTEMPTS - $attempts) . ' left)?</p>';
				$attempts++;
			}//END IF
		}//END IF
	case 'play':
		if (isset($_GET['display_words'])) {
			$display_words = json_decode($_GET['display_words']);
			$correct_word = $_GET['correct_word'];
		} else {
			$sub_dict = array();
			$rand_keys = array();
			$display_words = array();
			
			$full_dict = fopen('enable1.txt','r');
			while (!feof($full_dict)) {
				$line = trim(fgets($full_dict, 1024));
				
				if (strlen($line) == $word_length[$difficulty_choice]) {
					$sub_dict[] = $line;
				}//END IF
			}//END WHILE
			fclose($full_dict);
			
			$sub_dict_length = count($sub_dict);
			while (count($rand_keys) < $num_words[$difficulty_choice]) {
				$rand = rand(0,$sub_dict_length);
				
				if (! in_array($rand, $rand_keys)) {
					$rand_keys[] = $rand;
				}//END IF
			}//END WHILE
			
			foreach ($rand_keys as $key) {
				$display_words[] = trim($sub_dict[$key]);
			}//END FOREACH
			
			$correct_word = trim($display_words[array_rand($display_words)]);
		}//END IF
		
		$choices = '';
		foreach ($display_words as $word) {
			$choices .= '<label><input type="radio" name="choice" value="' . $word . '" /> ' . strtoupper($word) . '</label><input type="checkbox" /><br />';
		}//END FOREACH
		
		if ($pa != 'compare') {
			$results = '<p>Guess (' . MAX_ATTEMPTS . ' left)?</p>';
		}//END IF
		$html = 	'<form method="get">' . 
					$choices . 
					'<input type="hidden" name="pa" value="compare" />' . 
					'<input type="hidden" name="attempts" value="' . $attempts . '" />' . 
					'<input type="hidden" name="display_words" value=\'' . json_encode($display_words) . '\' />' . 
					'<input type="hidden" name="correct_word" value="' . $correct_word . '" />' . 
					'<input type="submit" />' . 
					'</form>';
		break;
	default:
		$choices = '';
		foreach ($num_words as $difficulty => $num) {
			$choices .= '<label><input type="radio" name="difficulty" value="' . $difficulty . '" /> ' . $difficulty . '</label><br />';
		}//END FOREACH
		$html = 	'<form method="get">' . 
					$choices . 
					'<input type="hidden" name="pa" value="play" />' . 
					'<input type="submit" />' . 
					'</form>';
		
		break;
}

echo '<div style="font-family: monospace; font-size: 16px;">' . $results . $html . '</div>';
/**/

function generate_word_list($difficulty) {
	global $word_length, $num_words;
	$sub_dict = array();
	$rand_keys = array();
	$display_words = array();
	
	$full_dict = fopen('enable1.txt','r');
	while (!feof($full_dict)) {
		$line = trim(fgets($full_dict, 1024));
		
		if (strlen($line) == $word_length[$difficulty]) {
			$sub_dict[] = $line;
		}//END IF
	}//END WHILE
	fclose($full_dict);
	
	$sub_dict_length = count($sub_dict);
	while (count($rand_keys) < $num_words[$difficulty]) {
		$rand = rand(0,$sub_dict_length);
		
		if (! in_array($rand, $rand_keys)) {
			$rand_keys[] = $rand;
		}//END IF
	}//END WHILE
	
	foreach ($rand_keys as $key) {
		$display_words[] = trim($sub_dict[$key]);
	}//END FOREACH
	
	return $display_words;
}

function choice_vs_answer($choice, $answer) {
	$choice_letters = str_split(strtoupper($choice));
	$answer_letters = str_split(strtoupper($answer));
	
	$correct = 0;
	$out_of = count($answer_letters);
	foreach ($choice_letters as $key => $letter) {
		if ($letter === $answer_letters[$key]) {
			$correct++;
		}//END IF
	}//END FOREACH
	
	return ($correct == $out_of ? true : $correct . '/' . $out_of);
}//END FUNCTION

function generate_hex_code() {
	$hex_vals = array('A','B','C','D','E','F',1,2,3,4,5,6,7,8,9);
	return '0x' . $hex_vals[array_rand($hex_vals)] . $hex_vals[array_rand($hex_vals)] . $hex_vals[array_rand($hex_vals)] . $hex_vals[array_rand($hex_vals)];
}//END FUNCTION

function generate_character_blob($num_chars) {
	$valid_chars = array(
		'"','\''
		,'/','?'
		,'!','@','#','$','%','^','&','*'
		,'-','_','+','='
		,'<','>','?'
		,'(',')'
		,'{','}'
		,'[',']'
		,'|','\\'
	);
	$blob = '';
	
	for ($i = 0; $i < $num_chars; $i++) {
		$blob .= $valid_chars[array_rand($valid_chars)];
	}//END FOR
	
	return $blob;
}//END FUNCTION

?>